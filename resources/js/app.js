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
    pending: [], // sent locally, not yet confirmed by the server
    body: '',
    timer: null,
    seq: 0,
    outbox: Promise.resolve(),
    pollUrl,
    postUrl,
    isAdmin,

    init() {
        this.refresh();
        this.timer = setInterval(() => {
            if (document.visibilityState === 'visible') this.refresh();
        }, 4000);
        // Keyboard opening shrinks the WebView; keep the newest message in view.
        this.onResize = () => this.scrollToBottom();
        window.addEventListener('resize', this.onResize);
    },

    destroy() {
        clearInterval(this.timer);
        window.removeEventListener('resize', this.onResize);
    },

    withMine(m) {
        return { ...m, is_mine: m.sender_role === (this.isAdmin ? 'admin' : 'customer') };
    },

    setMessages(list) {
        const lastId = this.messages[this.messages.length - 1]?.id;
        this.messages = list;
        if (list[list.length - 1]?.id !== lastId) {
            this.$nextTick(() => this.scrollToBottom());
        }
    },

    async refresh() {
        const seq = ++this.seq;
        try {
            const res = await fetch(this.pollUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            // A newer poll or a confirmed send has happened since; this response is stale.
            if (seq !== this.seq) return;
            this.setMessages([...(data.messages || []).map(m => this.withMine(m)), ...this.pending]);
        } catch (e) {
            // silent
        }
    },

    send() {
        const body = this.body.trim();
        if (body === '') return;

        const local = this.withMine({
            id: 'tmp-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
            sender_role: this.isAdmin ? 'admin' : 'customer',
            body,
            created: new Date().toISOString(),
            pending: true,
        });
        this.pending.push(local);
        this.setMessages([...this.messages, local]);

        this.body = '';
        const ta = this.$el.querySelector('textarea');
        if (ta) ta.style.height = '';

        // Serialised so messages reach the server in the order they were typed.
        this.outbox = this.outbox.then(() => this.post(local));
    },

    async post(local) {
        try {
            const res = await fetch(this.postUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ body: local.body }),
            });
            if (!res.ok) throw new Error('send failed: ' + res.status);
            const data = await res.json();

            this.seq++; // discard polls that started before this message existed
            this.pending = this.pending.filter(p => p.id !== local.id);
            const list = this.messages.filter(m => m.id !== local.id);
            if (data.message && !list.some(m => m.id === data.message.id)) {
                const at = list.findIndex(m => m.pending);
                list.splice(at === -1 ? list.length : at, 0, this.withMine(data.message));
            }
            this.setMessages(list);
        } catch (e) {
            this.pending = this.pending.filter(p => p.id !== local.id);
            this.setMessages(this.messages.filter(m => m.id !== local.id));
            if (this.body.trim() === '') this.body = local.body;
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
