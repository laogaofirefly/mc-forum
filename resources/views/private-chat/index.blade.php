@extends('layouts.app')

@section('title', '私聊')

@section('content')
<div class="space-y-3 sm:space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="page-title text-slate-900 flex items-center">
                @include('layouts.partials.icons', ['name' => 'mail', 'class' => 'w-6 h-6 mr-2 flex-shrink-0'])私聊
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">与其他用户一对一私密聊天</p>
        </div>
        <span id="chatStatus" class="badge text-xs sm:text-sm px-2.5 py-1 border bg-primary-50 text-primary-700 border-primary-200">
            <span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1 animate-pulse"></span>
            在线
        </span>
    </div>

    <div class="card overflow-hidden flex flex-col lg:flex-row" style="height:calc(100vh - 220px);height:calc(100dvh - 220px);min-height:400px;">
        {{-- ===== 左侧：联系人列表（手机端选人时隐藏） ===== --}}
        <div class="w-full lg:w-72 xl:w-80 flex-shrink-0 border-b lg:border-b-0 lg:border-r border-slate-200 bg-white flex flex-col {{ $chatUser ? 'hidden lg:flex' : 'flex' }}">
            {{-- 搜索栏 --}}
            <div class="p-3 border-b border-slate-100">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="contactSearchInput" placeholder="搜索用户..." class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:border-primary-400 focus:outline-none bg-slate-50 focus:bg-white transition">
                </div>
            </div>

            {{-- 联系人列表 --}}
            <div id="contactList" class="flex-1 overflow-y-auto">
                @if($contacts->isNotEmpty())
                    <div class="px-3 py-2 text-xs text-slate-400 font-medium tracking-wide">最近聊天</div>
                    @foreach($contacts as $contact)
                        <a href="{{ route('private-chat', ['with' => $contact->id]) }}"
                           class="contact-item flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 transition border-b border-slate-50 {{ $chatUser && $chatUser->id === $contact->id ? 'bg-primary-50/60 border-primary-100' : '' }}"
                           data-id="{{ $contact->id }}">
                            <div class="relative flex-shrink-0">
                                <img src="{{ $contact->getAvatarUrl() }}" alt="{{ $contact->name }}" class="w-10 h-10 rounded-full" onerror="this.style.display='none'">
                                @if(($contact->unread_count ?? 0) > 0)
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 leading-none shadow-sm">
                                        {{ $contact->unread_count > 99 ? '99+' : $contact->unread_count }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline gap-1">
                                    <span class="text-sm font-medium text-slate-800 truncate">{{ $contact->name }}</span>
                                    <span class="text-[11px] text-slate-400 flex-shrink-0">{{ $contact->last_message_time }}</span>
                                </div>
                                <p class="text-xs text-slate-400 truncate mt-0.5">{{ $contact->last_message ?: '暂无消息' }}</p>
                            </div>
                        </a>
                    @endforeach
                    @if($users->isNotEmpty())
                        <div class="px-3 py-2 text-xs text-slate-400 font-medium tracking-wide border-t border-slate-100">其他用户</div>
                    @endif
                @endif
                <div id="contactUserList">
                    @foreach($users as $u)
                        <a href="{{ route('private-chat', ['with' => $u->id]) }}"
                           class="contact-item flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 transition border-b border-slate-50 {{ $chatUser && $chatUser->id === $u->id ? 'bg-primary-50/60 border-primary-100' : '' }}"
                           data-id="{{ $u->id }}">
                            <img src="{{ $u->getAvatarUrl() }}" alt="{{ $u->name }}" class="w-10 h-10 rounded-full flex-shrink-0" onerror="this.style.display='none'">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-slate-800 truncate block">{{ $u->name }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== 右侧：聊天区域 ===== --}}
        <div class="flex-1 flex flex-col min-w-0 min-h-0">
@php
$currentUserId = auth()->id();
$currentUserName = auth()->user()->name;
@endphp
            @if($chatUser)
                {{-- 聊天头部 --}}
                <div class="px-4 py-2.5 border-b border-slate-200 bg-white flex items-center gap-3 flex-shrink-0">
                    {{-- 移动端返回按钮 --}}
                    <a href="{{ route('private-chat') }}" class="lg:hidden flex-shrink-0 text-slate-500 hover:text-slate-700 p-1 -ml-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <img src="{{ $chatUser->getAvatarUrl() }}" alt="{{ $chatUser->name }}" class="w-8 h-8 rounded-full flex-shrink-0">
                    <span class="font-medium text-slate-800 truncate">{{ $chatUser->name }}</span>
                    <a href="{{ route('profile.show', $chatUser) }}" class="ml-auto flex-shrink-0 text-xs text-primary-600 hover:text-primary-700 px-2 py-1 rounded hover:bg-primary-50 transition">查看主页</a>
                </div>

                {{-- 消息区域 --}}
                <div id="chatBody" class="flex-1 overflow-y-auto overflow-x-hidden p-3 sm:p-4 space-y-1.5 bg-slate-50/50">
                    @if($messages->isEmpty())
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
$today = now()->format('Y-m-d');
$lastDate = '';
$lastPlayerName = '';
$bubbleMine = 'bg-blue-500 text-white rounded-br-md';
$bubbleOther = 'bg-white shadow-sm text-slate-700 rounded-bl-md';
@endphp
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
                    $avatarUrl = $isMine ? auth()->user()->getAvatarUrl() : $chatUser->getAvatarUrl();
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
                </div>

                {{-- 底部信息栏 --}}
                <div class="border-t border-slate-200 px-3 py-2 flex items-center justify-between bg-white flex-shrink-0">
                    <div class="text-xs text-slate-500">
                        共 <span id="msgCount" class="text-slate-700 font-medium">{{ $messages->count() }}</span> 条
                        · 与 <span class="text-primary-600 font-medium">{{ $chatUser->name }}</span> 的私聊
                    </div>
                    <button type="button" id="scrollBottomBtn" class="no-disable text-xs text-primary-600 hover:text-primary-700 px-2 py-1 rounded hover:bg-primary-50 transition">
                        @include('layouts.partials.icons', ['name' => 'chevron-down', 'class' => 'w-3.5 h-3.5'])底部
                    </button>
                </div>

                {{-- 发送表单 --}}
                <div class="p-3 border-t border-slate-200 bg-white flex-shrink-0">
                    <form id="sendForm" class="flex gap-2" data-no-autodisable>
                        <textarea
                            id="sendInput"
                            name="message"
                            maxlength="500"
                            rows="1"
                            autocomplete="off"
                            placeholder="输入消息..."
                            class="input flex-1 px-3 py-2 text-sm resize-none"
                        ></textarea>
                        <button type="submit" id="sendBtn" class="btn-primary text-sm whitespace-nowrap px-4">发送</button>
                    </form>
                    <p id="sendHint" class="text-xs text-slate-400 mt-1.5">私密消息，仅你和 {{ $chatUser->name }} 可见</p>
                </div>
            @else
                {{-- 未选择聊天对象 --}}
                <div class="flex-1 flex items-center justify-center bg-slate-50/50">
                    <div class="text-center text-slate-400 px-4">
                        <svg class="w-16 h-16 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-sm font-medium">选择一位用户开始私聊</p>
                        <p class="mt-1 text-xs text-slate-400">在左侧搜索并点击用户即可开始</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function() {
    try {
    var chatBody = document.getElementById('chatBody');
    var msgCountEl = document.getElementById('msgCount');
    var statusEl = document.getElementById('chatStatus');
    var scrollBtn = document.getElementById('scrollBottomBtn');
    var sendForm = document.getElementById('sendForm');
    var sendInput = document.getElementById('sendInput');
    var sendBtn = document.getElementById('sendBtn');
    var sendHint = document.getElementById('sendHint');
    var contactSearch = document.getElementById('contactSearchInput');
    var contactListEl = document.getElementById('contactList');

    var chatUserId = {{ $chatUser ? $chatUser->id : 0 }};
    var chatUserName = {!! $chatUser ? json_encode($chatUser->name, JSON_HEX_APOS|JSON_HEX_QUOT) : '""' !!};
    var currentUserId = {{ $currentUserId }};
    var currentUserName = {!! json_encode(auth()->user()->name, JSON_HEX_APOS|JSON_HEX_QUOT) !!};
    var lastId = {{ $messages->last()?->id ?? 0 }};
    var totalCount = {{ $messages->count() }};
    var autoScroll = true;
    var refreshTimer = null;

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var BUBBLE_MINE = 'bg-blue-500 text-white rounded-br-md';
    var BUBBLE_OTHER = 'bg-white shadow-sm text-slate-700 rounded-bl-md';

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
        var isMine = m.sender_id === currentUserId;
        var displayName = isMine ? currentUserName : chatUserName;
        var samePlayer = displayName === lastPlayerName;
        var showName = !samePlayer;
        lastPlayerName = displayName;
        var bubble = makeBubble(m.message, isMine);
        var avatarUrl = m.avatar_url || '';

        var row = document.createElement('div');
        row.className = 'chat-row flex items-start chat-msg-enter ' + (isMine ? 'justify-end' : 'justify-start') + ' px-2 ' + (showName ? 'py-1' : 'py-0.5');
        row.dataset.id = m.id;

        if (showName) {
            var avatarHtml = avatarUrl ? '<img src="' + escapeHtml(avatarUrl) + '" alt="' + escapeHtml(m.player_name) + '" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-1 ring-slate-200 bg-white flex-shrink-0 object-cover">' : '';
            if (isMine) {
                row.innerHTML =
                    '<div class="max-w-[75%] sm:max-w-[65%]">' +
                        '<div class="text-xs text-slate-400 mb-0.5 text-right">' + escapeHtml(displayName) + ' · ' + escapeHtml(timeStr) + '</div>' +
                        bubble +
                    '</div>' +
                    '<div class="w-2 flex-shrink-0"></div>' +
                    (avatarHtml ? avatarHtml.replace('flex-shrink-0', 'flex-shrink-0 order-1') : '');
            } else {
                row.innerHTML =
                    (avatarHtml ? avatarHtml : '') +
                    '<div class="w-2 flex-shrink-0"></div>' +
                    '<div class="max-w-[75%] sm:max-w-[65%]">' +
                        '<div class="text-xs text-slate-400 mb-0.5 text-left">' + escapeHtml(displayName) + ' · ' + escapeHtml(timeStr) + '</div>' +
                        bubble +
                    '</div>';
            }
        } else {
            row.innerHTML = '<div class="max-w-[75%] sm:max-w-[65%]' + (isMine ? ' mr-9 sm:mr-10' : ' ml-9 sm:ml-10') + '">' + bubble + '</div>';
        }

        chatBody.appendChild(row);
        totalCount++;
        if (msgCountEl) msgCountEl.textContent = totalCount;
        if (autoScroll) scrollToBottom(false);
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    if (chatBody) {
        chatBody.addEventListener('scroll', function() {
            autoScroll = chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop < 60;
        });
    }

    function scrollToBottom(smooth) {
        if (chatBody) chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
    }

    if (scrollBtn) scrollBtn.addEventListener('click', function() { scrollToBottom(true); });

    function setStatus(text, color) {
        if (!statusEl) return;
        var colorMap = {
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
            // 静默刷新，不显示状态变化
            var res = await fetch('{{ route("private-chat.fetch") }}?with_id=' + chatUserId + '&after_id=' + lastId, { credentials: 'same-origin' });
            var data = await res.json();
            if (data && data.ok) {
                if (data.chat_user_name) chatUserName = data.chat_user_name;
                (data.messages || []).forEach(appendMessage);
                if (data.last_id > lastId) lastId = data.last_id;
            }
        } catch (e) {
            // 静默失败
        }
    }

    // === 搜索用户 ===
    if (contactSearch) {
        var searchTimer = null;
        var defaultListHtml = contactListEl ? contactListEl.innerHTML : '';

        contactSearch.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(searchTimer);
            if (q.length < 1) {
                if (contactListEl) contactListEl.innerHTML = defaultListHtml;
                return;
            }
            searchTimer = setTimeout(function() {
                fetch('{{ route("private-chat.search-users") }}?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        if (!d.ok || !contactListEl) return;
                        contactListEl.innerHTML = (d.users || []).map(function(u) {
                            var name = escapeHtml(u.name);
                            var avatar = escapeHtml(u.avatar_url || '');
                            return '<a href="{{ route("private-chat") }}?with=' + u.id + '" class="contact-item flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 transition border-b border-slate-50" data-id="' + u.id + '">' +
                                '<img src="' + avatar + '" alt="' + name + '" class="w-10 h-10 rounded-full flex-shrink-0" onerror="this.style.display=\'none\'">' +
                                '<div class="flex-1 min-w-0"><span class="text-sm font-medium text-slate-800 truncate block">' + name + '</span></div>' +
                                '</a>';
                        }).join('') || '<div class="px-3 py-2 text-sm text-slate-400 text-center">未找到用户</div>';
                    });
            }, 200);
        });
    }

    // === 发消息 ===
    if (sendForm) {
        var sending = false;
        function setHint(text, color) {
            if (!sendHint) return;
            sendHint.textContent = text;
            sendHint.className = 'text-xs mt-1.5 ' + ({
                green: 'text-primary-600', yellow: 'text-amber-600', red: 'text-red-600'
            })[color] || 'text-slate-400';
        }

        sendForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (sending || !chatUserId) return;
            var msg = sendInput.value.trim();
            if (!msg) return;

            sending = true;
            sendBtn.disabled = true;
            sendBtn.textContent = '发送中...';
            setHint('发送中...', 'yellow');

            try {
                var formData = new FormData();
                formData.append('message', msg);
                formData.append('receiver_id', chatUserId);
                var res = await fetch('{{ route("private-chat.send") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData,
                });
                var data = await res.json();

                if (data && data.ok) {
                    if (data.record) appendMessage(data.record);
                    sendInput.value = '';
                    setHint('已发送', 'green');
                    autoScroll = true;
                    scrollToBottom(false);
                    setTimeout(function() { scrollToBottom(false); }, 200);
                    lastId = data.record ? data.record.id : lastId;
                } else {
                    var errMsg = (data && data.message) ? data.message : '发送失败';
                    if (data && data.errors && data.errors.message) errMsg = data.errors.message[0];
                    setHint('发送失败：' + errMsg, 'red');
                }
            } catch(e) {
                setHint('网络错误：' + e.message, 'red');
            } finally {
                sending = false;
                sendBtn.disabled = false;
                sendBtn.textContent = '发送';
                try { sendInput.focus(); } catch(e) {}
            }
        });
    }

    // 初始滚动到底部
    if (chatUserId) {
        [50, 100, 200, 500, 1000, 1500].forEach(function(d) { setTimeout(function() { scrollToBottom(false); }, d); });
        function onLoadScroll() { scrollToBottom(false); [300, 800].forEach(function(d) { setTimeout(function() { scrollToBottom(false); }, d); }); }
        if (document.readyState === 'complete') { onLoadScroll(); } else { window.addEventListener('load', onLoadScroll); }
        refreshTimer = setInterval(fetchMessages, 2000);
    }
    } catch(e) { console.error(e); }
})();
</script>
@endsection