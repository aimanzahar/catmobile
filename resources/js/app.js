import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import './native-image-picker';

Alpine.plugin(collapse);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

Alpine.data('notificationBell', () => ({
    unread: 0,
    timer: null,

    init() {
        this.poll();
        this.timer = setInterval(() => this.poll(), 30000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') this.poll();
        });
    },

    async poll() {
        try {
            const res = await fetch('/notifications/unread-count', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            this.unread = data.unread || 0;
        } catch (e) {
            // silent
        }
    },
}));

Alpine.data('chatThread', ({ pollUrl, postUrl, isAdmin = false }) => ({
    messages: [],
    body: '',
    sending: false,
    timer: null,
    pollUrl,
    postUrl,
    isAdmin,

    init() {
        this.refresh();
        this.timer = setInterval(() => {
            if (document.visibilityState === 'visible') this.refresh();
        }, 4000);
    },

    destroy() {
        if (this.timer) clearInterval(this.timer);
    },

    async refresh() {
        try {
            const res = await fetch(this.pollUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            const previousLen = this.messages.length;
            this.messages = (data.messages || []).map(m => ({
                ...m,
                is_mine: this.isAdmin ? m.sender_role === 'admin' : m.sender_role === 'customer',
            }));
            if (this.messages.length !== previousLen) {
                this.$nextTick(() => this.scrollToBottom());
            }
        } catch (e) {
            // silent
        }
    },

    async send() {
        const body = this.body.trim();
        if (body === '' || this.sending) return;

        this.sending = true;
        const previous = this.body;
        this.body = '';

        // reset textarea height
        const ta = this.$el.querySelector('textarea');
        if (ta) ta.style.height = '';

        try {
            const res = await fetch(this.postUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ body }),
            });
            if (res.ok) {
                await this.refresh();
            } else {
                this.body = previous;
            }
        } catch (e) {
            this.body = previous;
        } finally {
            this.sending = false;
        }
    },

    autoresize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 112) + 'px';
    },

    scrollToBottom() {
        const list = document.getElementById('chat-message-list');
        if (list) list.scrollTop = list.scrollHeight;
    },

    formatTime(iso) {
        if (!iso) return '';
        try {
            const d = new Date(iso);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
            return '';
        }
    },

    isSameSenderAsPrev(idx) {
        if (idx === 0) return false;
        const prev = this.messages[idx - 1];
        const cur = this.messages[idx];
        if (!prev || !cur) return false;
        return prev.sender_role === cur.sender_role && this._minutesBetween(prev.created, cur.created) < 3;
    },

    shouldShowTime(idx) {
        const cur = this.messages[idx];
        if (!cur) return false;
        const next = this.messages[idx + 1];
        if (!next) return true;
        if (next.sender_role !== cur.sender_role) return true;
        return this._minutesBetween(cur.created, next.created) >= 3;
    },

    shouldShowDayDivider(idx) {
        const cur = this.messages[idx];
        if (!cur || !cur.created) return false;
        if (idx === 0) return true;
        const prev = this.messages[idx - 1];
        if (!prev || !prev.created) return true;
        return new Date(cur.created).toDateString() !== new Date(prev.created).toDateString();
    },

    dayLabel(iso) {
        if (!iso) return '';
        try {
            const d = new Date(iso);
            const today = new Date();
            const yesterday = new Date();
            yesterday.setDate(today.getDate() - 1);
            if (d.toDateString() === today.toDateString()) return 'Today';
            if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
            return d.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
        } catch (e) {
            return '';
        }
    },

    _minutesBetween(a, b) {
        if (!a || !b) return Infinity;
        try {
            return Math.abs(new Date(a) - new Date(b)) / 60000;
        } catch (e) {
            return Infinity;
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();
