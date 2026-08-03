@extends('layouts.app')

@section('title', '游戏内聊天')

@section('content')
<div class="space-y-4 sm:space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 flex items-center">
                <span class="mr-2">💬</span>游戏内聊天
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">实时查看 MC 服务器玩家聊天记录</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="chatStatus" class="badge text-xs sm:text-sm px-2.5 py-1 border bg-primary-50 text-primary-700 border-primary-200">
                <span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span>
                实时刷新中
            </span>
            @auth
                @if(auth()->user()->isAdmin())
                    <button type="button" id="syncLogBtn" class="btn-secondary no-disable text-xs sm:text-sm">
                        📜 同步游戏日志
                    </button>
                    <button type="button" id="demoMsgBtn" class="btn-secondary no-disable text-xs sm:text-sm">
                        🧪 插入一条测试消息
                    </button>
                @endif
            @endauth
        </div>
    </div>

    <div class="card overflow-hidden">
        @php
    $chatBgUrl = auth()->check() ? auth()->user()->getChatBgUrl() : '';
@endphp
    <div id="chatBody" class="h-[480px] sm:h-[560px] overflow-y-auto p-3 sm:p-4 space-y-1.5 bg-slate-50/50" style="height:480px;overflow-y:auto;overflow-x:hidden;@if($chatBgUrl) background-image: url('{{ $chatBgUrl }}'); background-size: cover; background-position: center; background-blend-mode: overlay; @endif">
            @if($messages->isEmpty())
                <div id="emptyTip" class="h-full flex items-center justify-center text-slate-400 text-sm text-center px-4">
                    <div>
                        <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        暂无聊天记录
                        <p class="mt-1 text-xs text-slate-400">有玩家在游戏内聊天时会自动显示在这里</p>
                    </div>
                </div>
            @endif
@php
$currentUserName = auth()->check() ? auth()->user()->name : '';
$today = now()->format('Y-m-d');
$lastDate = '';
$lastPlayerName = '';
$bubbleMine = 'bg-blue-500 text-white rounded-br-md';
$bubbleOther = 'bg-white shadow-sm text-slate-700 rounded-bl-md';
@endphp
            @foreach($messages as $m)
