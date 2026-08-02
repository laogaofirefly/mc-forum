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
    .tool-btn {
        font-size: 12px;
        padding: 6px 12px;
        background: #fff;
        color: #334155;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
    }
    .tool-btn:hover { background: #f8fafc; }
    .tool-btn:disabled { background: #e2e8f0; cursor: not-allowed; }
    .link-btn {
        font-size: 12px;
        color: #059669;
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .link-btn:hover { background: #ecfdf5; }
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
                <button type="button" id="syncLogBtn" class="tool-btn">📜 同步</button>
                <button type="button" id="demoMsgBtn" class="tool-btn">🧪 测试</button>
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
            <form id="sendForm" autocomplete="off" data-no-autodisable onsubmit="return false;">
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
                <button type="button" id="toggleLogPreview" class="link-btn">▶ 查看日志解析情况</button>
            </div>
            <div id="logPreviewWrap" style="display:none;margin-top:12px;border-top:1px solid #e2e8f0;padding-top:12px;">
                <div style="display:flex;gap:8px;margin-bottom:8px;">
                    <button type="button" id="loadLogPreview" class="tool-btn">读取最近 30 行日志</button>
                    <span id="logPreviewMeta" style="font-size:12px;color:#64748b;"></span>
                </div>
                <div id="logPreviewBody" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-family:monospace;font-size:12px;padding:8px;max-height:280px;overflow-y:auto;">
                    <div style="color:#94a3b8;">点击上方按钮加载日志...</div>
                </div>
            </div>
        </div>
    @endif
@endauth

{{-- 传递配置到 JS（避免在 JS 文件里写 Blade 语法） --}}
<script>
    window.__CSRF_TOKEN__ = '{{ csrf_token() }}';
    window.__CHAT_USER__ = {{ auth()->check() ? json_encode(auth()->user()->name) : 'null' }};
    window.__CHAT_LAST_ID__ = {{ $messages->last()?->id ?? 0 }};
    window.__CHAT_DEFAULT_HINT__ = {{ auth()->check() ? json_encode('以 ' . auth()->user()->name . ' 的名义发送给游戏内所有在线玩家') : 'null' }};
    window.__CHAT_ROUTES__ = {
        fetch: '{{ route("game-chat.fetch") }}',
        send: '{{ route("game-chat.send") }}',
        demo: '{{ route("game-chat.demo") }}',
        sync: '{{ route("chat-sync") }}',
        logPreview: '{{ route("chat-log-preview") }}'
    };
</script>
{{-- 外部 JS 文件，带版本号强制浏览器刷新缓存 --}}
<script src="/js/game-chat.js?v={{ filemtime(public_path('js/game-chat.js')) }}"></script>
@endsection
