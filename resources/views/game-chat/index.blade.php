@extends('layouts.app')

@section('title', '游戏内聊天')

@section('content')
<div class="space-y-4 sm:space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white flex items-center">
                <span class="mr-2">💬</span>游戏内聊天
            </h1>
            <p class="text-gray-400 text-xs sm:text-sm mt-1">实时查看 MC 服务器玩家聊天记录</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="chatStatus" class="text-xs sm:text-sm px-2.5 py-1 rounded-full bg-green-900/40 text-green-300 border border-green-700/50">
                <span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span>
                实时刷新中
            </span>
            @auth
                @if(auth()->user()->isAdmin())
                    <button type="button" id="syncLogBtn" class="text-xs sm:text-sm px-3 py-1.5 rounded-md bg-blue-700/40 text-blue-200 border border-blue-600/50 hover:bg-blue-700/60 transition">
                        📜 同步游戏日志
                    </button>
                    <button type="button" id="demoMsgBtn" class="text-xs sm:text-sm px-3 py-1.5 rounded-md bg-yellow-700/40 text-yellow-200 border border-yellow-600/50 hover:bg-yellow-700/60 transition">
                        🧪 插入一条测试消息
                    </button>
                @endif
            @endauth
        </div>
    </div>

    <div class="mc-card rounded-lg overflow-hidden">
        <div id="chatBody" class="h-[480px] sm:h-[560px] overflow-y-auto p-3 sm:p-4 space-y-1.5 bg-gradient-to-b from-slate-950/70 via-slate-900/40 to-transparent">
            @if($messages->isEmpty())
                <div id="emptyTip" class="h-full flex items-center justify-center text-gray-500 text-sm text-center px-4">
                    <div>
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-600 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        暂无聊天记录
                        <p class="mt-1 text-xs text-gray-600">有玩家在游戏内聊天时会自动显示在这里</p>
                    </div>
                </div>
            @endif
            @foreach($messages as $m)
                <div class="chat-row flex items-start px-2 py-1 rounded hover:bg-white/5 transition" data-id="{{ $m->id }}">
                    <span class="text-primary-400 font-medium flex-shrink-0 px-1">{{ $m->player_name }}</span>
                    <span class="text-gray-400 flex-shrink-0 mr-1">:</span>
                    <span class="text-gray-100 break-words flex-1 leading-relaxed">{{ $m->message }}</span>
                </div>
            @endforeach
        </div>
        <div class="border-t border-gray-700 px-3 py-2 flex items-center justify-between bg-slate-950/40">
            <div class="text-xs text-gray-500">
                共 <span id="msgCount" class="text-gray-300 font-medium">{{ $messages->count() }}</span> 条 · 每 <span class="text-primary-400">5 秒</span> 自动刷新
            </div>
            <button type="button" id="scrollBottomBtn" class="text-xs text-primary-400 hover:text-primary-300 px-2 py-1 rounded hover:bg-primary-900/30 transition">
                ↓ 滚动到底部
            </button>
        </div>
    </div>

    @auth
        <div class="mc-card rounded-lg p-3 sm:p-4">
            <form id="sendForm" class="flex flex-col sm:flex-row gap-2">
                <input
                    type="text"
                    id="sendInput"
                    name="message"
                    maxlength="200"
                    autocomplete="off"
                    placeholder="向游戏内发送消息（以你的用户名牢高 [网站] 显示）..."
                    class="flex-1 px-3 py-2 rounded-md bg-slate-950/60 border border-slate-700 text-gray-100 placeholder-slate-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-sm"
                >
                <button
                    type="submit"
                    id="sendBtn"
                    class="px-4 py-2 rounded-md bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                >
                    发送到游戏
                </button>
            </form>
            <p id="sendHint" class="text-xs text-gray-500 mt-2">提示：消息会通过 RCON 发送到 MC 服务器，所有在线玩家都能看到。</p>
        </div>
    @endauth

    @auth
        @if(auth()->user()->isAdmin())
            <div class="mc-card rounded-lg p-3 sm:p-4 text-xs sm:text-sm text-gray-400">
                <div class="flex items-center justify-between mb-2">
                    <p class="font-medium text-gray-300">🛠️ 管理员工具</p>
                    <button type="button" id="toggleLogPreview" class="text-xs text-primary-400 hover:text-primary-300 px-2 py-1 rounded hover:bg-primary-900/30 transition">
                        ▶ 查看日志解析情况
                    </button>
                </div>
                <div id="logPreviewWrap" class="hidden mt-3 border-t border-gray-700 pt-3">
                    <div class="flex items-center gap-2 mb-2">
                        <button type="button" id="loadLogPreview" class="text-xs px-3 py-1 rounded bg-slate-700/60 text-slate-200 border border-slate-600 hover:bg-slate-700 transition">
                            读取最近 30 行日志
                        </button>
                        <span id="logPreviewMeta" class="text-xs text-gray-500"></span>
                    </div>
                    <div id="logPreviewBody" class="font-mono text-xs space-y-1 max-h-72 overflow-y-auto bg-slate-950/60 p-2 rounded border border-slate-800">
                        <div class="text-gray-600">点击上方按钮加载日志...</div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        <span class="inline-block w-2 h-2 bg-green-400 rounded-full"></span> 绿色 = 识别为聊天消息　
                        <span class="inline-block w-2 h-2 bg-gray-500 rounded-full ml-2"></span> 灰色 = 系统消息（会被跳过）
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
    const msgCountEl = document.getElementById('msgCount');
    const statusEl = document.getElementById('chatStatus');
    const scrollBtn = document.getElementById('scrollBottomBtn');
    const demoBtn = document.getElementById('demoMsgBtn');
    const syncBtn = document.getElementById('syncLogBtn');
    const sendForm = document.getElementById('sendForm');
    const sendInput = document.getElementById('sendInput');
    const sendBtn = document.getElementById('sendBtn');
    const sendHint = document.getElementById('sendHint');
    let lastId = {{ $messages->last()?->id ?? 0 }};
    let totalCount = {{ $messages->count() }};
    let autoScroll = true;
    let refreshTimer = null;

    chatBody.addEventListener('scroll', function() {
        const bottom = chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop;
        autoScroll = bottom < 60;
    });
    function scrollToBottom(smooth) {
        chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
    }
    scrollBtn.addEventListener('click', function() { scrollToBottom(true); });

    function setStatus(text, color) {
        const colorMap = {
            green: 'bg-green-900/40 text-green-300 border-green-700/50',
            yellow: 'bg-yellow-900/40 text-yellow-200 border-yellow-700/50',
            red: 'bg-red-900/40 text-red-300 border-red-700/50',
        };
        statusEl.className = 'text-xs sm:text-sm px-2.5 py-1 rounded-full border ' + (colorMap[color] || colorMap.green);
        statusEl.innerHTML = text;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function appendMessage(m) {
        if (emptyTip) emptyTip.remove();
        // 用 ID 去重，避免同一条消息被重复添加
        if (m.id && chatBody.querySelector('.chat-row[data-id="' + m.id + '"]')) {
            return;
        }
        const row = document.createElement('div');
        row.className = 'chat-row flex items-start px-2 py-1 rounded hover:bg-white/5 transition';
        row.dataset.id = m.id;
        row.innerHTML =
            '<span class="text-primary-400 font-medium flex-shrink-0 px-1">' + escapeHtml(m.player_name) + '</span>' +
            '<span class="text-gray-400 flex-shrink-0 mr-1">:</span>' +
            '<span class="text-gray-100 break-words flex-1 leading-relaxed">' + escapeHtml(m.message) + '</span>';
        chatBody.appendChild(row);
        totalCount++;
        msgCountEl.textContent = totalCount;
        if (autoScroll) scrollToBottom(false);
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    async function fetchMessages() {
        try {
            setStatus('<span class="inline-block w-2 h-2 bg-yellow-400 rounded-full mr-1 animate-pulse"></span> 刷新中...', 'yellow');
            const res = await fetch('{{ route("game-chat.fetch") }}?after_id=' + lastId, { credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.ok) {
                (data.messages || []).forEach(appendMessage);
                if (data.last_id > lastId) lastId = data.last_id;
                setStatus('<span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span> 实时刷新中', 'green');
            } else {
                setStatus('<span class="inline-block w-2 h-2 bg-yellow-400 rounded-full mr-1"></span> 数据异常', 'yellow');
            }
        } catch (e) {
            setStatus('<span class="inline-block w-2 h-2 bg-red-400 rounded-full mr-1"></span> 刷新失败，稍后重试', 'red');
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
                demoBtn.textContent = '🧪 插入一条测试消息';
            } catch(e) { demoBtn.disabled = false; demoBtn.textContent = '🧪 插入一条测试消息'; }
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
                syncBtn.textContent = '📜 同步游戏日志';
                if (data && data.ok) {
                    // 同步成功后立即拉一次新消息
                    await fetchMessages();
                    if (data.inserted > 0) {
                        setStatus('<span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span> 同步成功，新增 ' + data.inserted + ' 条', 'green');
                    } else {
                        setStatus('<span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span> 已同步，暂无新消息', 'green');
                    }
                } else {
                    setStatus('<span class="inline-block w-2 h-2 bg-red-400 rounded-full mr-1"></span> ' + (data.message || '同步失败'), 'red');
                }
            } catch(e) {
                syncBtn.disabled = false;
                syncBtn.textContent = '📜 同步游戏日志';
                setStatus('<span class="inline-block w-2 h-2 bg-red-400 rounded-full mr-1"></span> 同步异常', 'red');
            }
        });
    }

    // === 发消息到游戏 ===
    if (sendForm) {
        let sending = false;
        sendForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            // 防止重复发送（按钮已禁用或正在发送中）
            if (sending) return;
            const msg = sendInput.value.trim();
            if (!msg) return;

            sending = true;
            sendBtn.disabled = true;
            sendInput.disabled = true;
            sendBtn.textContent = '发送中...';
            sendHint.textContent = '正在发送到游戏服务器...';
            sendHint.className = 'text-xs text-yellow-400 mt-2';

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
                    // 把自己发的消息直接加到列表（appendMessage 内部有 ID 去重）
                    if (data.record) appendMessage(data.record);
                    sendInput.value = '';
                    sendHint.textContent = '✓ 已发送到游戏';
                    sendHint.className = 'text-xs text-green-400 mt-2';
                    setTimeout(() => {
                        sendHint.textContent = '提示：消息会通过 RCON 发送到 MC 服务器，所有在线玩家都能看到。';
                        sendHint.className = 'text-xs text-gray-500 mt-2';
                    }, 3000);
                } else {
                    let errMsg = (data && data.message) ? data.message : '发送失败';
                    if (data && data.errors && data.errors.message) {
                        errMsg = data.errors.message[0];
                    }
                    sendHint.textContent = '✗ ' + errMsg;
                    sendHint.className = 'text-xs text-red-400 mt-2';
                }
            } catch(e) {
                sendHint.textContent = '✗ 网络错误：' + e.message;
                sendHint.className = 'text-xs text-red-400 mt-2';
            } finally {
                sending = false;
                sendBtn.disabled = false;
                sendInput.disabled = false;
                sendBtn.textContent = '发送到游戏';
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
        logBody.innerHTML = '<div class="text-yellow-400">读取中...</div>';
        loadBtn.disabled = true;
        try {
            const res = await fetch('{{ route("chat-log-preview") }}', { credentials: 'same-origin' });
            const data = await res.json();
            loadBtn.disabled = false;
            if (!data.ok) {
                logBody.innerHTML = '<div class="text-red-400">错误：' + escapeHtml(data.error || '未知') + '</div>';
                return;
            }
            logMeta.textContent = '路径：' + data.log_path;
            const rows = data.rows || [];
            if (rows.length === 0) {
                logBody.innerHTML = '<div class="text-gray-500">日志为空</div>';
                return;
            }
            let chatCount = 0;
            logBody.innerHTML = '';
            rows.forEach(function(r) {
                const div = document.createElement('div');
                const isChat = r.is_chat;
                if (isChat) chatCount++;
                div.className = 'flex items-start gap-2 ' + (isChat ? 'text-green-300' : 'text-gray-500');
                const dot = '<span class="inline-block w-2 h-2 mt-1.5 rounded-full ' + (isChat ? 'bg-green-400' : 'bg-gray-600') + ' flex-shrink-0"></span>';
                const text = '<span class="break-all">' + escapeHtml(r.raw) + '</span>';
                let parsed = '';
                if (isChat && r.parsed) {
                    parsed = '<span class="text-blue-400 ml-2">→ [' + escapeHtml(r.parsed.player) + '] ' + escapeHtml(r.parsed.message) + '</span>';
                }
                div.innerHTML = dot + '<div class="flex-1">' + text + parsed + '</div>';
                logBody.appendChild(div);
            });
            logMeta.textContent = '路径：' + data.log_path + '　|　共 ' + rows.length + ' 行，其中 ' + chatCount + ' 行被识别为聊天';
        } catch(e) {
            loadBtn.disabled = false;
            logBody.innerHTML = '<div class="text-red-400">读取失败：' + escapeHtml(e.message) + '</div>';
        }
    }

    // 初始滚动到底部
    scrollToBottom(false);
    // 启动定时刷新（只拉数据，不读日志，速度快）
    refreshTimer = setInterval(fetchMessages, 5000);
    // 单独定时同步 MC 日志（10 秒一次，与拉数据分开，避免阻塞发送）
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
