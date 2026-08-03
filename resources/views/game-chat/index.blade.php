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
            <button type="button" id="fullscreenBtn" class="no-disable text-xs sm:text-sm px-2.5 py-1 rounded border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 transition" title="全屏聊天">
                ⛶ 全屏
            </button>
            <span id="chatStatus" class="badge text-xs sm:text-sm px-2.5 py-1 border bg-primary-100 text-primary-700 border-primary-200">
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

    <div id="chatCard" class="card overflow-hidden flex flex-col">
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
$avatarCache = [];
function hashColor($name) { $h = abs(crc32($name)) % 360; return "hsl({$h}, 55%, 45%)"; }
function getPlayerAvatar($name, &$cache) {
    if (isset($cache[$name])) return $cache[$name];
    $user = \App\Models\User::where('name', $name)->first();
    $cache[$name] = $user ? $user->getAvatarUrl() : \App\Services\PlayerAvatarService::initialAvatar($name);
    return $cache[$name];
}
$lastPlayer = '';
$lastTime = null;
$lastDate = '';
$timeGap = 300;
$today = now()->format('Y-m-d');
@endphp
            @foreach($messages as $idx => $m)
                @php
                    $isMine = $currentUserName && $m->player_name === $currentUserName;
                    $samePlayer = ($m->player_name === $lastPlayer);
                    $msgTs = $m->timestamp ? $m->timestamp->timestamp : 0;
                    $showLabel = ($lastTime > 0 && ($msgTs - $lastTime) > $timeGap);
                    $lastPlayer = $m->player_name;
                    $lastTime = $msgTs;
                    // 时间标签文本
                    if ($m->timestamp) {
                        $msgDate = $m->timestamp->format('Y-m-d');
                        if ($msgDate === $today) {
                            $labelText = $m->timestamp->format('H:i');
                        } else {
                            $labelText = $m->timestamp->format('m-d H:i');
                        }
                    } else {
                        $labelText = '--:--';
                    }
                @endphp
                @if($idx === 0 || $showLabel)
                <div class="time-label flex justify-center my-2">
                    <span class="text-[11px] text-slate-400 bg-slate-100/80 px-3 py-0.5 rounded-full">{{ $labelText }}</span>
                </div>
                @endif
                <div class="chat-row flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-end gap-2 px-2 py-0.5" data-id="{{ $m->id }}" data-player="{{ $m->player_name }}">
                    @if(!$isMine)
                        @if(!$samePlayer)
                            <div class="flex-shrink-0 w-9 h-9 rounded-full overflow-hidden border border-white/20 shadow-sm">
                                <img src="{{ getPlayerAvatar($m->player_name, $avatarCache) }}" alt="{{ $m->player_name }}" class="w-full h-full object-cover" onerror="this.style.display='none';this.parentNode.querySelector('.avatar-fallback').style.display='flex';">
                                <div class="avatar-fallback w-full h-full bg-slate-400 items-center justify-center text-white text-xs font-bold" style="display:none">{{ mb_substr($m->player_name, 0, 1) }}</div>
                            </div>
                        @else
                            <div class="w-9 flex-shrink-0"></div>
                        @endif
                        <div class="max-w-[72%] sm:max-w-[62%]">
                            @if(!$samePlayer)
                                <div class="text-[11px] mb-0.5 font-medium" style="color: {{ hashColor($m->player_name) }}">{{ $m->player_name }}</div>
                            @endif
                            <div class="px-3 py-2 rounded-xl text-sm leading-relaxed break-words bg-white rounded-tl-sm shadow-sm text-slate-700 border border-slate-100">
                                {{ $m->message }}
                            </div>
                        </div>
                    @else
                        <div class="max-w-[72%] sm:max-w-[62%]">
                            <div class="px-3 py-2 rounded-xl text-sm leading-relaxed break-words bg-blue-500 text-white rounded-tr-sm shadow-sm">
                                {{ $m->message }}
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
 @php unset($avatarCache); @endphp
        </div>
        <div id="chatFooter" class="border-t border-slate-200 bg-white">
            <div class="flex items-center justify-between px-3 py-2">
                <div class="text-xs text-slate-500">
                    共 <span id="msgCount" class="text-slate-700 font-medium">{{ $messages->count() }}</span> 条 · <span class="text-primary-600">实时刷新</span>
                </div>
                <button type="button" id="scrollBottomBtn" class="no-disable text-xs text-primary-600 hover:text-primary-700 px-2 py-1 rounded hover:bg-primary-50 transition">
                    ↓ 滚动到底部
                </button>
            </div>
            @auth
            <div id="sendBox" class="border-t border-slate-200 px-3 py-2 hidden">
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
        </div>
    </div>

    @auth
    <div id="sendBoxOuter" class="card p-3 sm:p-4">
        <form id="sendFormOuter" class="flex flex-col sm:flex-row gap-2" data-no-autodisable>
            <textarea id="sendInputOuter" name="message" maxlength="200" rows="1" autocomplete="off"
                placeholder="向游戏内发送消息（以你的用户名牢高 [网站] 显示）..."
                class="input flex-1 px-3 py-2 text-sm resize-none"
            ></textarea>
            <button type="submit" id="sendBtnOuter" class="btn-primary text-sm whitespace-nowrap">发送到游戏</button>
        </form>
        <p id="sendHintOuter" class="text-xs text-slate-500 mt-2">提示：消息会以你的用户名义直接发送到游戏内所有在线玩家。</p>
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
    // 发送框双份 DOM（全屏内 + 外部），统一通过 getter 获取当前激活的
    const sendFormEl = document.getElementById('sendForm');
    const sendInputEl = document.getElementById('sendInput');
    const sendBtnEl = document.getElementById('sendBtn');
    const sendHintEl = document.getElementById('sendHint');
    const sendFormOuter = document.getElementById('sendFormOuter');
    const sendInputOuter = document.getElementById('sendInputOuter');
    const sendBtnOuter = document.getElementById('sendBtnOuter');
    const sendHintOuter = document.getElementById('sendHintOuter');
    const sendBox = document.getElementById('sendBox');
    const sendBoxOuter = document.getElementById('sendBoxOuter');
    let isFullscreen = false;

    function activeSendForm() { return isFullscreen ? sendFormEl : sendFormOuter; }
    function activeSendInput() { return isFullscreen ? sendInputEl : sendInputOuter; }
    function activeSendBtn() { return isFullscreen ? sendBtnEl : sendBtnOuter; }
    function activeSendHint() { return isFullscreen ? sendHintEl : sendHintOuter; }
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

    // ===== QQ 群聊风格 JS =====
    var lastPlayer = '';
    var lastMsgTime = 0;        // 秒级时间戳（跨天判断用）
    var lastMsgDate = '';       // 'Y-m-d' 字符串
    var todayStr = (function(){ var d=new Date(); return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); })();
    var timeGap = 300;
    function hashColor(name) {
        var hash = 0;
        for (var i = 0; i < name.length; i++) {
            hash = ((hash << 5) - hash) + name.charCodeAt(i);
            hash |= 0;
        }
        var h = Math.abs(hash) % 360;
        return 'hsl(' + h + ', 28%, 42%)';
    }
    // 解析 'Y-m-d H:i:s' 格式的时间字符串，返回 { sec: 秒时间戳, date: 'Y-m-d' } 或 null
    function parseTimestamp(timeStr) {
        if (!timeStr) return null;
        var m = timeStr.match(/^(\d{4}-\d{2}-\d{2})\s+(\d{2}):(\d{2})(?::(\d{2}))?/);
        if (!m) return null;
        var date = m[1];
        var h = parseInt(m[2]), min = parseInt(m[3]), s = m[4] ? parseInt(m[4]) : 0;
        var sec = h * 3600 + min * 60 + s;
        return { timestamp: new Date(date + 'T' + String(h).padStart(2,'0') + ':' + String(min).padStart(2,'0') + ':' + String(s).padStart(2,'0')).getTime() / 1000 | 0, date: date, sec: sec };
    }
    // 格式化时间标签文本：当天只显示 HH:mm；跨天显示 mm-dd HH:mm
    function formatTimeLabel(parsed) {
        if (!parsed) return '--:--';
        var h = String(Math.floor(parsed.sec / 3600)).padStart(2,'0');
        var m = String(Math.floor((parsed.sec % 3600) / 60)).padStart(2,'0');
        var timePart = h + ':' + m;
        if (parsed.date === todayStr) return timePart;
        var parts = parsed.date.split('-');
        return parts[1] + '-' + parts[2] + ' ' + timePart;
    }
    function addTimeLabel(text) {
        var label = document.createElement('div');
        label.className = 'time-label flex justify-center my-2';
        label.innerHTML = '<span class="text-[11px] text-slate-400 bg-slate-100/80 px-3 py-0.5 rounded-full">' + escapeHtml(text) + '</span>';
        return label;
    }
    // 页面加载后从 DOM 同步状态，并滚动到底部
    (function syncState() {
        var rows = chatBody.querySelectorAll('.chat-row');
        if (rows.length > 0) {
            var last = rows[rows.length - 1];
            lastPlayer = last.dataset.player || '';
        }
        var labels = chatBody.querySelectorAll('.time-label span');
        if (labels.length > 0) {
            var txt = labels[labels.length - 1].textContent || '';
            var parsed = parseTimestamp(txt);
            if (parsed) { lastMsgTime = parsed.timestamp; lastMsgDate = parsed.date; }
        }
        // 滚动到最新消息
        chatBody.scrollTop = chatBody.scrollHeight;
    })();
    function appendMessage(m) {
        if (emptyTip) emptyTip.remove();
        if (m.id && chatBody.querySelector('.chat-row[data-id="' + m.id + '"]')) return;
        var isMine = currentUserName && m.player_name === currentUserName;
        var parsed = parseTimestamp(typeof m.timestamp === 'string' ? m.timestamp : '');
        var curSec = parsed ? parsed.timestamp : 0;
        var samePlayer = (m.player_name === lastPlayer && m.player_name !== '');
        // 跨天或超过 timeGap 插入时间标签
        var needLabel = false;
        if (lastMsgTime > 0 && curSec > 0) {
            if ((lastMsgDate && parsed && lastMsgDate !== parsed.date) || (curSec - lastMsgTime) > timeGap) {
                needLabel = true;
            }
        }
        if (needLabel) {
            chatBody.appendChild(addTimeLabel(formatTimeLabel(parsed)));
            lastPlayer = '';
        }
        if (curSec > 0) { lastMsgTime = curSec; lastMsgDate = parsed ? parsed.date : ''; }
        lastPlayer = m.player_name;
        var row = document.createElement('div');
        row.className = 'chat-row flex items-end gap-2 px-2 py-0.5 ' + (isMine ? 'justify-end' : 'justify-start');
        row.dataset.id = m.id;
        row.dataset.player = m.player_name;
        if (isMine) {
            row.innerHTML =
                '<div class="max-w-[72%] sm:max-w-[62%]">' +
                '<div class="px-3 py-2 rounded-xl text-sm leading-relaxed break-words bg-blue-500 text-white rounded-tr-sm shadow-sm">' + escapeHtml(m.message) + '</div>' +
                '</div>';
        } else {
            var avatar = samePlayer ? '<div class="w-9 flex-shrink-0"></div>' :
                ('<div class="flex-shrink-0 w-9 h-9 rounded-full overflow-hidden border border-white/20 shadow-sm">' +
                 '<img src="' + escapeHtml(m.avatar_url || '') + '" alt="" class="w-full h-full object-cover" onerror="this.style.display=\\'none\\';this.parentNode.querySelector(\\'.avatar-fallback\\').style.display=\\'flex\\';">' +
                 '<div class="avatar-fallback w-full h-full bg-slate-400 items-center justify-center text-white text-xs font-bold" style="display:none">' + escapeHtml((m.player_name || '?').charAt(0)) + '</div>' +
                 '</div>');
            var nameHtml = samePlayer ? '' : '<div class="text-xs mb-0.5 font-medium" style="color:' + hashColor(m.player_name) + '">' + escapeHtml(m.player_name) + '</div>';
            row.innerHTML =
                avatar +
                '<div class="max-w-[72%] sm:max-w-[62%]">' +
                nameHtml +
                '<div class="px-3 py-2 rounded-xl text-sm leading-relaxed break-words bg-white rounded-tl-sm shadow-sm text-slate-700 border border-slate-100">' + escapeHtml(m.message) + '</div>' +
                '</div>';
        }
        chatBody.appendChild(row);
        totalCount++;
        msgCountEl.textContent = totalCount;
        if (autoScroll) scrollToBottom(false);
    }
    function escapeHtml(s) {
        var d = document.createElement('div');
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

    // === 发消息到游戏（统一绑定两份表单） ===
    let sending = false;
    function bindSendForm(formEl) {
        if (!formEl) return;
        formEl.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (sending) return;
            var input = activeSendInput();
            var btn = activeSendBtn();
            var hint = activeSendHint();
            var msg = input.value.trim();
            if (!msg) return;
            sending = true;
            btn.disabled = true;
            btn.textContent = '发送中...';
            hint.textContent = '正在发送到游戏内玩家...';
            hint.className = 'text-xs text-amber-600 mt-2';
            try {
                const formData = new FormData();
                formData.append('message', msg);
                const res = await fetch('{{ route('game-chat.send') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData,
                });
                const data = await res.json();
                if (data && data.ok) {
                    if (data.record) appendMessage(data.record);
                    input.value = '';
                    hint.textContent = '✓ 已发送到游戏';
                    hint.className = 'text-xs text-primary-600 mt-2';
                    autoScroll = true;
                    scrollToBottom(false);
                    setTimeout(function() { scrollToBottom(false); }, 50);
                    setTimeout(function() { scrollToBottom(false); }, 200);
                    setTimeout(function() { scrollToBottom(false); }, 500);
                    setTimeout(() => {
                        hint.textContent = '提示：消息会以你的用户名义直接发送到游戏内所有在线玩家。';
                        hint.className = 'text-xs text-slate-500 mt-2';
                    }, 3000);
                } else {
                    var errMsg = (data && data.message) ? data.message : '发送失败';
                    if (data && data.errors && data.errors.message) errMsg = data.errors.message[0];
                    hint.textContent = '✗ ' + errMsg;
                    hint.className = 'text-xs text-red-600 mt-2';
                }
            } catch(e) {
                hint.textContent = '✗ 网络错误：' + e.message;
                hint.className = 'text-xs text-red-600 mt-2';
            } finally {
                sending = false;
                btn.disabled = false;
                btn.textContent = '发送到游戏';
                try { input.focus(); } catch(e) {}
            }
        });
    }
    bindSendForm(sendFormEl);
    bindSendForm(sendFormOuter);

    // === 全屏切换 ===
    var fullscreenBtn = document.getElementById('fullscreenBtn');
    var chatCard = document.getElementById('chatCard');
    if (fullscreenBtn && chatCard) {
        fullscreenBtn.addEventListener('click', function() {
            isFullscreen = !isFullscreen;
            if (isFullscreen) {
                enterFullscreen();
            } else {
                exitFullscreen();
            }
        });
    }
    function enterFullscreen() {
        // 保存原始样式以便恢复
        chatCard._origClass = chatCard.className;
        chatCard._origStyle = chatCard.style.cssText;
        chatBody._origClass = chatBody.className;
        chatBody._origStyle = chatBody.style.cssText;
        // 全屏卡片
        chatCard.className = 'fixed inset-0 z-50 flex flex-col bg-white shadow-2xl';
        // chatBody 自动撑满
        chatBody.className = 'flex-1 overflow-y-auto p-3 sm:p-4 space-y-1.5 bg-slate-50/50';
        chatBody.style.cssText = 'overflow-y:auto;overflow-x:hidden;' + (chatBody.style.backgroundImage ? 'background-image:' + chatBody.style.backgroundImage + ';background-size:cover;background-position:center;background-blend-mode:overlay;' : '');
        // 显示全屏内发送框，隐藏外部
        if (sendBox) sendBox.classList.remove('hidden');
        if (sendBoxOuter) sendBoxOuter.style.display = 'none';
        // 同步输入框内容
        if (sendInputEl && sendInputOuter) sendInputEl.value = sendInputOuter.value;
        fullscreenBtn.innerHTML = '✕ 退出全屏';
        fullscreenBtn.title = '退出全屏';
        // 滚动到底部
        chatBody.scrollTop = chatBody.scrollHeight;
        // 防止 body 滚动
        document.body.style.overflow = 'hidden';
    }
    function exitFullscreen() {
        // 恢复
        chatCard.className = chatCard._origClass || 'card overflow-hidden flex flex-col';
        chatCard.style.cssText = '';
        chatBody.className = chatBody._origClass || 'h-[480px] sm:h-[560px] overflow-y-auto p-3 sm:p-4 space-y-1.5 bg-slate-50/50';
        chatBody.style.cssText = chatBody._origStyle || '';
        // 隐藏全屏内发送框，显示外部
        if (sendBox) sendBox.classList.add('hidden');
        if (sendBoxOuter) sendBoxOuter.style.display = '';
        // 同步输入框内容
        if (sendInputOuter && sendInputEl) sendInputOuter.value = sendInputEl.value;
        fullscreenBtn.innerHTML = '⛶ 全屏';
        fullscreenBtn.title = '全屏聊天';
        document.body.style.overflow = '';
        // 滚动到底部
        chatBody.scrollTop = chatBody.scrollHeight;
    }
    // ESC 退出全屏
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isFullscreen) {
            exitFullscreen();
            isFullscreen = false;
        }
    }););
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

    // 初始滚动到底部（多次延迟确保生效）
    scrollToBottom(false);
    setTimeout(function() { scrollToBottom(false); }, 50);
    setTimeout(function() { scrollToBottom(false); }, 100);
    setTimeout(function() { scrollToBottom(false); }, 200);
    setTimeout(function() { scrollToBottom(false); }, 500);
    setTimeout(function() { scrollToBottom(false); }, 1000);
    setTimeout(function() { scrollToBottom(false); }, 1500);
    if (document.readyState === 'complete') {
        scrollToBottom(false);
    } else {
        window.addEventListener('load', function() {
            scrollToBottom(false);
            setTimeout(function() { scrollToBottom(false); }, 300);
            setTimeout(function() { scrollToBottom(false); }, 800);
        });
    }
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
