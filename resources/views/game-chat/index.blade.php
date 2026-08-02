@extends('layouts.app')

@section('title', '游戏内聊天')

@section('content')
<style>
    /* === 聊天容器：纯块级布局，固定高度，确保是滚动容器 === */
    #chatWrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 12px;
    }
    #chatBody {
        height: calc(100vh - 280px);
        min-height: 300px;
        max-height: calc(100vh - 280px);
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        padding: 16px;
        background-color: #f8fafc;
    }
    #chatFooter {
        border-top: 1px solid #e2e8f0;
        padding: 12px 16px;
        background: #fff;
    }
    /* 顶部标题栏 */
    #chatHeader {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 0;
    }
    .chat-bubble {
        max-width: 80%;
        padding: 9px 13px;
        border-radius: 14px;
        word-break: break-word;
        line-height: 1.55;
        font-size: 14px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .chat-bubble.others {
        background: #fff;
        color: #1e293b;
        border: 1px solid #e8edf3;
        border-top-left-radius: 4px;
    }
    .chat-bubble.self {
        background: #10b981;
        color: #fff;
        border-top-right-radius: 4px;
    }
    .chat-name {
        font-size: 11px;
        color: #94a3b8;
        margin: 0 4px 4px;
        font-weight: 500;
    }
    .chat-row-qq {
        display: flex;
        flex-direction: column;
        margin-bottom: 12px;
    }
    .chat-row-qq.self {
        align-items: flex-end;
    }
    .chat-row-qq.self .chat-name {
        text-align: right;
    }
    .chat-row-qq + .chat-row-qq {
        margin-top: 2px;
    }
    .send-row {
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }
    .send-row textarea {
        flex: 1 1 0%;
        min-height: 44px;
        max-height: 100px;
        padding: 10px 14px;
        font-size: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        resize: none;
        outline: none;
        font-family: inherit;
        line-height: 1.5;
    }
    .send-row textarea:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
    }
    .send-btn {
        flex-shrink: 0;
        background: #10b981;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        min-height: 44px;
    }
    .send-btn:hover { background: #059669; }
    .send-btn:disabled { background: #9ca3af; cursor: not-allowed; }
    .status-badge {
        font-size: 12px;
        padding: 2px 10px;
        border-radius: 999px;
        border: 1px solid;
    }
</style>

<div id="chatHeader">
    <div style="display: flex; align-items: center; gap: 10px;">
        <div style="width:36px;height:36px;background:linear-gradient(135deg,#34d399,#059669);border-radius:8px;display:flex;align-items:center;justify-content:center;">
            <svg style="width:20px;height:20px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>
        <div>
            <div style="font-size:18px;font-weight:700;color:#0f172a;">MC 群聊</div>
            <div style="font-size:12px;color:#64748b;">实时同步游戏内聊天</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <span id="chatStatus" class="status-badge" style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;">
            <span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;margin-right:4px;">●</span>
            在线
        </span>
        @auth
            @if(auth()->user()->isAdmin())
                <button type="button" id="syncLogBtn" style="font-size:12px;padding:6px 12px;background:#fff;color:#334155;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">📜 同步</button>
                <button type="button" id="demoMsgBtn" style="font-size:12px;padding:6px 12px;background:#fff;color:#334155;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">🧪 测试</button>
            @endif
        @endauth
    </div>
</div>

<div id="chatWrap">
    <div id="chatBody">
        @if($messages->isEmpty())
            <div id="emptyTip" style="height:100%;display:flex;align-items:center;justify-content:center;text-align:center;color:#94a3b8;font-size:14px;">
                <div>
                    <p style="font-weight:600;color:#64748b;margin-bottom:4px;">还没有聊天消息</p>
                    <p style="font-size:12px;">玩家在游戏内说话或你在下方发消息就会出现在这里</p>
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

    @auth
        <div id="chatFooter">
            <form id="sendForm" autocomplete="off" data-no-autodisable>
                <div class="send-row">
                    <textarea
                        id="sendInput"
                        name="message"
                        maxlength="200"
                        rows="1"
                        placeholder="发送消息到游戏内..."
                    ></textarea>
                    <button type="submit" id="sendBtn" class="send-btn no-disable">发送</button>
                </div>
                <p id="sendHint" style="font-size:12px;color:#64748b;margin:6px 2px 0;">以 {{ auth()->user()->name }} 的名义发送给游戏内所有在线玩家</p>
            </form>
        </div>
    @else
        <div id="chatFooter" style="text-align:center;font-size:14px;color:#64748b;padding:16px;">
            <a href="{{ route('login') }}" style="color:#059669;font-weight:600;">登录</a> 后可向游戏内发送消息
        </div>
    @endauth
</div>

@auth
    @if(auth()->user()->isAdmin())
        <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px;font-size:13px;color:#475569;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <p style="font-weight:600;color:#0f172a;margin:0;">🛠️ 管理员工具</p>
                <button type="button" id="toggleLogPreview" style="font-size:12px;color:#059669;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:4px;">▶ 查看日志解析情况</button>
            </div>
            <div id="logPreviewWrap" style="display:none;margin-top:12px;border-top:1px solid #e2e8f0;padding-top:12px;">
                <div style="display:flex;gap:8px;margin-bottom:8px;">
                    <button type="button" id="loadLogPreview" style="font-size:12px;padding:6px 12px;background:#fff;color:#334155;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">读取最近 30 行日志</button>
                    <span id="logPreviewMeta" style="font-size:12px;color:#64748b;"></span>
                </div>
                <div id="logPreviewBody" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-family:monospace;font-size:12px;padding:8px;max-height:280px;overflow-y:auto;">
                    <div style="color:#94a3b8;">点击上方按钮加载日志...</div>
                </div>
            </div>
        </div>
    @endif
@endauth

<script>
(function() {
    'use strict';

    // === DOM 引用 ===
    var chatBody = document.getElementById('chatBody');
    var emptyTip = document.getElementById('emptyTip');
    var statusEl = document.getElementById('chatStatus');
    var demoBtn = document.getElementById('demoMsgBtn');
    var syncBtn = document.getElementById('syncLogBtn');
    var sendForm = document.getElementById('sendForm');
    var sendInput = document.getElementById('sendInput');
    var sendBtn = document.getElementById('sendBtn');
    var sendHint = document.getElementById('sendHint');

    // === 状态变量 ===
    var currentUser = {{ auth()->check() ? json_encode(auth()->user()->name) : 'null' }};
    var lastId = {{ $messages->last()?->id ?? 0 }};
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var sending = false;
    var renderingMessages = false;

    // === 工具函数 ===
    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    // 滚动到底部 - 直接设置 scrollTop，最可靠
    function scrollToBottom() {
        if (!chatBody) return;
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function setStatus(text, bg, color, border) {
        if (!statusEl) return;
        statusEl.style.background = bg || '#ecfdf5';
        statusEl.style.color = color || '#047857';
        statusEl.style.borderColor = border || '#a7f3d0';
        statusEl.innerHTML = text;
    }

    // === 添加消息到界面 ===
    function appendMessage(m) {
        if (!chatBody || !m) return;
        if (emptyTip) emptyTip.style.display = 'none';
        // 去重：已存在相同 id 的消息不重复添加
        if (m.id && chatBody.querySelector('.chat-row-qq[data-id="' + m.id + '"]')) {
            return;
        }
        var isSelf = currentUser && m.player_name === currentUser;
        var row = document.createElement('div');
        row.className = 'chat-row-qq' + (isSelf ? ' self' : '');
        if (m.id) row.setAttribute('data-id', m.id);
        row.innerHTML =
            '<div class="chat-name">' + escapeHtml(m.player_name) + '</div>' +
            '<div class="chat-bubble ' + (isSelf ? 'self' : 'others') + '">' + escapeHtml(m.message) + '</div>';
        chatBody.appendChild(row);
    }

    // === 拉取新消息 ===
    function fetchMessages() {
        fetch('{{ route("game-chat.fetch") }}?after_id=' + lastId, { credentials: 'same-origin' })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.ok && data.messages && data.messages.length > 0) {
                    var wasAtBottom = isAtBottom();
                    data.messages.forEach(function(m) {
                        appendMessage(m);
                        if (m.id && m.id > lastId) lastId = m.id;
                    });
                    // 如果之前在底部，或者用户就是发送者（自己发的消息），滚动到底部
                    if (wasAtBottom) {
                        scrollToBottom();
                    }
                    setStatus('<span style="color:#10b981;">●</span> 在线', '#ecfdf5', '#047857', '#a7f3d0');
                } else if (data && data.ok) {
                    setStatus('<span style="color:#10b981;">●</span> 在线', '#ecfdf5', '#047857', '#a7f3d0');
                } else {
                    setStatus('<span style="color:#f59e0b;">●</span> 数据异常', '#fffbeb', '#b45309', '#fde68a');
                }
            })
            .catch(function(e) {
                setStatus('<span style="color:#ef4444;">●</span> 刷新失败', '#fef2f2', '#b91c1c', '#fecaca');
            });
    }

    // 判断是否滚动在底部附近
    function isAtBottom() {
        if (!chatBody) return true;
        var bottom = chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop;
        return bottom < 80;
    }

    // === 测试消息按钮 ===
    if (demoBtn) {
        demoBtn.addEventListener('click', function() {
            demoBtn.disabled = true;
            demoBtn.textContent = '发送中...';
            fetch('{{ route("game-chat.demo") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: '{}',
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.ok && data.message) {
                    appendMessage(data.message);
                    scrollToBottom();
                }
            })
            .catch(function(){})
            .finally(function() {
                demoBtn.disabled = false;
                demoBtn.textContent = '🧪 测试';
            });
        });
    }

    // === 同步日志按钮 ===
    if (syncBtn) {
        syncBtn.addEventListener('click', function() {
            syncBtn.disabled = true;
            syncBtn.textContent = '同步中...';
            fetch('{{ route("chat-sync") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: '{}',
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                syncBtn.disabled = false;
                syncBtn.textContent = '📜 同步';
                if (data && data.ok) {
                    fetchMessages();
                    if (data.inserted > 0) {
                        setStatus('<span style="color:#10b981;">●</span> 新增 ' + data.inserted + ' 条', '#ecfdf5', '#047857', '#a7f3d0');
                    } else {
                        setStatus('<span style="color:#10b981;">●</span> 已同步', '#ecfdf5', '#047857', '#a7f3d0');
                    }
                } else {
                    setStatus('<span style="color:#ef4444;">●</span> ' + (data.message || '同步失败'), '#fef2f2', '#b91c1c', '#fecaca');
                }
            })
            .catch(function(e) {
                syncBtn.disabled = false;
                syncBtn.textContent = '📜 同步';
                setStatus('<span style="color:#ef4444;">●</span> 同步异常', '#fef2f2', '#b91c1c', '#fecaca');
            });
        });
    }

    // === 输入框自动增高 ===
    if (sendInput) {
        sendInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
    }

    // === 发消息到游戏 ===
    if (sendForm) {
        sendForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (sending) return;
            var msg = sendInput.value.trim();
            if (!msg) return;

            sending = true;
            sendBtn.disabled = true;
            sendBtn.textContent = '发送中';
            if (sendHint) {
                sendHint.textContent = '正在发送到游戏内玩家...';
                sendHint.style.color = '#b45309';
            }

            var formData = new FormData();
            formData.append('message', msg);

            fetch('{{ route("game-chat.send") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: formData,
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.ok) {
                    // 添加自己发的消息到界面
                    if (data.record) {
                        appendMessage(data.record);
                    }
                    sendInput.value = '';
                    sendInput.style.height = 'auto';
                    if (sendHint) {
                        sendHint.textContent = '✓ 已发送到游戏';
                        sendHint.style.color = '#059669';
                    }
                    // 强制滚动到底部，显示自己发的最新消息
                    scrollToBottom();
                    setTimeout(scrollToBottom, 50);
                    setTimeout(scrollToBottom, 200);
                    // 3秒后恢复提示
                    setTimeout(function() {
                        if (sendHint) {
                            sendHint.textContent = '以 {{ auth()->user()->name }} 的名义发送给游戏内所有在线玩家';
                            sendHint.style.color = '#64748b';
                        }
                    }, 3000);
                } else {
                    var errMsg = (data && data.message) ? data.message : '发送失败';
                    if (data && data.errors && data.errors.message) {
                        errMsg = data.errors.message[0];
                    }
                    if (sendHint) {
                        sendHint.textContent = '✗ ' + errMsg;
                        sendHint.style.color = '#dc2626';
                    }
                }
            })
            .catch(function(e) {
                if (sendHint) {
                    sendHint.textContent = '✗ 网络错误：' + e.message;
                    sendHint.style.color = '#dc2626';
                }
            })
            .finally(function() {
                sending = false;
                sendBtn.disabled = false;
                sendBtn.textContent = '发送';
                if (sendInput) sendInput.focus();
            });
        });
    }

    // === 日志预览面板 ===
    var toggleBtn = document.getElementById('toggleLogPreview');
    var logWrap = document.getElementById('logPreviewWrap');
    var loadBtn = document.getElementById('loadLogPreview');
    var logBody = document.getElementById('logPreviewBody');
    var logMeta = document.getElementById('logPreviewMeta');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            var isHidden = logWrap.style.display === 'none';
            logWrap.style.display = isHidden ? 'block' : 'none';
            toggleBtn.textContent = isHidden ? '▼ 收起日志预览' : '▶ 查看日志解析情况';
            if (isHidden && logBody.children.length <= 1) {
                loadLogPreview();
            }
        });
    }
    if (loadBtn) {
        loadBtn.addEventListener('click', loadLogPreview);
    }

    function loadLogPreview() {
        logBody.innerHTML = '<div style="color:#b45309;">读取中...</div>';
        loadBtn.disabled = true;
        fetch('{{ route("chat-log-preview") }}', { credentials: 'same-origin' })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                loadBtn.disabled = false;
                if (!data.ok) {
                    logBody.innerHTML = '<div style="color:#dc2626;">错误：' + escapeHtml(data.error || '未知') + '</div>';
                    return;
                }
                logMeta.textContent = '路径：' + data.log_path;
                var rows = data.rows || [];
                if (rows.length === 0) {
                    logBody.innerHTML = '<div style="color:#64748b;">日志为空</div>';
                    return;
                }
                var chatCount = 0;
                logBody.innerHTML = '';
                rows.forEach(function(r) {
                    var div = document.createElement('div');
                    var isChat = r.is_chat;
                    if (isChat) chatCount++;
                    div.style.color = isChat ? '#047857' : '#64748b';
                    div.style.display = 'flex';
                    div.style.alignItems = 'flex-start';
                    div.style.gap = '8px';
                    div.style.marginBottom = '4px';
                    var dot = '<span style="display:inline-block;width:8px;height:8px;margin-top:6px;border-radius:50%;flex-shrink:0;background:' + (isChat ? '#10b981' : '#94a3b8') + ';"></span>';
                    var text = '<span style="word-break:break-all;">' + escapeHtml(r.raw) + '</span>';
                    var parsed = '';
                    if (isChat && r.parsed) {
                        parsed = '<span style="color:#059669;margin-left:8px;">→ [' + escapeHtml(r.parsed.player) + '] ' + escapeHtml(r.parsed.message) + '</span>';
                    }
                    div.innerHTML = dot + '<div style="flex:1;">' + text + parsed + '</div>';
                    logBody.appendChild(div);
                });
                logMeta.textContent = '路径：' + data.log_path + '　|　共 ' + rows.length + ' 行，' + chatCount + ' 行聊天';
            })
            .catch(function(e) {
                loadBtn.disabled = false;
                logBody.innerHTML = '<div style="color:#dc2626;">读取失败：' + escapeHtml(e.message) + '</div>';
            });
    }

    // === 初始化 ===
    // 进入页面立即滚动到底部，并多次延迟确保 DOM 渲染完成
    scrollToBottom();
    [50, 100, 200, 500, 1000].forEach(function(d) {
        setTimeout(scrollToBottom, d);
    });
    window.addEventListener('load', function() {
        scrollToBottom();
        setTimeout(scrollToBottom, 100);
    });

    // 定时拉取新消息（3 秒一次）
    setInterval(fetchMessages, 3000);
    // 定时同步 MC 日志（10 秒一次）
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
