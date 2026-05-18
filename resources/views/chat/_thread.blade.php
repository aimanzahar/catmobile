{{-- Shared chat thread partial. Expects: $pollUrl, $postUrl, $isAdmin, $partnerName, $backUrl --}}
<div x-data="chatThread({ pollUrl: @js($pollUrl), postUrl: @js($postUrl), isAdmin: {{ $isAdmin ? 'true' : 'false' }} })"
     x-init="init()"
     class="chat-screen">

    <header class="chat-screen-header">
        @if (! empty($backUrl))
            <a href="{{ $backUrl }}" class="chat-screen-header-back" aria-label="Back">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif
        <div class="chat-screen-header-avatar">
            {{ strtoupper(mb_substr($partnerName, 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1 leading-tight">
            <div class="text-sm font-bold text-gray-900 truncate">{{ $partnerName }}</div>
            <div class="text-[11px] text-gray-400">
                {{ $isAdmin ? 'Customer' : 'Replies usually within an hour' }}
            </div>
        </div>
    </header>

    <div id="chat-message-list" class="chat-screen-list">
        <template x-if="messages.length === 0">
            <p class="chat-empty-state">
                @if($isAdmin)
                    No messages yet.<br>Send the first reply to greet the customer.
                @else
                    No messages yet.<br>Type below to start a conversation with the shop.
                @endif
            </p>
        </template>

        <template x-for="(msg, idx) in messages" :key="msg.id">
            <div class="contents">
                <div x-show="shouldShowDayDivider(idx)" class="chat-day-divider" x-text="dayLabel(msg.created)"></div>
                <div :class="msg.is_mine
                        ? 'chat-bubble chat-bubble-mine ' + (isSameSenderAsPrev(idx) ? '' : 'chat-bubble-mine--head')
                        : 'chat-bubble chat-bubble-theirs ' + (isSameSenderAsPrev(idx) ? '' : 'chat-bubble-theirs--head')"
                     :style="isSameSenderAsPrev(idx) ? 'margin-top: 2px' : 'margin-top: 6px'">
                    <span class="whitespace-pre-wrap" x-text="msg.body"></span>
                    <span x-show="shouldShowTime(idx)" class="chat-bubble-time" x-text="formatTime(msg.created)"></span>
                </div>
            </div>
        </template>
    </div>

    <form @submit.prevent="send()" class="chat-screen-composer">
        <textarea x-model="body"
                  rows="1"
                  placeholder="Type a message..."
                  class="chat-screen-composer-input"
                  @keydown.enter.prevent="send()"
                  @input="autoresize($event.target)"></textarea>
        <button type="submit"
                :disabled="sending || body.trim() === ''"
                class="chat-screen-composer-send"
                aria-label="Send">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                <path d="M3.105 2.288a.75.75 0 00-.826.95l1.414 4.926A.75.75 0 004.42 8.75h7.83a.75.75 0 010 1.5H4.42a.75.75 0 00-.728.586l-1.414 4.926a.75.75 0 00.826.95 28.896 28.896 0 0015.293-7.155.75.75 0 000-1.114A28.897 28.897 0 003.105 2.288z" />
            </svg>
        </button>
    </form>
</div>
