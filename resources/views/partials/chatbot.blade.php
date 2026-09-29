{{-- =====================================================================
     Matrix Platform — AI Support Chatbot Widget
     Include this partial in both layouts/app.blade.php and layouts/user.blade.php
     just before </body>
     ===================================================================== --}}

{{-- ── Floating Chat Button ─────────────────────────────────────────────── --}}
<button id="matrix-chat-toggle" aria-label="Open Chat Assistant" title="Chat with our AI Assistant">
    <span id="matrix-chat-icon-open">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
    </span>
    <span id="matrix-chat-icon-close" style="display:none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </span>
    <span id="matrix-chat-unread" class="matrix-chat-badge" style="display:none;">1</span>
</button>

{{-- ── Chat Window ──────────────────────────────────────────────────────── --}}
<div id="matrix-chat-window" role="dialog" aria-label="Support Chat" aria-modal="true">
    {{-- Header --}}
    <div class="matrix-chat-header">
        <div class="matrix-chat-avatar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white" stroke="none"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
        </div>
        <div class="matrix-chat-header-info">
            <div class="matrix-chat-bot-name">{{ config('basic.site_title', 'Matrix') }} Assistant</div>
            <div class="matrix-chat-status"><span class="matrix-chat-online-dot"></span> Online &amp; Ready to Help</div>
        </div>
        <button class="matrix-chat-minimize" id="matrix-chat-minimize-btn" title="Minimize">—</button>
    </div>

    {{-- Messages Container --}}
    <div id="matrix-chat-messages" class="matrix-chat-messages" role="log" aria-live="polite"></div>

    {{-- Quick Actions --}}
    <div class="matrix-chat-quick-actions" id="matrix-chat-quick-actions">
        <button class="matrix-chip" data-msg="What investment plans do you offer?">📊 Plans</button>
        <button class="matrix-chip" data-msg="How do I deposit funds?">💳 Deposit</button>
        <button class="matrix-chip" data-msg="How do I withdraw my earnings?">💸 Withdraw</button>
        <button class="matrix-chip" data-msg="Tell me about the referral program">🔗 Referral</button>
        <button class="matrix-chip" data-msg="How does the profit calculator work?">📈 Calculator</button>
        <button class="matrix-chip" data-msg="I need support help">🎫 Support</button>
    </div>

    {{-- Input Area --}}
    <div class="matrix-chat-input-area">
        <input
            type="text"
            id="matrix-chat-input"
            placeholder="Type your question..."
            autocomplete="off"
            maxlength="300"
            aria-label="Chat message input"
        />
        <button id="matrix-chat-send" title="Send message" aria-label="Send message">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
        </button>
    </div>

    <div class="matrix-chat-powered">Powered by {{ config('basic.site_title', 'Matrix') }} AI · <a href="{{ route('contact') }}" target="_blank">Contact Us</a></div>
</div>

{{-- ── Styles ───────────────────────────────────────────────────────────── --}}
<style>
/* ── Variables ─────────────────────────── */
:root {
    --mxc-accent:   #ff5400;
    --mxc-accent2:  #ff7733;
    --mxc-bg:       #0d0d1a;
    --mxc-bg2:      #13132a;
    --mxc-bg3:      #1a1a35;
    --mxc-border:   rgba(255,255,255,0.08);
    --mxc-text:     #e8e8f0;
    --mxc-muted:    rgba(255,255,255,0.45);
    --mxc-radius:   20px;
    --mxc-shadow:   0 24px 80px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,84,0,0.15);
    --mxc-w:        370px;
    --mxc-h:        540px;
}

