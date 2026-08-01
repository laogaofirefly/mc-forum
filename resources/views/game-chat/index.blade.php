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
                    <span class="text-gray-500 text-xs flex-shrink-0 pt-0.5 tabular-nums w-14 sm:w-16">{{ $m->timestamp?->format('H:i:s') ?? now()->format('H:i:s') }}</span>
                    <span class="text-primary-400 font-medium flex-shrink-0 px-1 truncate max-w-[25%] sm:max-w-[20%]">{{ $m->player_name }}</span>
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
        @if(auth()->user()->isAdmin())
            <div class="mc-card rounded-lg p-3 sm:p-4 text-xs sm:text-sm text-gray-400">
                <p class="font-medium text-gray-300 mb-1">🛠️ 管理员接入说明</p>
                <p>将 MC 服务器的聊天记录通过 Webhook / RCON 插件同步到 <code class="text-primary-400 bg-primary-900/30 px-1.5 py-0.5 rounded">/api/game-chat/send</code>，或在插件中直接向数据库 <code class="text-primary-400 bg-primary-900/30 px-1.5 py-0.5 rounded">game_chat_messages</code> 表插入记录即可。</p>
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
        const row = document.createElement('div');
        row.className = 'chat-row flex items-start px-2 py-1 rounded hover:bg-white/5 transition';
        row.dataset.id = m.id;
        row.innerHTML =
            '<span class="text-gray-500 text-xs flex-shrink-0 pt-0.5 tabular-nums w-14 sm:w-16">' + (m.timestamp || '') + '</span>' +
            '<span class="text-primary-400 font-medium flex-shrink-0 px-1 truncate max-w-[25%] sm:max-w-[20%]">' + escapeHtml(m.player_name) + '</span>' +
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

    // 初始滚动到底部
    scrollToBottom(false);
    // 启动定时刷新
    refreshTimer = setInterval(fetchMessages, 5000);
})();
</script>
@endsection
