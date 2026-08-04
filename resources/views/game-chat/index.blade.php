@extends('layouts.app')

@section('title', '游戏内聊天')

@section('content')
<div class="space-y-4 sm:space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="page-title text-slate-900 flex items-center">
                @include('layouts.partials.icons', ['name' => 'chat', 'class' => 'w-6 h-6 mr-2 flex-shrink-0'])游戏内聊天
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">实时查看 MC 服务器玩家聊天记录</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="chatStatus" class="badge text-xs sm:text-sm px-2.5 py-1 border bg-primary-50 text-primary-700 border-primary-200">
                <span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span>
                在线
            </span>
            
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
                $avatarFallback = \App\Services\PlayerAvatarService::initialAvatar($m->player_name);
                @endphp
                @if($showDate && $msgDate)
                <div class="flex justify-center my-2">
                    <span class="text-xs text-slate-400 bg-slate-100/80 px-3 py-0.5 rounded-full">{{ $dateDisplay }}</span>
                </div>
                @endif
                <div class="chat-row flex {{ $isMine ? 'justify-end' : 'justify-start' }} px-2 {{ $showName ? 'py-1' : 'py-0.5' }} items-start" data-id="{{ $m->id }}">
                    @if($showName)
                        @if(!$isMine)
                        <img src="{{ \App\Services\PlayerAvatarService::url($m->player_name, $m->player_uuid) }}" alt="{{ $m->player_name }}" data-fallback="{{ $avatarFallback }}" onerror="var f=this.getAttribute('data-fallback');if(f){this.src=f;this.onerror=null;}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-1 ring-slate-200 bg-white flex-shrink-0 mr-2 object-cover">
                        @endif
                        <div class="max-w-[75%] sm:max-w-[65%]">
                            <div class="text-xs text-slate-400 mb-0.5 {{ $isMine ? 'text-right' : 'text-left' }}">{{ $m->player_name }} · {{ $timeDisplay }}</div>
                            {!! $bubbleHtml !!}
                        </div>
                        @if($isMine)
                        <img src="{{ \App\Services\PlayerAvatarService::url($currentUserName, auth()->check() ? auth()->user()->mc_uuid : null) }}" alt="{{ $currentUserName }}" data-fallback="{{ \App\Services\PlayerAvatarService::initialAvatar($currentUserName) }}" onerror="var f=this.getAttribute('data-fallback');if(f){this.src=f;this.onerror=null;}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-1 ring-slate-200 bg-white flex-shrink-0 ml-2 order-1 object-cover">
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
                共 <span id="msgCount" class="text-slate-700 font-medium">{{ $messages->count() }}</span> 条
            </div>
            <button type="button" id="scrollBottomBtn" class="no-disable text-xs text-primary-600 hover:text-primary-700 px-2 py-1 rounded hover:bg-primary-50 transition flex items-center gap-1">
                @include('layouts.partials.icons', ['name' => 'chevron-down', 'class' => 'w-3.5 h-3.5'])底部
            </button>
        </div>
    </div>

    @auth
        <div class="card p-3 sm:p-4">
            <div class="flex items-center gap-2">
                <textarea
                    id="sendInput"
                    name="message"
                    maxlength="200"
                    rows="1"
                    autocomplete="off"
                    placeholder="向游戏内发送消息（以你的用户名 [网站] 显示）..."
                    class="input flex-1 px-3 py-2 text-sm resize-none"
                ></textarea>
                <button
                    type="button"
                    id="sendBtn"
                    class="btn-primary text-sm flex-shrink-0 w-10 h-10 p-0 flex items-center justify-center"
                    title="发送 (Enter)"
                >
                    @include('layouts.partials.icons', ['name' => 'send', 'class' => 'w-5 h-5'])
                </button>
            </div>
            <p id="sendHint" class="text-xs text-slate-500 mt-2">提示：消息会以你的用户名义直接发送到游戏内所有在线玩家。</p>
        </div>
    @endauth

    
</div>