/* ── Toggle Button ─────────────────────── */
#matrix-chat-toggle {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 99999;
    width: 62px;
    height: 62px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--mxc-accent), var(--mxc-accent2));
    border: none;
    cursor: pointer;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 30px rgba(255,84,0,0.45), 0 2px 8px rgba(0,0,0,0.35);
    transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), box-shadow 0.2s;
    animation: matrix-pulse 3s ease-in-out infinite;
}
#matrix-chat-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 40px rgba(255,84,0,0.6);
    animation: none;
}
@keyframes matrix-pulse {
    0%, 100% { box-shadow: 0 8px 30px rgba(255,84,0,0.45); }
    50%       { box-shadow: 0 8px 50px rgba(255,84,0,0.75); }
}
.matrix-chat-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #e74c3c;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    animation: matrix-badge-pop 0.4s ease;
}
@keyframes matrix-badge-pop {
    0%   { transform: scale(0); }
    60%  { transform: scale(1.25); }
    100% { transform: scale(1); }
}

/* ── Chat Window ───────────────────────── */
#matrix-chat-window {
    position: fixed;
    bottom: 104px;
    right: 28px;
    z-index: 99998;
    width: var(--mxc-w);
    max-height: var(--mxc-h);
    background: var(--mxc-bg);
    border-radius: var(--mxc-radius);
    box-shadow: var(--mxc-shadow);
    border: 1px solid var(--mxc-border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform-origin: bottom right;
    transform: scale(0) translateY(20px);
    opacity: 0;
    pointer-events: none;
    transition: transform 0.3s cubic-bezier(.34,1.56,.64,1), opacity 0.25s ease;
}
#matrix-chat-window.matrix-chat-open {
    transform: scale(1) translateY(0);
    opacity: 1;
    pointer-events: all;
}

/* ── Header ────────────────────────────── */
.matrix-chat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: linear-gradient(135deg, rgba(255,84,0,0.2), rgba(255,119,51,0.1));
    border-bottom: 1px solid var(--mxc-border);
    flex-shrink: 0;
}
.matrix-chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--mxc-accent), var(--mxc-accent2));
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(255,84,0,0.4);
}
.matrix-chat-header-info { flex: 1; min-width: 0; }
.matrix-chat-bot-name {
    font-weight: 700;
    font-size: 14px;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.matrix-chat-status {
    font-size: 11px;
    color: var(--mxc-muted);
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 2px;
}
.matrix-chat-online-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #2ecc71;
    box-shadow: 0 0 6px #2ecc71;
    animation: matrix-blink 2s infinite;
    flex-shrink: 0;
}
@keyframes matrix-blink {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.4; }
}
.matrix-chat-minimize {
    background: transparent;
    border: 1px solid var(--mxc-border);
    color: var(--mxc-muted);
    width: 28px;
    height: 28px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.2s, color 0.2s;
    line-height: 1;
    padding-bottom: 2px;
}
.matrix-chat-minimize:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

/* ── Messages ──────────────────────────── */
.matrix-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
}
.matrix-chat-messages::-webkit-scrollbar { width: 4px; }
.matrix-chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

.matrix-msg {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    animation: matrix-msg-in 0.3s ease;
}
@keyframes matrix-msg-in {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
.matrix-msg-bubble {
    max-width: 82%;
    padding: 10px 14px;
    border-radius: 18px;
    font-size: 13.5px;
    line-height: 1.55;
    white-space: pre-wrap;
    word-break: break-word;
}
.matrix-msg-bubble a {
    color: var(--mxc-accent2);
    text-decoration: underline;
}

/* Bot message */
.matrix-msg.bot .matrix-msg-bubble {
    background: var(--mxc-bg3);
    color: var(--mxc-text);
    border-bottom-left-radius: 6px;
    border: 1px solid var(--mxc-border);
}
/* User message */
.matrix-msg.user {
    flex-direction: row-reverse;
}
.matrix-msg.user .matrix-msg-bubble {
    background: linear-gradient(135deg, var(--mxc-accent), var(--mxc-accent2));
    color: #fff;
    border-bottom-right-radius: 6px;
}

/* Typing indicator */
.matrix-typing-bubble {
    background: var(--mxc-bg3);
    border: 1px solid var(--mxc-border);
    border-radius: 18px;
    border-bottom-left-radius: 6px;
    padding: 12px 16px;
    display: flex;
    gap: 5px;
    align-items: center;
    max-width: 70px;
}
.matrix-typing-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--mxc-accent);
    animation: matrix-typing 1.2s infinite;
}
.matrix-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.matrix-typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes matrix-typing {
    0%, 100% { opacity: 0.3; transform: translateY(0); }
    50%       { opacity: 1;   transform: translateY(-5px); }
}

