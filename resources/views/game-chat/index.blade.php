@extends('layouts.app')

@section('title', '游戏内聊天')

@section('content')

<div style="margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;background:linear-gradient(135deg,#34d399,#059669);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">💬</div>
        <div>
            <div style="font-size:18px;font-weight:700;color:#0f172a;">MC 群聊</div>
            <div style="font-size:12px;color:#64748b;">实时同步游戏内聊天</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <span id="statusText" style="font-size:12px;padding:2px 10px;border-radius:999px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;">
            ● 在线
        </span>
        @auth
            @if(auth()->user()->isAdmin())
                <button type="button" onclick="syncLogClick()" style="font-size:12px;padding:6px 12px;background:#fff;color:#334155;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">📜 同步</button>
                <button type="button" onclick="demoMsgClick()" style="font-size:12px;padding:6px 12px;background:#fff;color:#334155;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">🧪 测试</button>
            @endif
        @endauth
    </div>
</div>

{{-- 聊天主容器：卡片 --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">

    {{-- 消息区域：固定高度，明确是滚动容器 --}}
    <div id="chatBody" style="height:calc(100vh - 280px);min-height:300px;max-height:calc(100vh - 280px);overflow-y:auto;overflow-x:hidden;padding:16px;background:#f8fafc;-webkit-overflow-scrolling:touch;">

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
            <div id="msg-{{ $m->id }}" style="display:flex;flex-direction:column;margin-bottom:12px;{{ $isSelf ? 'align-items:flex-end;' : '' }}">
                <div style="font-size:11px;color:#94a3b8;margin:0 4px 4px;font-weight:500;{{ $isSelf ? 'text-align:right;' : '' }}">{{ $m->player_name }}</div>
                <div style="max-width:80%;padding:9px 13px;border-radius:14px;word-break:break-word;line-height:1.55;font-size:14px;
                    {{ $isSelf
                        ? 'background:#10b981;color:#fff;border-top-right-radius:4px;'
                        : 'background:#fff;color:#1e293b;border:1px solid #e8edf3;border-top-left-radius:4px;'
                    }}">{{ $m->message }}</div>
            </div>
        @endforeach
    </div>

    {{-- 发送区 --}}
    @auth
        <div style="border-top:1px solid #e2e8f0;padding:12px 16px;background:#fff;">
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <textarea
                    id="sendInput"
                    maxlength="200"
                    rows="1"
                    placeholder="发送消息到游戏内..."
                    onkeydown="if(event.key==='Enter'&&(event.ctrlKey||event.metaKey)){event.preventDefault();sendClick();return false;}"
                    oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px';"
                    style="flex:1;min-height:44px;max-height:100px;padding:10px 14px;font-size:14px;border:1px solid #e2e8f0;border-radius:10px;resize:none;outline:none;font-family:inherit;line-height:1.5;"
                ></textarea>
                <button type="button" onclick="sendClick()" id="sendBtn" style="flex-shrink:0;background:#10b981;color:#fff;border:none;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;min-height:44px;">发送</button>
            </div>
            <p id="sendHint" style="font-size:12px;color:#64748b;margin:6px 2px 0;">以 {{ auth()->user()->name }} 的名义发送给游戏内所有在线玩家</p>
        </div>
    @else
        <div style="border-top:1px solid #e2e8f0;padding:16px;text-align:center;font-size:14px;color:#64748b;background:#fff;">
            <a href="{{ route('login') }}" style="color:#059669;font-weight:600;">登录</a> 后可向游戏内发送消息
        </div>
    @endauth
</div>

@auth
    @if(auth()->user()->isAdmin())
        <div style="margin-top:12px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px;font-size:13px;color:#475569;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <p style="font-weight:600;color:#0f172a;margin:0;">🛠️ 管理员工具</p>
                <button type="button" onclick="toggleLogPanel()" style="font-size:12px;color:#059669;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:4px;" id="toggleLogBtn">▶ 查看日志解析情况</button>
            </div>
            <div id="logWrap" style="display:none;margin-top:12px;border-top:1px solid #e2e8f0;padding-top:12px;">
                <div style="display:flex;gap:8px;margin-bottom:8px;">
                    <button type="button" onclick="loadLogClick()" id="loadLogBtn" style="font-size:12px;padding:6px 12px;background:#fff;color:#334155;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">读取最近 30 行日志</button>
                    <span id="logMeta" style="font-size:12px;color:#64748b;"></span>
                </div>
                <div id="logBody" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-family:monospace;font-size:12px;padding:8px;max-height:280px;overflow-y:auto;">
                    <div style="color:#94a3b8;">点击上方按钮加载日志...</div>
                </div>
            </div>
        </div>
    @endif
@endauth

<script>
/* ============================================================
   游戏聊天 JS - 最简可靠实现
   - 所有按钮直接 onclick 属性绑定
   - 不用 form submit，完全绕开全局表单监听器
   - 不用外部文件，直接内联
   - 所有请求路径硬编码，避免 route() 缓存问题
   ============================================================ */

// 全局配置（硬编码路径，避免路由缓存影响）
var _FETCH_URL = '/game-chat/fetch';
var _SEND_URL  = '/game-chat/send';
var _DEMO_URL  = '/game-chat/demo';
var _SYNC_URL  = '/chat-sync';
var _LOG_URL   = '/chat-log-preview';
var _CSRF      = '{{ csrf_token() }}';
var _ME        = {{ auth()->check() ? json_encode(auth()->user()->name) : 'null' }};
var _LAST_ID   = {{ $messages->last()?->id ?? 0 }};

// 防重复渲染的消息 ID 集合
var _MSG_IDS = {};
@foreach($messages as $m)
    _MSG_IDS[{{ $m->id }}] = true;
@endforeach

// 安全 HTML 转义
function _esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
}

// 滚动到底部
function _scrollBottom() {
    var el = document.getElementById('chatBody');
    if (!el) return;
    try {
        el.scrollTop = el.scrollHeight;
    } catch(e) {}
}

// 判断是否在底部附近
function _isAtBottom() {
    var el = document.getElementById('chatBody');
    if (!el) return true;
    return (el.scrollHeight - el.clientHeight - el.scrollTop) < 80;
}

// 设置状态栏
function _setStatus(text, type) {
    var el = document.getElementById('statusText');
    if (!el) return;
    var cfg = {
        green:  ['#ecfdf5', '#047857', '#a7f3d0'],
        yellow: ['#fffbeb', '#b45309', '#fde68a'],
        red:    ['#fef2f2', '#b91c1c', '#fecaca']
    };
    var c = cfg[type] || cfg.green;
    el.style.background = c[0];
    el.style.color = c[1];
    el.style.borderColor = c[2];
    el.textContent = text;
}

// 添加一条消息到页面
function _addMsg(m) {
    if (!m) return;
    if (m.id && _MSG_IDS[m.id]) return; // 去重
    if (m.id) _MSG_IDS[m.id] = true;

    // 隐藏空提示
    var tip = document.getElementById('emptyTip');
    if (tip) tip.style.display = 'none';

    var body = document.getElementById('chatBody');
    if (!body) return;

    var isSelf = _ME && m.player_name === _ME;
    var wrap = document.createElement('div');
    wrap.id = m.id ? ('msg-' + m.id) : '';
    wrap.style.display = 'flex';
    wrap.style.flexDirection = 'column';
    wrap.style.marginBottom = '12px';
    if (isSelf) wrap.style.alignItems = 'flex-end';

    var nameDiv = document.createElement('div');
    nameDiv.style.fontSize = '11px';
    nameDiv.style.color = '#94a3b8';
    nameDiv.style.margin = '0 4px 4px';
    nameDiv.style.fontWeight = '500';
    if (isSelf) nameDiv.style.textAlign = 'right';
    nameDiv.textContent = m.player_name;

    var bubble = document.createElement('div');
    bubble.style.maxWidth = '80%';
    bubble.style.padding = '9px 13px';
    bubble.style.borderRadius = '14px';
    bubble.style.wordBreak = 'break-word';
    bubble.style.lineHeight = '1.55';
    bubble.style.fontSize = '14px';
    if (isSelf) {
        bubble.style.background = '#10b981';
        bubble.style.color = '#fff';
        bubble.style.borderTopRightRadius = '4px';
    } else {
        bubble.style.background = '#fff';
        bubble.style.color = '#1e293b';
        bubble.style.border = '1px solid #e8edf3';
        bubble.style.borderTopLeftRadius = '4px';
    }
    bubble.textContent = m.message;

    wrap.appendChild(nameDiv);
    wrap.appendChild(bubble);
    body.appendChild(wrap);
}

// 容错 JSON 解析
function _jsonOrError(res) {
    return res.text().then(function(t) {
        try { return JSON.parse(t); }
        catch(e) { return { ok:false, message:'服务器返回异常：' + t.substring(0,100) }; }
    });
}

// ============ 拉取新消息 ============
function _fetch() {
    fetch(_FETCH_URL + '?after_id=' + _LAST_ID + '&_=' + Date.now(), { credentials: 'same-origin' })
        .then(_jsonOrError)
        .then(function(d) {
            if (!d || !d.ok) {
                _setStatus('● ' + (d.message || '数据异常'), 'yellow');
                return;
            }
            var wasBottom = _isAtBottom();
            if (d.messages && d.messages.length > 0) {
                d.messages.forEach(function(m) {
                    _addMsg(m);
                    if (m.id > _LAST_ID) _LAST_ID = m.id;
                });
            }
            _setStatus('● 在线', 'green');
            if (wasBottom) _scrollBottom();
        })
        .catch(function() { _setStatus('● 刷新失败', 'red'); });
}

// ============ 发送消息 ============
var _sending = false;
function sendClick() {
    if (_sending) return;
    var input = document.getElementById('sendInput');
    var btn = document.getElementById('sendBtn');
    var hint = document.getElementById('sendHint');
    if (!input || !btn) return;
    var msg = input.value.trim();
    if (!msg) return;

    _sending = true;
    btn.disabled = true;
    btn.textContent = '发送中';
    if (hint) { hint.textContent = '正在发送到游戏内玩家...'; hint.style.color = '#b45309'; }

    var fd = new FormData();
    fd.append('message', msg);

    fetch(_SEND_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Accept': 'application/json' },
        credentials: 'same-origin',
        body: fd
    })
    .then(_jsonOrError)
    .then(function(d) {
        if (d && d.ok) {
            // 显示自己发的消息
            if (d.record) _addMsg(d.record);
            input.value = '';
            input.style.height = 'auto';
            if (hint) { hint.textContent = '✓ 已发送到游戏'; hint.style.color = '#059669'; }
            // 强制滚到底
            _scrollBottom();
            setTimeout(_scrollBottom, 50);
            setTimeout(_scrollBottom, 200);
            // 3秒后恢复提示
            setTimeout(function() {
                if (hint) { hint.textContent = '以 ' + _ME + ' 的名义发送给游戏内所有在线玩家'; hint.style.color = '#64748b'; }
            }, 3000);
        } else {
            var err = (d && d.message) ? d.message : '发送失败';
            if (d && d.errors && d.errors.message) err = d.errors.message[0];
            if (hint) { hint.textContent = '✗ ' + err; hint.style.color = '#dc2626'; }
        }
    })
    .catch(function(e) {
        if (hint) { hint.textContent = '✗ 网络错误：' + (e.message || e); hint.style.color = '#dc2626'; }
    })
    .finally(function() {
        _sending = false;
        btn.disabled = false;
        btn.textContent = '发送';
        try { input.focus(); } catch(e) {}
    });
}