<script>
(function() {
    const chatBody = document.getElementById('chatBody');
    const emptyTip = document.getElementById('emptyTip');
    const msgCountEl = document.getElementById('msgCount');
    const statusEl = document.getElementById('chatStatus');
    const scrollBtn = document.getElementById('scrollBottomBtn');
    const sendInput = document.getElementById('sendInput');
    const sendBtn = document.getElementById('sendBtn');
    const sendHint = document.getElementById('sendHint');
    let currentUserName = '';
    let lastId = {{ $messages->last()?->id ?? 0 }};
    let totalCount = {{ $messages->count() }};
    let autoScroll = true;
    let refreshTimer = null;

    // ========== localStorage 缓存 lastId ==========
    const CHAT_CACHE_KEY = 'mc_chat_last_id';
    (function() {
        try {
            const cached = parseInt(localStorage.getItem(CHAT_CACHE_KEY), 10);
            if (cached > lastId) lastId = cached;
        } catch(e) {}
    })();
    function saveChatCache() {
        try { localStorage.setItem(CHAT_CACHE_KEY, String(lastId)); } catch(e) {}
    }

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
        const avatarFallback = m.avatar_fallback || '';
        const avatarImg = avatarUrl ? '<img src="' + escapeHtml(avatarUrl) + '" alt="' + escapeHtml(m.player_name) + '" data-fallback="' + escapeHtml(avatarFallback) + '" onerror="var f=this.getAttribute(\'data-fallback\');if(f){this.src=f;this.onerror=null;}" class="' + AVATAR_CLASS + '">' : '';
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
            // 静默刷新，不显示状态变化
            const res = await fetch('{{ route("game-chat.fetch") }}?after_id=' + lastId, { credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.ok) {
                if (data.current_user_name) currentUserName = data.current_user_name;
                (data.messages || []).forEach(appendMessage);
                if (data.last_id > lastId) lastId = data.last_id;
                saveChatCache();
            }
        } catch (e) {
            // 静默失败
        }
    }

    // === 发消息到游戏（支持 Enter 键发送） ===
    if (sendBtn && sendInput) {
        let sending = false;
        function setHint(text, color) {
            sendHint.textContent = text;
            sendHint.className = 'text-xs mt-2 ' + ({
                green: 'text-primary-600', yellow: 'text-amber-600', red: 'text-red-600'
            })[color] || 'text-slate-500';
        }

        async function doSend() {
            if (sending) return;
            const msg = sendInput.value.trim();
            if (!msg) return;

            sending = true;
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
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
                    setHint('已发送到游戏', 'green');
                    autoScroll = true;
                    scrollToBottom(false);
                    setTimeout(function() { scrollToBottom(false); }, 200);
                    setTimeout(function() { scrollToBottom(false); }, 500);
                    setTimeout(function() { setHint('提示：消息会以你的用户名义直接发送到游戏内所有在线玩家。'); }, 3000);
                } else {
                    var errMsg = (data && data.message) ? data.message : '发送失败';
                    if (data && data.errors && data.errors.message) errMsg = data.errors.message[0];
                    setHint(errMsg, 'red');
                }
            } catch(e) {
                setHint('网络错误：' + e.message, 'red');
            } finally {
                sending = false;
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>';
                try { sendInput.focus(); } catch(e) {}
            }
        }

        sendBtn.addEventListener('click', doSend);

        // 回车发送（Shift+Enter 换行）
        sendInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                doSend();
            }
        });
    }

    // 初始滚动到底部（多次延迟确保图片加载后也正确）
    [50, 100, 200, 500, 1000, 1500].forEach(function(d) { setTimeout(function() { scrollToBottom(false); }, d); });
    function onLoadScroll() { scrollToBottom(false); [300, 800].forEach(function(d) { setTimeout(function() { scrollToBottom(false); }, d); }); }
    if (document.readyState === 'complete') { onLoadScroll(); } else { window.addEventListener('load', onLoadScroll); }
    // 启动定时刷新（只拉数据，不读日志，速度快）- 2秒实时刷新
    refreshTimer = setInterval(fetchMessages, 2000);
})();
</script>
@endsection