/* Bot mini avatar */
.matrix-bot-mini-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--mxc-accent), var(--mxc-accent2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(255,84,0,0.3);
}

/* ── Quick Actions ─────────────────────── */
.matrix-chat-quick-actions {
    padding: 8px 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    border-top: 1px solid var(--mxc-border);
    flex-shrink: 0;
    background: var(--mxc-bg2);
}
.matrix-chip {
    background: var(--mxc-bg3);
    border: 1px solid var(--mxc-border);
    color: var(--mxc-text);
    border-radius: 20px;
    padding: 5px 11px;
    font-size: 12px;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s, color 0.2s;
    white-space: nowrap;
}
.matrix-chip:hover {
    background: var(--mxc-accent);
    border-color: var(--mxc-accent);
    color: #fff;
}

/* ── Input ─────────────────────────────── */
.matrix-chat-input-area {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 12px 14px;
    border-top: 1px solid var(--mxc-border);
    background: var(--mxc-bg2);
    flex-shrink: 0;
}
#matrix-chat-input {
    flex: 1;
    background: var(--mxc-bg3);
    border: 1px solid var(--mxc-border);
    border-radius: 24px;
    padding: 10px 16px;
    color: #fff;
    font-size: 13.5px;
    outline: none;
    transition: border-color 0.2s;
}
#matrix-chat-input::placeholder { color: var(--mxc-muted); }
#matrix-chat-input:focus { border-color: var(--mxc-accent); }

#matrix-chat-send {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--mxc-accent), var(--mxc-accent2));
    border: none;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 12px rgba(255,84,0,0.3);
}
#matrix-chat-send:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 18px rgba(255,84,0,0.5);
}

/* ── Footer ────────────────────────────── */
.matrix-chat-powered {
    text-align: center;
    font-size: 10.5px;
    color: var(--mxc-muted);
    padding: 6px 14px 8px;
    background: var(--mxc-bg);
    flex-shrink: 0;
}
.matrix-chat-powered a {
    color: var(--mxc-accent2);
    text-decoration: none;
}

/* ── Mobile Responsive ─────────────────── */
@media (max-width: 480px) {
    :root { --mxc-w: calc(100vw - 24px); }
    #matrix-chat-window {
        right: 12px;
        bottom: 90px;
    }
    #matrix-chat-toggle {
        right: 16px;
        bottom: 20px;
    }
}
</style>

