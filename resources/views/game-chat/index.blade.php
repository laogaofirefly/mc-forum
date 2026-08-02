@extends('layouts.app')

@section('title', '游戏内聊天')

@section('content')
<style>
    .chat-bubble {
        max-width: 75%;
        padding: 8px 12px;
        border-radius: 12px;
        word-break: break-word;
        line-height: 1.5;
        font-size: 14px;
        position: relative;
    }
    .chat-bubble.others {
        background: #fff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-top-left-radius: 4px;
    }
    .chat-bubble.self {
        background: #10b981;
        color: #fff;
        border-top-right-radius: 4px;
    }
    .chat-bubble.others {
        background: #fff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-top-left-radius: 4px;
    }
    .chat-bubble.self {
        background: #10b981;
        color: #fff;
        border-top-right-radius: 4px;
    }
    .chat-name {
        font-size: 12px;
        color: #94a3b8;
        margin-bottom: 3px;
    }
    .chat-row-qq {
        display: flex;
        flex-direction: column;
        margin-bottom: 14px;
    }
    .chat-row-qq.self {
        align-items: flex-end;
    }
    .chat-row-qq.self .chat-name {
        text-align: right;
    }
</style>

<div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-900">MC 群聊</h1>
                <p class="text-slate-500 text-xs">{{ $messages->count() }} 条消息 · 实时同步游戏内聊天</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span id="chatStatus" class="badge text-xs px-2.5 py-1 border bg-primary-50 text-primary-700 border-primary-200">
                <span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span>
                在线
            </span>
            @auth
                @if(auth()->user()->isAdmin())
                    <button type="button" id="syncLogBtn" class="btn-secondary no-disable text-xs">📜 同步</button>
                    <button type="button" id="demoMsgBtn" class="btn-secondary no-disable text-xs">🧪 测试</button>
                @endif
            @endauth
        </div>
    </div>

    <div class="card overflow-hidden flex flex-col" style="height: calc(100vh - 280px); min-height: 420px;">
        <div id="chatBody" class="flex-1 overflow-y-auto p-3 sm:p-4 bg-slate-50">
            @if($messages->isEmpty())
                <div id="emptyTip" class="h-full flex items-center justify-center text-slate-400 text-sm text-center px-4">
                    <div>
                        <svg class="w-14 h-14 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="font-medium text-slate-500">还没有聊天消息</p>
                        <p class="mt-1 text-xs">玩家在游戏内说话或你在下方发消息就会出现在这里</p>
                    </div>
                </div>
            @endif
            @foreach($messages as $m)
                @php
                    $isSelf = auth()->check() && $m->player_name === auth()->user()->name;
                @endphp
                <div class="chat-row-qq {{ $isSelf ? 'self' : '' }}" data-id="{{ $m->id }}">
                    <div class="chat-name">{{ $m->player_name }}</div>
                    <div class="chat-bubble {{ $isSelf ? 'self' : 'others' }}">{{ $m->message }}</div>
                </div>
            @endforeach
        </div>
    </div>

    @auth
        <div class="card p-2.5 sm:p-3">
            <form id="sendForm" class="flex items-end gap-2">
                <textarea
                    id="sendInput"
                    name="message"
                    maxlength="200"
                    rows="1"
                    autocomplete="off"
                    placeholder="发送消息到游戏内..."
                    class="input flex-1 px-3 py-2 text-sm resize-none"
                    style="max-height: 100px;"
                ></textarea>
                <button type="submit" id="sendBtn" class="btn-primary text-sm px-5 py-2.5 whitespace-nowrap">
                    发送
                </button>
            </form>
            <p id="sendHint" class="text-xs text-slate-500 mt-1.5 px-1">以 {{ auth()->user()->name }} 的名义发送给游戏内所有在线玩家</p>
        </div>
    @else
        <div class="card p-4 text-center text-sm text-slate-500">
            <a href="{{ route('login') }}" class="link-primary font-medium">登录</a> 后可向游戏内发送消息
        </div>
    @endauth

    @auth
        @if(auth()->user()->isAdmin())
            <div class="card p-3 text-xs sm:text-sm text-slate-600">
                <div class="flex items-center justify-between mb-2">
                    <p class="font-medium text-slate-900">🛠️ 管理员工具</p>
                    <button type="button" id="toggleLogPreview" class="no-disable text-xs text-primary-600 hover:text-primary-700 px-2 py-1 rounded hover:bg-primary-50 transition">
                        ▶ 查看日志解析情况
                    </button>
                </div>
                <div id="logPreviewWrap" class="hidden mt-3 border-t border-slate-200 pt-3">
                    <div class="flex items-center gap-2 mb-2">
                        <button type="button" id="loadLogPreview" class="btn-secondary no-disable text-xs">
                            读取最近 30 行日志
                        </button>
                        <span id="logPreviewMeta" class="text-xs text-slate-500"></span>
                    </div>
                    <div id="logPreviewBody" class="card bg-slate-50 font-mono text-xs space-y-1 max-h-72 overflow-y-auto p-2">
                        <div class="text-slate-400">点击上方按钮加载日志...</div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        <span class="inline-block w-2 h-2 bg-primary-500 rounded-full"></span> 绿色 = 识别为聊天消息
                        <span class="inline-block w-2 h-2 bg-slate-400 rounded-full ml-3"></span> 灰色 = 系统消息（跳过）
                    </p>
                </div>
            </div>
        @endif
    @endauth