@php
                    $isMine = $currentUserName && $m->player_name === $currentUserName;
                    $msgDate = $m->timestamp ? $m->timestamp->format('Y-m-d') : '';
                    $showDate = $msgDate && $msgDate !== $lastDate;
                    $lastDate = $msgDate ?: $lastDate;
                    $timeDisplay = $m->timestamp ? $m->timestamp->format('H:i') : '--:--';
                    if ($showDate && $msgDate) {
                        $dateDisplay = $msgDate === $today ? '今天' : $m->timestamp->format('m-d');
                    }
                    $samePlayer = $lastPlayerName === $m->player_name;
                    $showName = !$samePlayer;
                    $lastPlayerName = $m->player_name;
                    $bubbleClass = $isMine ? $bubbleMine : $bubbleOther;
                    $bubbleHtml = '<div class="px-3 py-2 rounded-2xl text-sm leading-relaxed break-words ' . $bubbleClass . '">' . e($m->message) . '</div>';
                @endphp
                @if($showDate && $msgDate)
                <div class="flex justify-center my-2">
                    <span class="text-xs text-slate-400 bg-slate-100/80 px-3 py-0.5 rounded-full">{{ $dateDisplay }}</span>
                </div>
                @endif
                <div class="chat-row flex {{ $isMine ? 'justify-end' : 'justify-start' }} px-2 {{ $showName ? 'py-1' : 'py-0.5' }} items-start" data-id="{{ $m->id }}">
                    @if($showName)
                        @if(!$isMine)
                        <img src="{{ \App\Services\PlayerAvatarService::url($m->player_name, $m->player_uuid) }}" alt="{{ $m->player_name }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-1 ring-slate-200 bg-white flex-shrink-0 mr-2 object-cover">
                        @endif
                        <div class="max-w-[75%] sm:max-w-[65%]">
                            <div class="text-xs text-slate-400 mb-0.5 {{ $isMine ? 'text-right' : 'text-left' }}">{{ $m->player_name }} · {{ $timeDisplay }}</div>
                            {!! $bubbleHtml !!}
                        </div>
                        @if($isMine)
                        <img src="{{ \App\Services\PlayerAvatarService::url($currentUserName, auth()->check() ? auth()->user()->mc_uuid : null) }}" alt="{{ $currentUserName }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-1 ring-slate-200 bg-white flex-shrink-0 ml-2 order-1 object-cover">
                        @endif
                    @else
                        <div class="max-w-[75%] sm:max-w-[65%] {{ $isMine ? 'mr-9 sm:mr-10' : 'ml-9 sm:ml-10' }}">
                            {!! $bubbleHtml !!}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="border-t border-slate-200 px-3 py-2 flex items-center justify-between bg-white">
            <div class="text-xs text-slate-500">
                共 <span id="msgCount" class="text-slate-700 font-medium">{{ $messages->count() }}</span> 条 · <span class="text-primary-600">实时刷新</span>
            </div>
            <button type="button" id="scrollBottomBtn" class="no-disable text-xs text-primary-600 hover:text-primary-700 px-2 py-1 rounded hover:bg-primary-50 transition">
                ↓ 滚动到底部
            </button>
        </div>
    </div>

    @auth
        <div class="card p-3 sm:p-4">
            <form id="sendForm" class="flex flex-col sm:flex-row gap-2" data-no-autodisable>
                <textarea
                    id="sendInput"
                    name="message"
                    maxlength="200"
                    rows="1"
                    autocomplete="off"
                    placeholder="向游戏内发送消息（以你的用户名牢高 [网站] 显示）..."
                    class="input flex-1 px-3 py-2 text-sm resize-none"
                ></textarea>
                <button
                    type="submit"
                    id="sendBtn"
                    class="btn-primary text-sm whitespace-nowrap"
                >
                    发送到游戏
                </button>
            </form>
            <p id="sendHint" class="text-xs text-slate-500 mt-2">提示：消息会以你的用户名义直接发送到游戏内所有在线玩家。</p>
        </div>
    @endauth

    @auth
        @if(auth()->user()->isAdmin())
            <div class="card p-3 sm:p-4 text-xs sm:text-sm text-slate-600">
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
                        <span class="inline-block w-2 h-2 bg-slate-400 rounded-full ml-2"></span> 灰色 = 系统消息（会被跳过）
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
    let currentUserName = '';
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
            green: 'bg-primary-50 text-primary-700 border-primary-200',
            yellow: 'bg-amber-50 text-amber-700 border-amber-200',
            red: 'bg-red-50 text-red-700 border-red-200',
        };
        statusEl.className = 'badge text-xs sm:text-sm px-2.5 py-1 border ' + (colorMap[color] || colorMap.green);
        statusEl.innerHTML = text;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var lastMsgDate = '';
    var lastPlayerName = '';
    var todayStr = (function(){ var d=new Date(); return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); })();
    const BUBBLE_MINE = 'bg-blue-500 text-white rounded-br-md';
    const BUBBLE_OTHER = 'bg-white shadow-sm text-slate-700 rounded-bl-md';
    const AVATAR_CLASS = 'w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-1 ring-slate-200 bg-white flex-shrink-0 object-cover';

    function makeBubble(msg, isMine) {
        return '<div class="px-3 py-2 rounded-2xl text-sm leading-relaxed break-words ' + (isMine ? BUBBLE_MINE : BUBBLE_OTHER) + '">' + escapeHtml(msg) + '</div>';
    }

    // 将 'YYYY-MM-DD' 转为显示用的日期标签：今天 → '今天'，其他 → 'MM-DD'
    function formatDateLabel(dateStr) {
        if (!dateStr || dateStr.length < 10) return dateStr || '';
        if (dateStr === todayStr) return '今天';
        return dateStr.substring(5, 10); // 'MM-DD'
    }

    function addDateLabel(dateStr) {
        var label = document.createElement('div');
        label.className = 'flex justify-center my-2';
        label.innerHTML = '<span class="text-xs text-slate-400 bg-slate-100/80 px-3 py-0.5 rounded-full">' + escapeHtml(dateStr) + '</span>';
        chatBody.appendChild(label);
    }

    // 从时间戳字符串解析出日期和时:分
    function parseTimestamp(ts) {
        if (!ts || typeof ts !== 'string') return { date: '', time: '--:--' };
        if (ts.length >= 16) {
            return { date: ts.substring(0, 10), time: ts.substring(11, 16) };
        }
        if (ts.length >= 5) {
            return { date: '', time: ts.substring(0, 5) };
        }
        return { date: '', time: '--:--' };
    }

    function appendMessage(m) {
        if (emptyTip) emptyTip.remove();
        if (m.id && chatBody.querySelector('.chat-row[data-id="' + m.id + '"]')) return;

        var parsed = parseTimestamp(m.timestamp);
        var timeStr = parsed.time;
        var msgDate = parsed.date;

        if (msgDate && msgDate !== lastMsgDate) {
            addDateLabel(formatDateLabel(msgDate));
            lastMsgDate = msgDate;
            lastPlayerName = '';
        }
        const isMine = currentUserName && m.player_name === currentUserName;
        const samePlayer = m.player_name === lastPlayerName;
        const showName = !samePlayer;
        lastPlayerName = m.player_name;
        const bubble = makeBubble(m.message, isMine);
        const avatarUrl = m.avatar_url || '';
        const avatarImg = avatarUrl ? '<img src="' + escapeHtml(avatarUrl) + '" alt="' + escapeHtml(m.player_name) + '" class="' + AVATAR_CLASS + '">' : '';
        const row = document.createElement('div');
        row.className = 'chat-row flex items-start ' + (isMine ? 'justify-end' : 'justify-start') + ' px-2 ' + (showName ? 'py-1' : 'py-0.5');
        row.dataset.id = m.id;
        var html = '';
        if (showName) {
            html = (isMine ? '' : avatarImg + '<div class="w-2 flex-shrink-0"></div>') +
                '<div class="max-w-[75%] sm:max-w-[65%]">' +
                    '<div class="text-xs text-slate-400 mb-0.5 ' + (isMine ? 'text-right' : 'text-left') + '">' + escapeHtml(m.player_name) + ' · ' + escapeHtml(timeStr) + '</div>' +
                    bubble +
                '</div>' +
                (isMine && avatarImg ? '<div class="w-2 flex-shrink-0"></div>' + avatarImg.replace('flex-shrink-0', 'flex-shrink-0 order-1') : '');
        } else {
            html = '<div class="max-w-[75%] sm:max-w-[65%]' + (isMine ? ' mr-9 sm:mr-10' : ' ml-9 sm:ml-10') + '">' + bubble + '</div>';
        }
        row.innerHTML = html;
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
            setStatus('<span class="inline-block w-2 h-2 bg-amber-500 rounded-full mr-1 animate-pulse"></span> 刷新中...', 'yellow');
            const res = await fetch('{{ route("game-chat.fetch") }}?after_id=' + lastId, { credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.ok) {
                if (data.current_user_name) currentUserName = data.current_user_name;
                (data.messages || []).forEach(appendMessage);
                if (data.last_id > lastId) lastId = data.last_id;
                setStatus('<span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span> 实时刷新中', 'green');
            } else {
                setStatus('<span class="inline-block w-2 h-2 bg-amber-500 rounded-full mr-1"></span> 数据异常', 'yellow');
            }
        } catch (e) {
            setStatus('<span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span> 刷新失败，稍后重试', 'red');
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
                        setStatus('<span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span> 同步成功，新增 ' + data.inserted + ' 条', 'green');
                    } else {
                        setStatus('<span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span> 已同步，暂无新消息', 'green');
                    }
                } else {
                    setStatus('<span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span> ' + (data.message || '同步失败'), 'red');
                }
            } catch(e) {
                syncBtn.disabled = false;
                syncBtn.textContent = '📜 同步游戏日志';
                setStatus('<span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span> 同步异常', 'red');
            }
        });
    }

    // === 发消息到游戏 ===
    if (sendForm) {
        let sending = false;
        function setHint(text, color) {
            sendHint.textContent = text;
            sendHint.className = 'text-xs mt-2 ' + ({
                green: 'text-primary-600', yellow: 'text-amber-600', red: 'text-red-600'
            })[color] || 'text-slate-500';
        }
        sendForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (sending) return;
            const msg = sendInput.value.trim();
            if (!msg) return;

            sending = true;
            sendBtn.disabled = true;
            sendBtn.textContent = '发送中...';
            setHint('正在发送到游戏内玩家...', 'yellow');

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
                    setHint('✓ 已发送到游戏', 'green');
                    autoScroll = true;
                    scrollToBottom(false);
                    setTimeout(function() { scrollToBottom(false); }, 200);
                    setTimeout(function() { scrollToBottom(false); }, 500);
                    setTimeout(function() { setHint('提示：消息会以你的用户名义直接发送到游戏内所有在线玩家。'); }, 3000);
                } else {
                    var errMsg = (data && data.message) ? data.message : '发送失败';
                    if (data && data.errors && data.errors.message) errMsg = data.errors.message[0];
                    setHint('✗ ' + errMsg, 'red');
                }
            } catch(e) {
                setHint('✗ 网络错误：' + e.message, 'red');
            } finally {
                sending = false;
                sendBtn.disabled = false;
                sendBtn.textContent = '发送到游戏';
                try { sendInput.focus(); } catch(e) {}
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
            logMeta.textContent = '路径：' + data.log_path + '　|　共 ' + rows.length + ' 行，其中 ' + chatCount + ' 行被识别为聊天';
        } catch(e) {
            loadBtn.disabled = false;
            logBody.innerHTML = '<div class="text-red-600">读取失败：' + escapeHtml(e.message) + '</div>';
        }
    }

    // 初始滚动到底部（多次延迟确保图片加载后也正确）
    [50, 100, 200, 500, 1000, 1500].forEach(function(d) { setTimeout(function() { scrollToBottom(false); }, d); });
    function onLoadScroll() { scrollToBottom(false); [300, 800].forEach(function(d) { setTimeout(function() { scrollToBottom(false); }, d); }); }
    if (document.readyState === 'complete') { onLoadScroll(); } else { window.addEventListener('load', onLoadScroll); }
    // 启动定时刷新（只拉数据，不读日志，速度快）- 2秒实时刷新
    refreshTimer = setInterval(fetchMessages, 2000);
    // 单独定时同步 MC 日志（5 秒一次，与拉数据分开，避免阻塞发送）
    setInterval(function() {
        fetch('{{ route("chat-sync") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: '{}',
        }).catch(function(){});
    }, 5000);
})();
</script>
@endsection
