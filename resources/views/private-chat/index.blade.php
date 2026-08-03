@extends('layouts.app')

@section('title', '私聊')

@section('content')
<div class="space-y-4 sm:space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 flex items-center">
                <span class="mr-2">✉️</span>私聊
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">与其他用户一对一私密聊天</p>
        </div>
        <span id="chatStatus" class="badge text-xs sm:text-sm px-2.5 py-1 border bg-primary-50 text-primary-700 border-primary-200">
            <span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span>
            实时刷新中
        </span>
    </div>

    <div class="card overflow-hidden">
        {{-- 用户选择区域 --}}
        <div class="border-b border-slate-200 px-3 py-2.5 bg-white flex items-center gap-2">
            <div class="relative flex-1" id="contactSelectorWrap">
                <button type="button" id="contactSelectorBtn" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:border-primary-300 transition text-sm">
                    @if($chatUser)
                        <img src="{{ $chatUser->getAvatarUrl() }}" alt="{{ $chatUser->name }}" class="w-6 h-6 rounded-full">
                        <span class="font-medium">{{ $chatUser->name }}</span>
                    @else
                        <span class="text-slate-400">选择聊天对象...</span>
                    @endif
                    <svg class="w-4 h-4 ml-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="contactDropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-20 max-h-72 overflow-y-auto">
                    <div class="p-2 sticky top-0 bg-white border-b border-slate-100">
                        <input type="text" id="contactSearchInput" placeholder="搜索用户..." class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:border-primary-400 focus:outline-none">
                    </div>
                    <div id="contactList" class="py-1">
                        @if($contacts->isNotEmpty())
                            <div class="px-3 py-1.5 text-xs text-slate-400 font-medium">最近聊天</div>
                            @foreach($contacts as $contact)
                                <button type="button" class="contact-item w-full flex items-center gap-2.5 px-3 py-2 text-sm hover:bg-slate-50 transition text-left" data-id="{{ $contact->id }}" data-name="{{ $contact->name }}" data-avatar="{{ $contact->getAvatarUrl() }}">
                                    <img src="{{ $contact->getAvatarUrl() }}" alt="{{ $contact->name }}" class="w-7 h-7 rounded-full">
                                    <span class="text-slate-700">{{ $contact->name }}</span>
                                </button>
                            @endforeach
                            <div class="px-3 py-1.5 text-xs text-slate-400 font-medium border-t border-slate-100 mt-1 pt-2">其他用户</div>
                        @endif
                        <div id="contactUserList">
                            @foreach($users as $u)
                                <button type="button" class="contact-item w-full flex items-center gap-2.5 px-3 py-2 text-sm hover:bg-slate-50 transition text-left" data-id="{{ $u->id }}" data-name="{{ $u->name }}" data-avatar="{{ $u->getAvatarUrl() }}">
                                    <img src="{{ $u->getAvatarUrl() }}" alt="{{ $u->name }}" class="w-7 h-7 rounded-full">
                                    <span class="text-slate-700">{{ $u->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @if($chatUser)
                <a href="{{ route('profile.show', $chatUser) }}" class="flex-shrink-0 text-xs text-primary-600 hover:text-primary-700 px-2 py-1 rounded hover:bg-primary-50 transition">查看主页</a>
            @endif
        </div>

        {{-- 聊天区域 --}}
        <div id="chatBody" class="h-[420px] sm:h-[500px] overflow-y-auto p-3 sm:p-4 space-y-1.5 bg-slate-50/50" style="height:420px;overflow-y:auto;overflow-x:hidden;">
            @if(!$chatUser)
                <div class="h-full flex items-center justify-center text-slate-400 text-sm text-center px-4">
                    <div>
                        <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        请选择一位用户开始私聊
                        <p class="mt-1 text-xs text-slate-400">点击上方搜索框查找用户</p>
                    </div>
                </div>
            @elseif($messages->isEmpty())
                <div class="h-full flex items-center justify-center text-slate-400 text-sm text-center px-4">
                    <div>
                        <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        暂无聊天记录
                        <p class="mt-1 text-xs text-slate-400">发送第一条消息给 {{ $chatUser->name }}</p>
                    </div>
                </div>
            @endif
@php
$currentUserId = auth()->id();
$currentUserName = auth()->user()->name;
$today = now()->format('Y-m-d');
$lastDate = '';
$lastPlayerName = '';
$bubbleMine = 'bg-blue-500 text-white rounded-br-md';
$bubbleOther = 'bg-white shadow-sm text-slate-700 rounded-bl-md';
@endphp
            @if($chatUser)
                @foreach($messages as $m)
@php
                    $isMine = $m->sender_id === $currentUserId;
                    $msgDate = $m->created_at ? $m->created_at->format('Y-m-d') : '';
                    $showDate = $msgDate && $msgDate !== $lastDate;
                    $lastDate = $msgDate ?: $lastDate;
                    $timeDisplay = $m->created_at ? $m->created_at->format('H:i') : '--:--';
                    if ($showDate && $msgDate) {
                        $dateDisplay = $msgDate === $today ? '今天' : $m->created_at->format('m-d');
                    }
                    $samePlayer = $lastPlayerName === ($isMine ? $currentUserName : $chatUser->name);
                    $showName = !$samePlayer;
                    $lastPlayerName = $isMine ? $currentUserName : $chatUser->name;
                    $bubbleClass = $isMine ? $bubbleMine : $bubbleOther;
                    $displayName = $isMine ? $currentUserName : $chatUser->name;
                    $avatarUrl = $isMine ? \App\Services\PlayerAvatarService::url($currentUserName, auth()->user()->mc_uuid) : \App\Services\PlayerAvatarService::url($chatUser->name, $chatUser->mc_uuid);
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
                        <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-1 ring-slate-200 bg-white flex-shrink-0 mr-2 object-cover">
                        @endif
                        <div class="max-w-[75%] sm:max-w-[65%]">
                            <div class="text-xs text-slate-400 mb-0.5 {{ $isMine ? 'text-right' : 'text-left' }}">{{ $displayName }} · {{ $timeDisplay }}</div>
                            {!! $bubbleHtml !!}
                        </div>
                        @if($isMine)
                        <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-1 ring-slate-200 bg-white flex-shrink-0 ml-2 order-1 object-cover">
                        @endif
                    @else
                        <div class="max-w-[75%] sm:max-w-[65%] {{ $isMine ? 'mr-9 sm:mr-10' : 'ml-9 sm:ml-10' }}">
                            {!! $bubbleHtml !!}
                        </div>
                    @endif
                </div>
                @endforeach
            @endif
        </div>

        {{-- 底部信息栏 --}}
        <div class="border-t border-slate-200 px-3 py-2 flex items-center justify-between bg-white">
            <div class="text-xs text-slate-500">
                共 <span id="msgCount" class="text-slate-700 font-medium">{{ $messages->count() }}</span> 条
                @if($chatUser)
                    · 与 <span class="text-primary-600 font-medium">{{ $chatUser->name }}</span> 的私聊
                @endif
            </div>
            <button type="button" id="scrollBottomBtn" class="no-disable text-xs text-primary-600 hover:text-primary-700 px-2 py-1 rounded hover:bg-primary-50 transition">
                ↓ 滚动到底部
            </button>
        </div>
    </div>

    @if($chatUser)
        <div class="card p-3 sm:p-4">
            <form id="sendForm" class="flex flex-col sm:flex-row gap-2" data-no-autodisable>
                <textarea
                    id="sendInput"
                    name="message"
                    maxlength="500"
                    rows="1"
                    autocomplete="off"
                    placeholder="发送消息给 {{ $chatUser->name }}..."
                    class="input flex-1 px-3 py-2 text-sm resize-none"
                ></textarea>
                <button type="submit" id="sendBtn" class="btn-primary text-sm whitespace-nowrap">发送</button>
            </form>
            <p id="sendHint" class="text-xs text-slate-500 mt-2">私密消息，仅你和 {{ $chatUser->name }} 可见</p>
        </div>
    @endif
</div>

<script>
(function() {
    const chatBody = document.getElementById('chatBody');
    const msgCountEl = document.getElementById('msgCount');
    const statusEl = document.getElementById('chatStatus');
    const scrollBtn = document.getElementById('scrollBottomBtn');
    const sendForm = document.getElementById('sendForm');
    const sendInput = document.getElementById('sendInput');
    const sendBtn = document.getElementById('sendBtn');
    const sendHint = document.getElementById('sendHint');
    const contactBtn = document.getElementById('contactSelectorBtn');
    const contactDropdown = document.getElementById('contactDropdown');
    const contactSearch = document.getElementById('contactSearchInput');
    const contactListEl = document.getElementById('contactList');

    let chatUserId = {{ $chatUser ? $chatUser->id : 0 }};
    let chatUserName = '{{ $chatUser ? $chatUser->name : '' }}';
    let currentUserId = {{ $currentUserId }};
    let currentUserName = '{{ $currentUserName }}';
    let lastId = {{ $messages->last()?->id ?? 0 }};
    let totalCount = {{ $messages->count() }};
    let autoScroll = true;
    let refreshTimer = null;

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const BUBBLE_MINE = 'bg-blue-500 text-white rounded-br-md';
    const BUBBLE_OTHER = 'bg-white shadow-sm text-slate-700 rounded-bl-md';
    const AVATAR_CLASS = 'w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-1 ring-slate-200 bg-white flex-shrink-0 object-cover';

    var lastMsgDate = '';
    var lastPlayerName = '';
    var todayStr = (function(){ var d=new Date(); return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); })();

    function makeBubble(msg, isMine) {
        return '<div class="px-3 py-2 rounded-2xl text-sm leading-relaxed break-words ' + (isMine ? BUBBLE_MINE : BUBBLE_OTHER) + '">' + escapeHtml(msg) + '</div>';
    }

    function formatDateLabel(dateStr) {
        if (!dateStr || dateStr.length < 10) return dateStr || '';
        if (dateStr === todayStr) return '今天';
        return dateStr.substring(5, 10);
    }

    function addDateLabel(dateStr) {
        var label = document.createElement('div');
        label.className = 'flex justify-center my-2';
        label.innerHTML = '<span class="text-xs text-slate-400 bg-slate-100/80 px-3 py-0.5 rounded-full">' + escapeHtml(dateStr) + '</span>';
        chatBody.appendChild(label);
    }

    function parseTimestamp(ts) {
        if (!ts || typeof ts !== 'string') return { date: '', time: '--:--' };
        if (ts.length >= 16) return { date: ts.substring(0, 10), time: ts.substring(11, 16) };
        if (ts.length >= 5) return { date: '', time: ts.substring(0, 5) };
        return { date: '', time: '--:--' };
    }

    function appendMessage(m) {
        var emptyTip = chatBody.querySelector('.h-full.flex.items-center.justify-center');
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
        const isMine = m.sender_id === currentUserId;
        const displayName = isMine ? currentUserName : chatUserName;
        const samePlayer = displayName === lastPlayerName;
        const showName = !samePlayer;
        lastPlayerName = displayName;
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
                    '<div class="text-xs text-slate-400 mb-0.5 ' + (isMine ? 'text-right' : 'text-left') + '">' + escapeHtml(displayName) + ' · ' + escapeHtml(timeStr) + '</div>' +
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

    chatBody.addEventListener('scroll', function() {
        autoScroll = chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop < 60;
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

    async function fetchMessages() {
        if (!chatUserId) return;
        try {
            setStatus('<span class="inline-block w-2 h-2 bg-amber-500 rounded-full mr-1 animate-pulse"></span> 刷新中...', 'yellow');
            const res = await fetch('{{ route("private-chat.fetch") }}?with_id=' + chatUserId + '&after_id=' + lastId, { credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.ok) {
                if (data.chat_user_name) chatUserName = data.chat_user_name;
                (data.messages || []).forEach(appendMessage);
                if (data.last_id > lastId) lastId = data.last_id;
                setStatus('<span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span> 实时刷新中', 'green');
            } else {
                setStatus('<span class="inline-block w-2 h-2 bg-amber-500 rounded-full mr-1"></span> 数据异常', 'yellow');
            }
        } catch (e) {
            setStatus('<span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span> 刷新失败', 'red');
        }
    }

    // === 用户选择（事件委托，无需逐个绑定） ===
    if (contactBtn) {
        contactBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            contactDropdown.classList.toggle('hidden');
            if (!contactDropdown.classList.contains('hidden') && contactSearch) {
                contactSearch.focus();
            }
        });

        document.addEventListener('click', function(e) {
            // 点击下拉外部时关闭
            if (!contactDropdown.contains(e.target) && e.target !== contactBtn) {
                contactDropdown.classList.add('hidden');
            }
        });
    }

    // 委托：所有 .contact-item 点击统一处理（包括搜索后动态生成）
    contactDropdown.addEventListener('click', function(e) {
        var item = e.target.closest('.contact-item');
        if (!item) return;
        e.preventDefault();
        var id = item.dataset.id;
        contactDropdown.classList.add('hidden');
        window.location.href = '{{ route("private-chat") }}?with=' + id;
    });

    // 搜索用户
    if (contactSearch) {
        var searchTimer = null;
        var defaultListHtml = contactListEl ? contactListEl.innerHTML : '';

        contactSearch.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(searchTimer);
            if (q.length < 1) {
                contactListEl.innerHTML = defaultListHtml;
                return;
            }
            searchTimer = setTimeout(function() {
                fetch('{{ route("private-chat.search-users") }}?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        if (!d.ok) return;
                        contactListEl.innerHTML = (d.users || []).map(function(u) {
                            var name = escapeHtml(u.name);
                            var avatar = escapeHtml(u.avatar_url || '');
                            return '<button type="button" class="contact-item w-full flex items-center gap-2.5 px-3 py-2 text-sm hover:bg-slate-50 transition text-left" data-id="' + u.id + '" data-name="' + name + '">' +
                                '<img src="' + avatar + '" alt="' + name + '" class="w-7 h-7 rounded-full" onerror="this.style.display=\'none\'">' +
                                '<span class="text-slate-700">' + name + '</span></button>';
                        }).join('') || '<div class="px-3 py-2 text-sm text-slate-400">未找到用户</div>';
                    });
            }, 200);
        });
    }

    // === 发消息 ===
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
            if (sending || !chatUserId) return;
            const msg = sendInput.value.trim();
            if (!msg) return;

            sending = true;
            sendBtn.disabled = true;
            sendBtn.textContent = '发送中...';
            setHint('发送中...', 'yellow');

            try {
                const formData = new FormData();
                formData.append('message', msg);
                formData.append('receiver_id', chatUserId);
                const res = await fetch('{{ route("private-chat.send") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData,
                });
                const data = await res.json();

                if (data && data.ok) {
                    if (data.record) appendMessage(data.record);
                    sendInput.value = '';
                    setHint('✓ 已发送', 'green');
                    autoScroll = true;
                    scrollToBottom(false);
                    setTimeout(function() { scrollToBottom(false); }, 200);
                    setTimeout(function() { scrollToBottom(false); }, 500);
                    lastId = data.record ? data.record.id : lastId;
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
                sendBtn.textContent = '发送';
                try { sendInput.focus(); } catch(e) {}
            }
        });
    }

    // 初始滚动
    [50, 100, 200, 500, 1000, 1500].forEach(function(d) { setTimeout(function() { scrollToBottom(false); }, d); });
    function onLoadScroll() { scrollToBottom(false); [300, 800].forEach(function(d) { setTimeout(function() { scrollToBottom(false); }, d); }); }
    if (document.readyState === 'complete') { onLoadScroll(); } else { window.addEventListener('load', onLoadScroll); }

    // 定时刷新
    if (chatUserId) {
        refreshTimer = setInterval(fetchMessages, 2000);
    }
})();
</script>
@endsection