// ============ 测试消息按钮 ============
function demoMsgClick() {
    var btn = event.target;
    btn.disabled = true;
    btn.textContent = '发送中...';
    fetch(_DEMO_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: '{}'
    })
    .then(_jsonOrError)
    .then(function(d) {
        if (d && d.ok && d.message) {
            _addMsg(d.message);
            _scrollBottom();
        }
    })
    .catch(function(){})
    .finally(function() {
        btn.disabled = false;
        btn.textContent = '🧪 测试';
    });
}

// ============ 同步日志按钮 ============
function syncLogClick() {
    var btn = event.target;
    btn.disabled = true;
    btn.textContent = '同步中...';
    fetch(_SYNC_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: '{}'
    })
    .then(_jsonOrError)
    .then(function(d) {
        btn.disabled = false;
        btn.textContent = '📜 同步';
        if (d && d.ok) {
            _fetch(); // 同步后立即拉一次
            _setStatus('● ' + (d.inserted > 0 ? '新增 ' + d.inserted + ' 条' : '已同步'), 'green');
        } else {
            _setStatus('● ' + (d.message || '同步失败'), 'red');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = '📜 同步';
        _setStatus('● 同步异常', 'red');
    });
}

// ============ 日志面板 ============
function toggleLogPanel() {
    var wrap = document.getElementById('logWrap');
    var btn = document.getElementById('toggleLogBtn');
    if (!wrap) return;
    var show = wrap.style.display === 'none';
    wrap.style.display = show ? 'block' : 'none';
    btn.textContent = show ? '▼ 收起日志预览' : '▶ 查看日志解析情况';
    if (show) {
        var body = document.getElementById('logBody');
        if (body && body.children.length <= 1) loadLogClick();
    }
}

