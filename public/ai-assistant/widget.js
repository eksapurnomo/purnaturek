/**
 * PurNatureK Wholesale Portal — Customer-Facing AI Assistant Widget
 * 
 * Title: Assistant (strictly enforced)
 * Self-contained vanilla JS embeddable widget.
 */
(function () {
    'use strict';

    // Prevent duplicate initialization
    if (window.__PurNatureKAIAssistantLoaded) return;
    window.__PurNatureKAIAssistantLoaded = true;

    const STORAGE_KEY_MSGS = 'pnk_ai_assistant_msgs';
    const STORAGE_KEY_STATE = 'pnk_ai_assistant_open';

    let config = {
        enabled: false,
        title: 'Assistant',
        welcome_message: 'Welcome to PurNatureK Bulk & Wholesale. How can I help your business today?',
        placeholder: 'Ask about botanical extracts, specifications, MOQs...',
        position: 'bottom-right',
        theme_color: '#0f4a3e',
        auto_open: false,
        faqs: []
    };

    let isOpen = false;
    let isSending = false;
    let messages = [];

    // Base URL resolution:
    // 1. Explicit window global: window.PNK_ASSISTANT_API_BASE
    // 2. Script tag attribute: data-api-base on currentScript
    // 3. Origin of the widget.js script itself (new URL(currentScript.src).origin)
    // 4. Meta tag: meta[name="base-url"] (Bagisto membership storefront)
    // 5. Fallback: window.location.origin
    function resolveBaseUrl() {
        if (window.PNK_ASSISTANT_API_BASE) {
            return window.PNK_ASSISTANT_API_BASE.replace(/\/+$/, '');
        }

        const script = document.currentScript || document.querySelector('script[src*="ai-assistant/widget.js"]');
        if (script) {
            if (script.getAttribute('data-api-base')) {
                return script.getAttribute('data-api-base').replace(/\/+$/, '');
            }
            if (script.src) {
                try {
                    const parsed = new URL(script.src);
                    return parsed.origin;
                } catch (e) {}
            }
        }

        const baseUrlMeta = document.querySelector('meta[name="base-url"]');
        if (baseUrlMeta && baseUrlMeta.getAttribute('content')) {
            return baseUrlMeta.getAttribute('content').replace(/\/+$/, '');
        }

        return window.location.origin;
    }

    const BASE_URL = resolveBaseUrl();

    // Fetch CSRF Token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) return meta.content;
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    }

    // Helper: Escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Format simple Markdown to safe HTML
    function formatMarkdown(text) {
        if (!text) return '';
        let html = escapeHtml(text);

        // Bold: **text**
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

        // Italic: *text*
        html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');

        // Markdown links: [title](url) -> ensure relative links navigate to BASE_URL (e.g. member portal)
        html = html.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+|\/[^\s)]+)\)/g, function (match, label, url) {
            let finalUrl = url;
            if (finalUrl.startsWith('/')) {
                finalUrl = BASE_URL + finalUrl;
            }
            return '<a href="' + finalUrl + '" target="_blank" rel="noopener noreferrer" class="pnk-ai-link">' + label + '</a>';
        });

        // Unordered lists: lines starting with "- " or "* "
        html = html.replace(/(?:^|\n)[-*]\s+(.+)/g, '<div class="pnk-ai-list-item"><span class="pnk-ai-bullet">•</span><span>$1</span></div>');

        // Numbered lists: lines starting with "1. "
        html = html.replace(/(?:^|\n)(\d+)\.\s+(.+)/g, '<div class="pnk-ai-list-item"><span class="pnk-ai-num">$1.</span><span>$2</span></div>');

        // Line breaks
        html = html.replace(/\n\n/g, '<div class="pnk-ai-p-gap"></div>');
        html = html.replace(/\n/g, '<br>');

        return html;
    }

    // Load saved messages
    function loadSavedMessages() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY_MSGS);
            if (raw) {
                messages = JSON.parse(raw);
            }
        } catch (e) {
            messages = [];
        }
    }

    // Save messages
    function saveMessages() {
        try {
            sessionStorage.setItem(STORAGE_KEY_MSGS, JSON.stringify(messages));
        } catch (e) {
            // ignore storage quota errors
        }
    }

    // Inject Styles
    function injectStyles() {
        if (document.getElementById('pnk-ai-assistant-styles')) return;

        const posRight = config.position === 'bottom-left' ? 'left: 24px;' : 'right: 24px;';
        const brandColor = config.theme_color || '#0f4a3e';

        const style = document.createElement('style');
        style.id = 'pnk-ai-assistant-styles';
        style.textContent = `
            /* Widget Root & Container */
            .pnk-ai-widget-root {
                position: fixed;
                bottom: 24px;
                ${posRight}
                z-index: 999990;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                color: #1e293b;
                box-sizing: border-box;
            }
            .pnk-ai-widget-root * {
                box-sizing: border-box;
            }

            /* Launcher Button */
            .pnk-ai-launcher {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background-color: ${brandColor};
                color: #ffffff;
                box-shadow: 0 10px 25px -5px rgba(15, 74, 62, 0.4), 0 8px 10px -6px rgba(15, 74, 62, 0.3);
                border: 2px solid rgba(255, 255, 255, 0.2);
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                outline: none;
            }
            .pnk-ai-launcher:hover {
                transform: scale(1.06);
                box-shadow: 0 14px 28px -4px rgba(15, 74, 62, 0.5);
                background-color: #155e4f;
            }
            .pnk-ai-launcher:active {
                transform: scale(0.96);
            }
            .pnk-ai-launcher-icon,
            .pnk-ai-launcher-close {
                transition: transform 0.25s ease, opacity 0.25s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .pnk-ai-launcher.open .pnk-ai-launcher-icon {
                display: none;
            }
            .pnk-ai-launcher:not(.open) .pnk-ai-launcher-close {
                display: none;
            }

            /* Online Status Dot on Launcher */
            .pnk-ai-status-pulse {
                position: absolute;
                top: 2px;
                right: 2px;
                width: 14px;
                height: 14px;
                background-color: #10b981;
                border: 2px solid #ffffff;
                border-radius: 50%;
            }

            /* Chat Window Container */
            .pnk-ai-chat-window {
                position: absolute;
                bottom: 74px;
                ${config.position === 'bottom-left' ? 'left: 0;' : 'right: 0;'}
                width: 390px;
                max-width: calc(100vw - 32px);
                height: 580px;
                max-height: calc(100vh - 110px);
                background-color: #ffffff;
                border-radius: 16px;
                box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.18), 0 1px 3px rgba(0, 0, 0, 0.08);
                border: 1px solid #e2e8f0;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                transform-origin: bottom ${config.position === 'bottom-left' ? 'left' : 'right'};
                transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease, visibility 0.25s;
                opacity: 0;
                visibility: hidden;
                transform: scale(0.85) translateY(20px);
            }
            .pnk-ai-chat-window.active {
                opacity: 1;
                visibility: visible;
                transform: scale(1) translateY(0);
            }

            /* Window Header */
            .pnk-ai-header {
                background: linear-gradient(135deg, ${brandColor} 0%, #155e4f 100%);
                color: #ffffff;
                padding: 16px 18px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            .pnk-ai-header-left {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .pnk-ai-header-avatar {
                width: 38px;
                height: 38px;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.16);
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(255, 255, 255, 0.25);
            }
            .pnk-ai-header-title {
                font-size: 16px;
                font-weight: 700;
                line-height: 1.2;
                letter-spacing: -0.01em;
            }
            .pnk-ai-header-subtitle {
                font-size: 11px;
                color: rgba(255, 255, 255, 0.82);
                display: flex;
                align-items: center;
                gap: 5px;
                margin-top: 2px;
            }
            .pnk-ai-header-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background-color: #34d399;
            }
            .pnk-ai-header-actions {
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .pnk-ai-icon-btn {
                background: transparent;
                border: none;
                color: rgba(255, 255, 255, 0.85);
                cursor: pointer;
                padding: 6px;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background-color 0.15s ease;
            }
            .pnk-ai-icon-btn:hover {
                background-color: rgba(255, 255, 255, 0.16);
                color: #ffffff;
            }

            /* Messages Area */
            .pnk-ai-body {
                flex: 1;
                overflow-y: auto;
                padding: 18px 16px;
                background-color: #f8fafc;
                display: flex;
                flex-direction: column;
                gap: 14px;
                scroll-behavior: smooth;
            }
            .pnk-ai-body::-webkit-scrollbar {
                width: 5px;
            }
            .pnk-ai-body::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 3px;
            }

            /* FAQ Chips Container */
            .pnk-ai-faqs-wrap {
                margin-top: 4px;
                margin-bottom: 6px;
            }
            .pnk-ai-faqs-title {
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #64748b;
                margin-bottom: 8px;
            }
            .pnk-ai-faqs-chips {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .pnk-ai-faq-chip {
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 8px 12px;
                font-size: 12px;
                font-weight: 500;
                color: #1e293b;
                cursor: pointer;
                text-align: left;
                transition: all 0.15s ease;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
            }
            .pnk-ai-faq-chip:hover {
                border-color: ${brandColor};
                background-color: #f0fdf4;
                color: ${brandColor};
                transform: translateX(2px);
            }
            .pnk-ai-faq-chip svg {
                flex-shrink: 0;
                opacity: 0.5;
            }
            .pnk-ai-faq-chip:hover svg {
                opacity: 1;
                color: ${brandColor};
            }

            /* Message Bubbles */
            .pnk-ai-msg-row {
                display: flex;
                flex-direction: column;
                max-width: 86%;
            }
            .pnk-ai-msg-row.user {
                align-self: flex-end;
                align-items: flex-end;
            }
            .pnk-ai-msg-row.assistant {
                align-self: flex-start;
                align-items: flex-start;
            }
            .pnk-ai-msg-bubble {
                padding: 11px 15px;
                border-radius: 14px;
                font-size: 13.5px;
                line-height: 1.5;
                word-break: break-word;
            }
            .pnk-ai-msg-row.user .pnk-ai-msg-bubble {
                background-color: ${brandColor};
                color: #ffffff;
                border-bottom-right-radius: 4px;
            }
            .pnk-ai-msg-row.assistant .pnk-ai-msg-bubble {
                background-color: #ffffff;
                color: #1e293b;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
                border-bottom-left-radius: 4px;
            }
            .pnk-ai-msg-time {
                font-size: 10px;
                color: #94a3b8;
                margin-top: 4px;
                padding: 0 2px;
            }

            /* Gated Price Notice */
            .pnk-ai-gated-box {
                margin-top: 10px;
                background-color: #fefce8;
                border: 1px solid #fef08a;
                border-radius: 10px;
                padding: 12px;
                color: #854d0e;
                font-size: 12px;
            }
            .pnk-ai-gated-header {
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 6px;
                color: #713f12;
            }
            .pnk-ai-gated-actions {
                display: flex;
                gap: 8px;
                margin-top: 10px;
            }
            .pnk-ai-btn-sm {
                padding: 5px 10px;
                border-radius: 6px;
                font-size: 11.5px;
                font-weight: 600;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: all 0.15s ease;
            }
            .pnk-ai-btn-primary {
                background-color: ${brandColor};
                color: #ffffff;
            }
            .pnk-ai-btn-primary:hover {
                background-color: #155e4f;
            }
            .pnk-ai-btn-secondary {
                background-color: #ffffff;
                border: 1px solid #d1d5db;
                color: #374151;
            }
            .pnk-ai-btn-secondary:hover {
                background-color: #f3f4f6;
            }

            /* Typing Indicator */
            .pnk-ai-typing {
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 10px 14px;
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                border-bottom-left-radius: 4px;
                width: fit-content;
            }
            .pnk-ai-dot {
                width: 7px;
                height: 7px;
                background-color: #94a3b8;
                border-radius: 50%;
                animation: pnkAiBounce 1.4s infinite ease-in-out both;
            }
            .pnk-ai-dot:nth-child(1) { animation-delay: -0.32s; }
            .pnk-ai-dot:nth-child(2) { animation-delay: -0.16s; }
            @keyframes pnkAiBounce {
                0%, 80%, 100% { transform: scale(0); }
                40% { transform: scale(1); }
            }

            /* Input Area */
            .pnk-ai-footer {
                padding: 12px 14px;
                background-color: #ffffff;
                border-top: 1px solid #e2e8f0;
            }
            .pnk-ai-input-form {
                display: flex;
                align-items: center;
                gap: 8px;
                background-color: #f8fafc;
                border: 1px solid #cbd5e1;
                border-radius: 24px;
                padding: 6px 6px 6px 14px;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }
            .pnk-ai-input-form:focus-within {
                border-color: ${brandColor};
                background-color: #ffffff;
                box-shadow: 0 0 0 3px rgba(15, 74, 62, 0.12);
            }
            .pnk-ai-input {
                flex: 1;
                border: none;
                background: transparent;
                font-size: 13.5px;
                color: #1e293b;
                outline: none;
                padding: 4px 0;
            }
            .pnk-ai-input::placeholder {
                color: #94a3b8;
            }
            .pnk-ai-send-btn {
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background-color: ${brandColor};
                color: #ffffff;
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.15s ease;
                flex-shrink: 0;
            }
            .pnk-ai-send-btn:disabled {
                background-color: #cbd5e1;
                cursor: not-allowed;
            }
            .pnk-ai-send-btn:not(:disabled):hover {
                background-color: #155e4f;
                transform: scale(1.05);
            }
            .pnk-ai-footer-note {
                font-size: 10px;
                color: #94a3b8;
                text-align: center;
                margin-top: 8px;
            }

            /* Content Typography & Links */
            .pnk-ai-link {
                color: ${brandColor};
                text-decoration: underline;
                font-weight: 600;
            }
            .pnk-ai-list-item {
                display: flex;
                align-items: flex-start;
                gap: 6px;
                margin-top: 4px;
            }
            .pnk-ai-bullet, .pnk-ai-num {
                font-weight: 700;
                color: ${brandColor};
            }
            .pnk-ai-p-gap {
                height: 8px;
            }

            /* Mobile view adjustments */
            @media (max-width: 480px) {
                .pnk-ai-chat-window {
                    width: calc(100vw - 20px);
                    right: -14px;
                    bottom: 70px;
                    height: calc(100vh - 90px);
                    border-radius: 14px;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Build DOM Elements
    function buildWidgetDOM() {
        const root = document.createElement('div');
        root.className = 'pnk-ai-widget-root';
        root.id = 'pnk-ai-assistant-root';

        // Launcher Button
        const launcher = document.createElement('button');
        launcher.type = 'button';
        launcher.className = 'pnk-ai-launcher';
        launcher.setAttribute('aria-label', 'Open Wholesale Assistant');
        launcher.innerHTML = `
            <div class="pnk-ai-status-pulse"></div>
            <div class="pnk-ai-launcher-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    <path d="M8 10h.01"></path>
                    <path d="M12 10h.01"></path>
                    <path d="M16 10h.01"></path>
                </svg>
            </div>
            <div class="pnk-ai-launcher-close">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
        `;

        // Chat Window
        const windowEl = document.createElement('div');
        windowEl.className = 'pnk-ai-chat-window';
        windowEl.innerHTML = `
            <!-- Header (Strictly "Assistant") -->
            <div class="pnk-ai-header">
                <div class="pnk-ai-header-left">
                    <div class="pnk-ai-header-avatar">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a10 10 0 0 1 10 10c0 5.5-4.5 10-10 10S2 17.5 2 12A10 10 0 0 1 12 2z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="pnk-ai-header-title">Assistant</div>
                        <div class="pnk-ai-header-subtitle">
                            <span class="pnk-ai-header-dot"></span>
                            <span>Wholesale Member Support</span>
                        </div>
                    </div>
                </div>
                <div class="pnk-ai-header-actions">
                    <button type="button" class="pnk-ai-icon-btn" id="pnk-ai-btn-reset" title="Restart conversation">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <path d="M3 3v5h5"></path>
                        </svg>
                    </button>
                    <button type="button" class="pnk-ai-icon-btn" id="pnk-ai-btn-close" title="Close">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Messages Body -->
            <div class="pnk-ai-body" id="pnk-ai-body">
                <!-- Rendered dynamically -->
            </div>

            <!-- Input Form -->
            <div class="pnk-ai-footer">
                <form class="pnk-ai-input-form" id="pnk-ai-form">
                    <input
                        type="text"
                        class="pnk-ai-input"
                        id="pnk-ai-input"
                        placeholder="${escapeHtml(config.placeholder || 'Ask a wholesale question...')}"
                        autocomplete="off"
                    />
                    <button type="submit" class="pnk-ai-send-btn" id="pnk-ai-send" disabled title="Send message">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
                <div class="pnk-ai-footer-note">PurNatureK Wholesale AI • Verified Members Portal</div>
            </div>
        `;

        root.appendChild(windowEl);
        root.appendChild(launcher);
        document.body.appendChild(root);

        // Bind events
        launcher.addEventListener('click', toggleWidget);
        document.getElementById('pnk-ai-btn-close').addEventListener('click', toggleWidget);
        document.getElementById('pnk-ai-btn-reset').addEventListener('click', resetConversation);

        const form = document.getElementById('pnk-ai-form');
        const input = document.getElementById('pnk-ai-input');
        const sendBtn = document.getElementById('pnk-ai-send');

        input.addEventListener('input', function () {
            sendBtn.disabled = !input.value.trim() || isSending;
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = input.value.trim();
            if (text && !isSending) {
                sendMessage(text);
                input.value = '';
                sendBtn.disabled = true;
            }
        });
    }

    // Toggle Open / Close
    function toggleWidget() {
        isOpen = !isOpen;
        const launcher = document.querySelector('.pnk-ai-launcher');
        const windowEl = document.querySelector('.pnk-ai-chat-window');

        if (launcher && windowEl) {
            if (isOpen) {
                launcher.classList.add('open');
                windowEl.classList.add('active');
                sessionStorage.setItem(STORAGE_KEY_STATE, '1');
                setTimeout(() => {
                    const input = document.getElementById('pnk-ai-input');
                    if (input) input.focus();
                }, 200);
            } else {
                launcher.classList.remove('open');
                windowEl.classList.remove('active');
                sessionStorage.removeItem(STORAGE_KEY_STATE);
            }
        }
    }

    // Reset Conversation
    function resetConversation() {
        messages = [];
        saveMessages();
        renderMessages();
    }

    // Render Messages and FAQ chips
    function renderMessages() {
        const body = document.getElementById('pnk-ai-body');
        if (!body) return;

        body.innerHTML = '';

        // Initial welcome message from Assistant
        const welcomeRow = document.createElement('div');
        welcomeRow.className = 'pnk-ai-msg-row assistant';
        welcomeRow.innerHTML = `
            <div class="pnk-ai-msg-bubble">
                ${formatMarkdown(config.welcome_message)}
            </div>
            <div class="pnk-ai-msg-time">Assistant</div>
        `;
        body.appendChild(welcomeRow);

        // Render previous messages
        messages.forEach(msg => {
            const row = document.createElement('div');
            row.className = `pnk-ai-msg-row ${msg.role}`;

            let bubbleContent = formatMarkdown(msg.content);

            // If message flagged as price gated, append gating card
            if (msg.gated) {
                bubbleContent += `
                    <div class="pnk-ai-gated-box">
                        <div class="pnk-ai-gated-header">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <span>Private Wholesale Access</span>
                        </div>
                        <div>Wholesale volume tiers and live pricing are protected for verified commercial partners.</div>
                        <div class="pnk-ai-gated-actions">
                            <a href="${BASE_URL}/customer/login" class="pnk-ai-btn-sm pnk-ai-btn-primary">Sign In</a>
                            <a href="${BASE_URL}/customer/register" class="pnk-ai-btn-sm pnk-ai-btn-secondary">Register Account</a>
                        </div>
                    </div>
                `;
            }

            row.innerHTML = `
                <div class="pnk-ai-msg-bubble">${bubbleContent}</div>
                <div class="pnk-ai-msg-time">${msg.role === 'user' ? 'You' : 'Assistant'}</div>
            `;
            body.appendChild(row);
        });

        // If no user messages yet, show Approved FAQ Prompt Chips
        if (messages.length === 0 && config.faqs && config.faqs.length > 0) {
            const faqsWrap = document.createElement('div');
            faqsWrap.className = 'pnk-ai-faqs-wrap';
            faqsWrap.innerHTML = `
                <div class="pnk-ai-faqs-title">Suggested Wholesale Inquiries</div>
                <div class="pnk-ai-faqs-chips" id="pnk-ai-faqs-chips"></div>
            `;

            const chipsContainer = faqsWrap.querySelector('#pnk-ai-faqs-chips');
            config.faqs.forEach(faq => {
                const questionText = typeof faq === 'string' ? faq : (faq.question || '');
                if (!questionText) return;

                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'pnk-ai-faq-chip';
                chip.innerHTML = `
                    <span>${escapeHtml(questionText)}</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                `;
                chip.addEventListener('click', () => {
                    sendMessage(questionText);
                });
                chipsContainer.appendChild(chip);
            });

            body.appendChild(faqsWrap);
        }

        body.scrollTop = body.scrollHeight;
    }

    // Show Typing Indicator
    function showTypingIndicator() {
        const body = document.getElementById('pnk-ai-body');
        if (!body) return;

        let typing = document.getElementById('pnk-ai-typing-indicator');
        if (!typing) {
            typing = document.createElement('div');
            typing.id = 'pnk-ai-typing-indicator';
            typing.className = 'pnk-ai-msg-row assistant';
            typing.innerHTML = `
                <div class="pnk-ai-typing">
                    <div class="pnk-ai-dot"></div>
                    <div class="pnk-ai-dot"></div>
                    <div class="pnk-ai-dot"></div>
                </div>
            `;
            body.appendChild(typing);
            body.scrollTop = body.scrollHeight;
        }
    }

    // Hide Typing Indicator
    function hideTypingIndicator() {
        const typing = document.getElementById('pnk-ai-typing-indicator');
        if (typing) typing.remove();
    }

    // Send Message
    async function sendMessage(text) {
        if (!text || isSending) return;

        isSending = true;
        const sendBtn = document.getElementById('pnk-ai-send');
        if (sendBtn) sendBtn.disabled = true;

        // Append user message
        messages.push({
            role: 'user',
            content: text,
            gated: false
        });
        saveMessages();
        renderMessages();
        showTypingIndicator();

        try {
            const reqHeaders = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            const csrf = getCsrfToken();
            if (csrf) {
                reqHeaders['X-CSRF-TOKEN'] = csrf;
            }

            const response = await fetch(`${BASE_URL}/api/v1/ai-assistant/chat`, {
                method: 'POST',
                headers: reqHeaders,
                credentials: 'same-origin',
                body: JSON.stringify({ message: text })
            });

            hideTypingIndicator();

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                const errMsg = errData.reply || errData.message || 'Unable to connect to the assistant. Please try again.';
                messages.push({
                    role: 'assistant',
                    content: errMsg,
                    gated: false
                });
            } else {
                const data = await response.json();
                messages.push({
                    role: 'assistant',
                    content: data.reply || 'Thank you for your inquiry. A wholesale specialist will follow up.',
                    gated: Boolean(data.gated)
                });
            }
        } catch (error) {
            hideTypingIndicator();
            messages.push({
                role: 'assistant',
                content: 'Network connection error. Please verify your connection or refresh the page.',
                gated: false
            });
        } finally {
            isSending = false;
            saveMessages();
            renderMessages();
            const input = document.getElementById('pnk-ai-input');
            if (input) {
                input.focus();
            }
        }
    }

    // Initialize Widget
    async function init() {
        try {
            const res = await fetch(`${BASE_URL}/api/v1/ai-assistant/config`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) return;

            const remoteConfig = await res.json();
            if (!remoteConfig.enabled) {
                // Assistant is disabled in admin
                return;
            }

            config = { ...config, ...remoteConfig };
            // Ensure title is strictly "Assistant"
            config.title = 'Assistant';

            loadSavedMessages();
            injectStyles();
            buildWidgetDOM();
            renderMessages();

            // Auto-open if configured or previously open in session
            const wasOpen = sessionStorage.getItem(STORAGE_KEY_STATE) === '1';
            if (wasOpen || config.auto_open) {
                toggleWidget();
            }
        } catch (e) {
            console.warn('[PurNatureK] Assistant widget failed to load:', e);
        }
    }

    // Start on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