</div>

<script>
(function() {
    const chatBody = document.getElementById('chatBody');
    const emptyTip = document.getElementById('emptyTip');
    const statusEl = document.getElementById('chatStatus');
    const demoBtn = document.getElementById('demoMsgBtn');
    const syncBtn = document.getElementById('syncLogBtn');
    const sendForm = document.getElementById('sendForm');
    const sendInput = document.getElementById('sendInput');
    const sendBtn = document.getElementById('sendBtn');
    const sendHint = document.getElementById('sendHint');
    const currentUser = {{ auth()->check() ? json_encode(auth()->user()->name) : 'null' }};
    let lastId = {{ $messages->last()?->id ?? 0 }};
    let autoScroll = true;

    chatBody.addEventListener('scroll', function() {
        const bottom = chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop;
        autoScroll = bottom < 60;
    });
    function scrollToBottom(smooth) {
        chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
    }

    function setStatus(text, color) {
        const colorMap = {
            green: 'bg-primary-50 text-primary-700 border-primary-200',
            yellow: 'bg-amber-50 text-amber-700 border-amber-200',
            red: 'bg-red-50 text-red-700 border-red-200',
        };
        statusEl.className = 'badge text-xs px-2.5 py-1 border ' + (colorMap[color] || colorMap.green);
        statusEl.innerHTML = text;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function appendMessage(m) {
        if (emptyTip) emptyTip.remove();
        if (m.id && chatBody.querySelector('.chat-row-qq[data-id="' + m.id + '"]')) {
            return;
        }
        const isSelf = currentUser && m.player_name === currentUser;
        const row = document.createElement('div');
        row.className = 'chat-row-qq' + (isSelf ? ' self' : '');
        row.dataset.id = m.id;

        const bubbleClass = isSelf ? 'self' : 'others';

        row.innerHTML =
            '<div class="chat-name">' + escapeHtml(m.player_name) + '</div>' +
            '<div class="chat-bubble ' + bubbleClass + '">' + escapeHtml(m.message) + '</div>';
        chatBody.appendChild(row);
        if (autoScroll) scrollToBottom(false);
    }

    async function fetchMessages() {
        try {
            setStatus('<span class="inline-block w-2 h-2 bg-amber-500 rounded-full mr-1 animate-pulse"></span> 刷新中', 'yellow');
            const res = await fetch('{{ route("game-chat.fetch") }}?after_id=' + lastId, { credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.ok) {
                (data.messages || []).forEach(appendMessage);
                if (data.last_id > lastId) lastId = data.last_id;
                setStatus('<span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span> 在线', 'green');
            } else {
                setStatus('<span class="inline-block w-2 h-2 bg-amber-500 rounded-full mr-1"></span> 数据异常', 'yellow');
            }
        } catch (e) {
            setStatus('<span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span> 刷新失败', 'red');
        }
    }

    if (demoBtn) {
        demoBtn.addEventListener('click', async function() {
            try {
                demoBtn.disabled = true;
                demoBtn.textContent = '发送中...';
                const res = await fetch('{{ route("game-chat.demo") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: '{}',
                });
                const data = await res.json();
                if (data && data.ok) appendMessage(data.message);
                demoBtn.disabled = false;
                demoBtn.textContent = '🧪 测试';
            } catch(e) { demoBtn.disabled = false; demoBtn.textContent = '🧪 测试'; }
        });
    }

    if (syncBtn) {
        syncBtn.addEventListener('click', async function() {
            try {
                syncBtn.disabled = true;
                syncBtn.textContent = '同步中...';
                const res = await fetch('{{ route("chat-sync") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: '{}',
                });
                const data = await res.json();
                syncBtn.disabled = false;
                syncBtn.textContent = '📜 同步';
                if (data && data.ok) {
                    await fetchMessages();
                    if (data.inserted > 0) {
                        setStatus('<span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span> 新增 ' + data.inserted + ' 条', 'green');
                    } else {
                        setStatus('<span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span> 已同步', 'green');
                    }
                } else {
                    setStatus('<span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span> ' + (data.message || '同步失败'), 'red');
                }
            } catch(e) {
                syncBtn.disabled = false;
                syncBtn.textContent = '📜 同步';
                setStatus('<span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span> 同步异常', 'red');
            }
        });
    }

    // 发送框自动增高
    if (sendInput) {
        sendInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
        // Ctrl+Enter 发送
        sendInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                sendForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // === 发消息到游戏 ===
    if (sendForm) {
        let sending = false;
        sendForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (sending) return;
            const msg = sendInput.value.trim();
            if (!msg) return;

            sending = true;
            sendBtn.disabled = true;
            sendInput.disabled = true;
            sendBtn.textContent = '发送中';
            sendHint.textContent = '正在发送到游戏内玩家...';
            sendHint.className = 'text-xs text-amber-600 mt-1.5 px-1';

            try {
                const formData = new FormData();
                formData.append('message', msg);
                const res = await fetch('{{ route("game-chat.send") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData,
                });
                const data = await res.json();

                if (data && data.ok) {
                    if (data.record) appendMessage(data.record);
                    sendInput.value = '';
                    sendInput.style.height = 'auto';
                    sendHint.textContent = '✓ 已发送到游戏';
                    sendHint.className = 'text-xs text-primary-600 mt-1.5 px-1';
                    setTimeout(() => {
                        sendHint.textContent = '以 {{ auth()->user()->name }} 的名义发送给游戏内所有在线玩家';
                        sendHint.className = 'text-xs text-slate-500 mt-1.5 px-1';
                    }, 3000);
                } else {
                    let errMsg = (data && data.message) ? data.message : '发送失败';
                    if (data && data.errors && data.errors.message) {
                        errMsg = data.errors.message[0];
                    }
                    sendHint.textContent = '✗ ' + errMsg;
                    sendHint.className = 'text-xs text-red-600 mt-1.5 px-1';
                }
            } catch(e) {
                sendHint.textContent = '✗ 网络错误：' + e.message;
                sendHint.className = 'text-xs text-red-600 mt-1.5 px-1';
            } finally {
                sending = false;
                sendBtn.disabled = false;
                sendInput.disabled = false;
                sendBtn.textContent = '发送';
                sendInput.focus();
            }
        });
    }

    // === 日志预览面板 ===
    const toggleBtn = document.getElementById('toggleLogPreview');
    const logWrap = document.getElementById('logPreviewWrap');
    const loadBtn = document.getElementById('loadLogPreview');
    const logBody = document.getElementById('logPreviewBody');
    const logMeta = document.getElementById('logPreviewMeta');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            logWrap.classList.toggle('hidden');
            toggleBtn.textContent = logWrap.classList.contains('hidden') ? '▶ 查看日志解析情况' : '▼ 收起日志预览';
            if (!logWrap.classList.contains('hidden') && logBody.children.length <= 1) {
                loadLogPreview();
            }
        });
    }

    if (loadBtn) {
        loadBtn.addEventListener('click', loadLogPreview);
    }

    async function loadLogPreview() {
        logBody.innerHTML = '<div class="text-amber-600">读取中...</div>';
        loadBtn.disabled = true;
        try {
            const res = await fetch('{{ route("chat-log-preview") }}', { credentials: 'same-origin' });
            const data = await res.json();
            loadBtn.disabled = false;
            if (!data.ok) {
                logBody.innerHTML = '<div class="text-red-600">错误：' + escapeHtml(data.error || '未知') + '</div>';
                return;
            }
            logMeta.textContent = '路径：' + data.log_path;
            const rows = data.rows || [];
            if (rows.length === 0) {
                logBody.innerHTML = '<div class="text-slate-500">日志为空</div>';
                return;
            }
            let chatCount = 0;
            logBody.innerHTML = '';
            rows.forEach(function(r) {
                const div = document.createElement('div');
                const isChat = r.is_chat;
                if (isChat) chatCount++;
                div.className = 'flex items-start gap-2 ' + (isChat ? 'text-primary-700' : 'text-slate-500');
                const dot = '<span class="inline-block w-2 h-2 mt-1.5 rounded-full ' + (isChat ? 'bg-primary-500' : 'bg-slate-400') + ' flex-shrink-0"></span>';
                const text = '<span class="break-all">' + escapeHtml(r.raw) + '</span>';
                let parsed = '';
                if (isChat && r.parsed) {
                    parsed = '<span class="text-primary-600 ml-2">→ [' + escapeHtml(r.parsed.player) + '] ' + escapeHtml(r.parsed.message) + '</span>';
                }
                div.innerHTML = dot + '<div class="flex-1">' + text + parsed + '</div>';
                logBody.appendChild(div);
            });
            logMeta.textContent = '路径：' + data.log_path + '　|　共 ' + rows.length + ' 行，' + chatCount + ' 行聊天';
        } catch(e) {
            loadBtn.disabled = false;
            logBody.innerHTML = '<div class="text-red-600">读取失败：' + escapeHtml(e.message) + '</div>';
        }
    }

    // 初始滚动到底部
    scrollToBottom(false);
    // 启动定时刷新
    setInterval(fetchMessages, 5000);
    // 单独定时同步 MC 日志
    setInterval(function() {
        fetch('{{ route("chat-sync") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: '{}',
        }).catch(function(){});
    }, 10000);
})();
</script>
@endsection