function loadLogClick() {
    var btn = document.getElementById('loadLogBtn');
    var body = document.getElementById('logBody');
    if (!body) return;
    if (btn) { btn.disabled = true; btn.textContent = '读取中...'; }
    body.innerHTML = '<div style="color:#b45309;">读取中...</div>';
    fetch(_LOG_URL + '?_=' + Date.now(), { credentials: 'same-origin' })
        .then(_jsonOrError)
        .then(function(d) {
            if (btn) { btn.disabled = false; btn.textContent = '读取最近 30 行日志'; }
            if (!d || !d.ok) {
                body.innerHTML = '<div style="color:#dc2626;">错误：' + _esc(d.error || '未知') + '</div>';
                return;
            }
            var meta = document.getElementById('logMeta');
            if (meta) meta.textContent = '路径：' + d.log_path;
            var rows = d.rows || [];
            if (!rows.length) {
                body.innerHTML = '<div style="color:#64748b;">日志为空</div>';
                return;
            }
            var cnt = 0;
            var html = '';
            for (var i = 0; i < rows.length; i++) {
                var r = rows[i];
                if (r.is_chat) cnt++;
                var dotC = r.is_chat ? '#10b981' : '#94a3b8';
                var txtC = r.is_chat ? '#047857' : '#64748b';
                var parsed = '';
                if (r.is_chat && r.parsed) {
                    parsed = '<span style="color:#059669;margin-left:8px;">→ [' + _esc(r.parsed.player) + '] ' + _esc(r.parsed.message) + '</span>';
                }
                html += '<div style="color:' + txtC + ';display:flex;align-items:flex-start;gap:8px;margin-bottom:4px;">' +
                        '<span style="display:inline-block;width:8px;height:8px;margin-top:6px;border-radius:50%;flex-shrink:0;background:' + dotC + ';"></span>' +
                        '<div style="flex:1;"><span style="word-break:break-all;">' + _esc(r.raw) + '</span>' + parsed + '</div></div>';
            }
            body.innerHTML = html;
            if (meta) meta.textContent = '路径：' + d.log_path + '　|　共 ' + rows.length + ' 行，' + cnt + ' 行聊天';
        })
        .catch(function(e) {
            if (btn) { btn.disabled = false; btn.textContent = '读取最近 30 行日志'; }
            body.innerHTML = '<div style="color:#dc2626;">读取失败：' + _esc(e.message || e) + '</div>';
        });
}

// ============ 初始化：滚动到底 + 启动定时器 ============
// 立即滚 + 延迟滚，确保 DOM/字体 等加载完后一定到最底
_scrollBottom();
setTimeout(_scrollBottom, 50);
setTimeout(_scrollBottom, 100);
setTimeout(_scrollBottom, 200);
setTimeout(_scrollBottom, 500);
setTimeout(_scrollBottom, 1000);
setTimeout(_scrollBottom, 1500);
if (document.readyState === 'complete') {
    _scrollBottom();
} else {
    window.addEventListener('load', function() {
        _scrollBottom();
        setTimeout(_scrollBottom, 200);
        setTimeout(_scrollBottom, 800);
    });
}

// 轮询：拉消息 3秒 / 同步日志 10秒
setInterval(_fetch, 3000);
setInterval(function() {
    fetch(_SYNC_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: '{}'
    }).catch(function(){});
}, 10000);
</script>
@endsection