{{-- ── Script ───────────────────────────────────────────────────────────── --}}
<script>
(function() {
    'use strict';

    const CSRF_TOKEN  = '{{ csrf_token() }}';
    const BOT_ENDPOINT = '{{ route("chatbot.respond") }}';
    const SITE_NAME   = '{{ config("basic.site_title", "Matrix") }}';

    const toggleBtn  = document.getElementById('matrix-chat-toggle');
    const chatWindow = document.getElementById('matrix-chat-window');
    const openIcon   = document.getElementById('matrix-chat-icon-open');
    const closeIcon  = document.getElementById('matrix-chat-icon-close');
    const badge      = document.getElementById('matrix-chat-unread');
    const msgContainer = document.getElementById('matrix-chat-messages');
    const inputEl    = document.getElementById('matrix-chat-input');
    const sendBtn    = document.getElementById('matrix-chat-send');
    const minimizeBtn= document.getElementById('matrix-chat-minimize-btn');
    const quickActions = document.getElementById('matrix-chat-quick-actions');

    let isOpen = false;
    let hasGreeted = false;

    // ── Toggle ────────────────────────────────────────────────────────────
    function openChat() {
        isOpen = true;
        chatWindow.classList.add('matrix-chat-open');
        openIcon.style.display = 'none';
        closeIcon.style.display = 'flex';
        badge.style.display = 'none';
        if (!hasGreeted) {
            hasGreeted = true;
            setTimeout(function() {
                appendBotMessage("👋 Hello! I'm the " + SITE_NAME + " Support Assistant.\n\nI can answer questions about investments, deposits, withdrawals, referrals, and more!\n\nWhat can I help you with today?");
            }, 400);
        }
        setTimeout(function() { inputEl.focus(); }, 350);
    }

    function closeChat() {
        isOpen = false;
        chatWindow.classList.remove('matrix-chat-open');
        openIcon.style.display = 'flex';
        closeIcon.style.display = 'none';
    }

    toggleBtn.addEventListener('click', function() {
        if (isOpen) { closeChat(); } else { openChat(); }
    });

    minimizeBtn.addEventListener('click', closeChat);

    // Show badge after 3 seconds to attract attention
    setTimeout(function() {
        if (!isOpen) {
            badge.style.display = 'flex';
        }
    }, 3000);

    // ── Append messages ───────────────────────────────────────────────────
    function appendBotMessage(text) {
        const wrap = document.createElement('div');
        wrap.className = 'matrix-msg bot';
        wrap.innerHTML =
            '<div class="matrix-bot-mini-avatar">🤖</div>' +
            '<div class="matrix-msg-bubble">' + formatText(text) + '</div>';
        msgContainer.appendChild(wrap);
        scrollToBottom();
    }

    function appendUserMessage(text) {
        const wrap = document.createElement('div');
        wrap.className = 'matrix-msg user';
        wrap.innerHTML = '<div class="matrix-msg-bubble">' + escapeHtml(text) + '</div>';
        msgContainer.appendChild(wrap);
        scrollToBottom();
        // Hide quick actions after first user message
        quickActions.style.display = 'none';
    }

    function showTyping() {
        const el = document.createElement('div');
        el.className = 'matrix-msg bot';
        el.id = 'matrix-typing-indicator';
        el.innerHTML =
            '<div class="matrix-bot-mini-avatar">🤖</div>' +
            '<div class="matrix-typing-bubble">' +
              '<div class="matrix-typing-dot"></div>' +
              '<div class="matrix-typing-dot"></div>' +
              '<div class="matrix-typing-dot"></div>' +
            '</div>';
        msgContainer.appendChild(el);
        scrollToBottom();
    }

    function hideTyping() {
        const el = document.getElementById('matrix-typing-indicator');
        if (el) el.remove();
    }

    function scrollToBottom() {
        msgContainer.scrollTop = msgContainer.scrollHeight;
    }

    // ── Text helpers ──────────────────────────────────────────────────────
    function escapeHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function formatText(text) {
        // Bold **text**
        text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        // Links [label](url)
        text = text.replace(/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
        // Bare URLs (not already in tags)
        text = text.replace(/(^|\s)(https?:\/\/[^\s<]+)/g, '$1<a href="$2" target="_blank" rel="noopener">$2</a>');
        // Newlines to <br>
        text = text.replace(/\n/g, '<br>');
        return text;
    }

    // ── Send message ──────────────────────────────────────────────────────
    function sendMessage(msg) {
        msg = (msg || inputEl.value).trim();
        if (!msg) return;

        appendUserMessage(msg);
        inputEl.value = '';
        sendBtn.disabled = true;
        showTyping();

        fetch(BOT_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: msg })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            hideTyping();
            appendBotMessage(data.reply || 'I had trouble processing that. Please try again.');
        })
        .catch(function() {
            hideTyping();
            appendBotMessage("⚠️ Connection issue. Please check your internet and try again, or use the Contact page for direct support.");
        })
        .finally(function() {
            sendBtn.disabled = false;
            inputEl.focus();
        });
    }

    sendBtn.addEventListener('click', function() { sendMessage(); });

    inputEl.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // ── Quick action chips ────────────────────────────────────────────────
    document.querySelectorAll('.matrix-chip').forEach(function(btn) {
        btn.addEventListener('click', function() {
            sendMessage(this.dataset.msg);
        });
    });

})();
</script>
