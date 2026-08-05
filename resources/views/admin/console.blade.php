@extends('layouts.app')

@section('title', '服务器控制台')

@section('content')
<div class="space-y-3 sm:space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="page-title text-slate-900 flex items-center">
                @include('layouts.partials.icons', ['name' => 'terminal', 'class' => 'w-6 h-6 mr-2 flex-shrink-0'])服务器控制台
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1" id="pageSubtitle">实时日志 + RCON 命令 + 服务器配置</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="serverStatus" class="badge text-xs sm:text-sm px-2.5 py-1 border bg-slate-100 text-slate-600 border-slate-200">
                <span class="inline-block w-2 h-2 bg-slate-400 rounded-full mr-1"></span>
                检测中...
            </span>
            <span id="rconStatus" class="badge text-xs sm:text-sm px-2.5 py-1 border bg-slate-100 text-slate-600 border-slate-200">
                <span class="inline-block w-2 h-2 bg-slate-400 rounded-full mr-1"></span>
                未连接
            </span>
<button type="button" id="configToggleBtn" class="btn-secondary text-xs sm:text-sm px-3 py-1.5">
                @include('layouts.partials.icons', ['name' => 'cog', 'class' => 'w-4 h-4'])配置
            </button>
            <button type="button" id="startServerBtn" class="btn-primary text-xs sm:text-sm px-3 py-1.5 hidden" title="启动 MC 服务器">
                @include('layouts.partials.icons', ['name' => 'play', 'class' => 'w-4 h-4 mr-1'])启动服务器
            </button>
            <button type="button" id="clearConsoleBtn" class="btn-secondary text-xs sm:text-sm px-3 py-1.5">
                @include('layouts.partials.icons', ['name' => 'scroll', 'class' => 'w-4 h-4'])清屏
            </button>
            <button type="button" id="downloadWorldBtn" class="btn-secondary text-xs sm:text-sm px-3 py-1.5" title="下载世界存档">
                @include('layouts.partials.icons', ['name' => 'download', 'class' => 'w-4 h-4'])下载存档
            </button>
        </div>
    </div>

    {{-- 服务器配置面板 --}}
    <div id="configPanel" class="card p-0 hidden">
        {{-- 标题栏 --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
            <p class="font-medium text-slate-900 text-sm flex items-center gap-1.5">
                @include('layouts.partials.icons', ['name' => 'cog', 'class' => 'w-4 h-4'])服务器配置
            </p>
            <span id="configStatus" class="text-xs text-slate-400"></span>
        </div>

        {{-- Tab 导航 --}}
        <div class="flex border-b border-slate-100 bg-slate-50/50 px-4">
            <button type="button" class="config-tab active" data-tab="tabServer">
                @include('layouts.partials.icons', ['name' => 'server', 'class' => 'w-3.5 h-3.5'])服务器设置
            </button>
            <button type="button" class="config-tab" data-tab="tabGame">
                @include('layouts.partials.icons', ['name' => 'gamepad', 'class' => 'w-3.5 h-3.5'])游戏规则
            </button>
            <button type="button" class="config-tab" data-tab="tabJava">
                @include('layouts.partials.icons', ['name' => 'chip', 'class' => 'w-3.5 h-3.5'])Java / 启动
            </button>
        </div>

        {{-- Tab 1: 服务器设置 --}}
        <div id="tabServer" class="config-tab-content p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">MC 服务器路径</label>
                    <input type="text" id="cfgMcServerPath" placeholder="/home/mc/server" class="input w-full text-sm py-2">
                    <p class="text-[11px] text-slate-400 mt-0.5">日志、玩家数据、server.properties 等文件的根目录</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">MC 服务器主机</label>
                    <input type="text" id="cfgMcHost" placeholder="127.0.0.1" class="input w-full text-sm py-2">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">MC 服务器端口</label>
                    <input type="text" id="cfgMcPort" placeholder="25565" class="input w-full text-sm py-2">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">RCON 主机</label>
                    <input type="text" id="cfgRconHost" placeholder="127.0.0.1" class="input w-full text-sm py-2">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">RCON 端口</label>
                    <input type="text" id="cfgRconPort" placeholder="25575" class="input w-full text-sm py-2">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">RCON 密码</label>
                    <input type="password" id="cfgRconPassword" placeholder="输入 RCON 密码" class="input w-full text-sm py-2">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">查询端口 (Query)</label>
                    <input type="text" id="cfgQueryPort" placeholder="25565" class="input w-full text-sm py-2">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">备份路径</label>
                    <input type="text" id="cfgBackupPath" placeholder="/home/mc/backups" class="input w-full text-sm py-2">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">自动重启</label>
                    <select id="cfgAutoRestart" class="input w-full text-sm py-2">
                        <option value="false">关闭</option>
                        <option value="true">开启</option>
                    </select>
                    <p class="text-[11px] text-slate-400 mt-0.5">崩溃或停止后自动重启服务器</p>
                </div>
            </div>
        </div>

        {{-- Tab 2: 游戏规则 --}}
        <div id="tabGame" class="config-tab-content hidden p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-slate-500">server.properties — 改变游戏玩法</span>
                <div class="flex items-center gap-2">
                    <button type="button" id="loadPropsBtn" class="btn-secondary text-xs px-3 py-1.5">读取配置</button>
                    <button type="button" id="savePropsBtn" class="btn-primary text-xs px-3 py-1.5 hidden">保存规则</button>
                </div>
            </div>
            <div id="propsContent" class="text-xs text-slate-500">点击「读取配置」加载 server.properties</div>
            <div id="propsSaveMsg" class="text-xs mt-2 hidden"></div>
        </div>

        {{-- Tab 3: Java / 启动 --}}
        <div id="tabJava" class="config-tab-content hidden p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Java 路径</label>
                    <input type="text" id="cfgJavaPath" placeholder="java" class="input w-full text-sm py-2 font-mono">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">最小内存 (Xms)</label>
                    <input type="text" id="cfgJavaXms" placeholder="1G" class="input w-full text-sm py-2 font-mono">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">最大内存 (Xmx)</label>
                    <input type="text" id="cfgJavaXmx" placeholder="4G" class="input w-full text-sm py-2 font-mono">
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">启动命令</label>
                    <input type="text" id="cfgStartCommand" placeholder="cd /home/mc/server && java -Xms1G -Xmx4G -jar server.jar nogui" class="input w-full text-sm py-2 font-mono">
                    <p class="text-[11px] text-slate-400 mt-0.5">仅在服务器未运行时可使用「启动服务器」按钮执行</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">停止命令</label>
                    <input type="text" id="cfgStopCommand" placeholder="stop" class="input w-full text-sm py-2 font-mono">
                    <p class="text-[11px] text-slate-400 mt-0.5">通过 RCON 发送此命令停止服务器</p>
                </div>
            </div>
        </div>

        {{-- 底部操作栏 --}}
        <div class="flex items-center gap-2 px-4 py-3 border-t border-slate-100 bg-slate-50/50">
            <button type="button" id="saveConfigBtn" class="btn-primary text-xs px-4 py-2">
                @include('layouts.partials.icons', ['name' => 'check', 'class' => 'w-3.5 h-3.5 mr-1'])保存全部
            </button>
            <button type="button" id="testRconBtn" class="btn-secondary text-xs px-4 py-2">
                @include('layouts.partials.icons', ['name' => 'link', 'class' => 'w-3.5 h-3.5 mr-1'])测试 RCON
            </button>
        </div>
    </div>

    <div class="card overflow-hidden">
        {{-- 日志控制栏 --}}
        <div class="border-b border-slate-700 bg-slate-900 px-3 py-1.5 flex items-center gap-2 flex-wrap">
            <span class="text-[11px] text-slate-500 flex-1" id="logInfo">等待加载...</span>
            <button type="button" id="logPauseBtn" class="text-[11px] text-slate-400 hover:text-slate-200 px-1.5 py-0.5 rounded transition flex items-center gap-1">
                @include('layouts.partials.icons', ['name' => 'pause', 'class' => 'w-3 h-3'])暂停
            </button>
            <button type="button" id="logAutoScrollBtn" class="text-[11px] text-slate-400 hover:text-slate-200 px-1.5 py-0.5 rounded transition flex items-center gap-1">
                @include('layouts.partials.icons', ['name' => 'chevron-down', 'class' => 'w-3 h-3'])自动滚动
            </button>
            <button type="button" id="logJumpBottomBtn" class="text-[11px] text-slate-400 hover:text-slate-200 px-1.5 py-0.5 rounded transition hidden items-center gap-1">
                @include('layouts.partials.icons', ['name' => 'chevron-down', 'class' => 'w-3 h-3'])到底部
            </button>
        </div>

        {{-- 终端输出区 --}}
        <div id="consoleOutput" class="bg-slate-950 text-green-400 font-mono text-xs sm:text-sm p-3 sm:p-4 overflow-y-auto overflow-x-hidden" style="height:calc(100vh - 440px);min-height:300px;">
            <div class="text-slate-500">Minecraft 服务器控制台 — 实时日志 + 命令执行</div>
            <div class="text-slate-600">输入命令按 Enter 执行，日志自动流式显示</div>
            <div class="text-slate-700">---</div>
        </div>

        {{-- 命令输入区 --}}
        <div class="border-t border-slate-700 bg-slate-900 px-3 py-2.5 flex items-center gap-2">
            <span class="text-cyan-400 font-mono font-bold text-sm flex-shrink-0 select-none">$</span>
            <input
                type="text"
                id="consoleInput"
                autocomplete="off"
                placeholder="输入命令，如 list、say hello..."
                class="flex-1 bg-transparent border-none outline-none text-cyan-300 font-mono text-sm placeholder-slate-600"
            >
            <button type="button" id="sendCommandBtn" class="btn-primary text-xs sm:text-sm px-3 py-1.5 flex-shrink-0">
                执行
            </button>
        </div>
    </div>

    {{-- 快捷命令 --}}
    <div class="card p-3 sm:p-4">
        <p class="text-xs text-slate-500 mb-2 font-medium">快捷命令</p>
        <div class="flex flex-wrap gap-1.5">
            @php
            $quickCommands = [
                'list' => '在线玩家',
                'say 欢迎来到服务器！' => '广播消息',
                'whitelist list' => '白名单',
                'time query daytime' => '游戏时间',
                'weather query' => '天气查询',
                'difficulty' => '当前难度',
                'seed' => '世界种子',
                'tps' => '服务器TPS',
                'save-all' => '保存世界',
                'stop' => '停止服务器',
            ];
            @endphp
            @foreach($quickCommands as $cmd => $label)
                <button type="button"
                    class="quick-cmd-btn text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700 transition text-slate-600 {{ $cmd === 'stop' ? 'border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 hover:border-red-300' : '' }}"
                    data-cmd="{{ $cmd }}"
                    title="{{ $label }}">
                    /{{ $cmd }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- 图例 --}}
    <div class="card p-3 sm:p-4">
        <div class="flex flex-wrap gap-3 text-xs text-slate-500">
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-amber-500/40"></span> 命令输入</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-cyan-500/40"></span> 命令输出</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-green-500/30"></span> 聊天消息</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-amber-500/30"></span> 玩家进出</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-red-500/30"></span> 警告/错误</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-slate-500/30"></span> 系统信息</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-purple-500/30"></span> 系统消息</span>
        </div>
    </div>

</div>

<style>
    /* 美化复选框开关 */
    .toggle-checkbox {
        appearance: none;
        width: 2.5rem;
        height: 1.5rem;
        background-color: #d1d5db; /* gray-300 */
        border-radius: 9999px;
        position: relative;
        transition: background-color 0.2s;
    }
    .toggle-checkbox:checked {
        background-color: #6366f1; /* indigo-500 */
    }
    .toggle-checkbox::before {
        content: "";
        position: absolute;
        top: 0.125rem;
        left: 0.125rem;
        width: 1.25rem;
        height: 1.25rem;
        background-color: white;
        border-radius: 9999px;
        transition: transform 0.2s;
    }
    .toggle-checkbox:checked::before {
        transform: translateX(1rem);
    }

    /* 数值滑块样式 */
    .range-slider {
        -webkit-appearance: none;
        width: 100%;
        height: 0.5rem;
        background: #e5e7eb; /* gray-200 */
        border-radius: 0.25rem;
        outline: none;
    }
    .range-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 1rem;
        height: 1rem;
        background: #6366f1; /* indigo-500 */
        border-radius: 50%;
        cursor: pointer;
    }
    .range-slider::-moz-range-thumb {
        width: 1rem;
        height: 1rem;
        background: #6366f1;
        border-radius: 50%;
        cursor: pointer;
    }

    /* 配置面板 Tab 样式 */
    .config-tab {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #64748b;
        border-bottom: 2px solid transparent;
        background: transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        margin-bottom: -1px;
    }
    .config-tab:hover {
        color: #334155;
        border-bottom-color: #cbd5e1;
    }
    .config-tab.active {
        color: #059669;
        border-bottom-color: #10b981;
    }
    .config-tab-content {
        animation: tabFadeIn 0.15s ease;
    }
    @keyframes tabFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .dark .config-tab {
        color: #94a3b8;
    }
    .dark .config-tab:hover {
        color: #cbd5e1;
        border-bottom-color: #475569;
    }
    .dark .config-tab.active {
        color: #34d399;
        border-bottom-color: #34d399;
    }
</style>

<script>

(function() {
    const output = document.getElementById('consoleOutput');
    const input = document.getElementById('consoleInput');
    const sendBtn = document.getElementById('sendCommandBtn');
    const clearBtn = document.getElementById('clearConsoleBtn');
    const statusEl = document.getElementById('rconStatus');
    const serverStatusEl = document.getElementById('serverStatus');
    const startServerBtn = document.getElementById('startServerBtn');
    const configToggleBtn = document.getElementById('configToggleBtn');
    const configPanel = document.getElementById('configPanel');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 日志控制
    const logPauseBtn = document.getElementById('logPauseBtn');
    const logAutoScrollBtn = document.getElementById('logAutoScrollBtn');
    const logInfo = document.getElementById('logInfo');

    // ========== 自适应日志轮询 ==========
    let logPaused = false;
    let logAutoScroll = true;
    let logTimer = null;
    let logPos = 0;
    let logFileSize = 0;
    let logLineCount = 0;
    const LOG_MAX_LINES = 2000;
    let cmdHistory = [];
    let cmdHistoryIndex = -1;
    let executing = false;
    let logFetching = false;                // 请求去重：防止并发请求
    let logBurstCount = 0;                 // 爆发计数：连续有新数据则加速
    let logErrorCount = 0;                 // 错误计数：指数退避
    let logIdleCount = 0;                  // 空闲计数：长期无数据则降速
    const LOG_POLL_FAST = 800;             // 快速轮询间隔（ms）
    const LOG_POLL_NORMAL = 1500;          // 正常轮询间隔
    const LOG_POLL_IDLE = 3000;            // 空闲降速间隔
    const LOG_POLL_HIDDEN = 5000;          // 页面隐藏时间隔
    const LOG_POLL_ERROR_BASE = 2000;      // 错误退避基础间隔
    const LOG_BURST_MAX = 3;               // 最多连续快速轮询次数
    const LOG_IDLE_THRESHOLD = 6;          // 连续 N 次无数据后降速

    // ========== localStorage 缓存 ==========
    const CACHE_KEY = 'mc_console_state';
    const CONFIG_PANEL_KEY = 'mc_console_config_panel';
    function saveState() {
        try {
            localStorage.setItem(CACHE_KEY, JSON.stringify({
                logPos: logPos,
                logAutoScroll: logAutoScroll,
                logPaused: logPaused,
                ts: Date.now()
            }));
        } catch(e) {}
    }
    function loadState() {
        try {
            const raw = localStorage.getItem(CACHE_KEY);
            if (!raw) return;
            const state = JSON.parse(raw);
            if (state.ts && Date.now() - state.ts < 30 * 60 * 1000) {
                if (typeof state.logPos === 'number' && state.logPos > 0) logPos = state.logPos;
                if (typeof state.logAutoScroll === 'boolean') logAutoScroll = state.logAutoScroll;
                if (typeof state.logPaused === 'boolean') logPaused = state.logPaused;
                return true;
            }
        } catch(e) {}
        return false;
    }

    // ========== 服务器配置面板 ==========
    configToggleBtn.addEventListener('click', function() {
        configPanel.classList.toggle('hidden');
        const isOpen = !configPanel.classList.contains('hidden');
        try { localStorage.setItem(CONFIG_PANEL_KEY, isOpen); } catch(e) {}
        if (isOpen) {
            loadServerConfig();
            loadProperties();
        }
    });

    // 恢复配置面板状态
    try {
        if (localStorage.getItem(CONFIG_PANEL_KEY) === 'true') {
            configPanel.classList.remove('hidden');
            loadServerConfig();
            loadProperties();
        }
    } catch(e) {}

    // Tab 切换
    document.querySelectorAll('.config-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.config-tab').forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');
            document.querySelectorAll('.config-tab-content').forEach(function(c) { c.classList.add('hidden'); });
            document.getElementById(this.getAttribute('data-tab')).classList.remove('hidden');
            // 切换到游戏规则 tab 时自动加载
            if (this.getAttribute('data-tab') === 'tabGame') {
                loadProperties();
            }
        });
    });

    async function loadServerConfig() {
        try {
            const res = await fetch('{{ route("admin.console.config") }}', { credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.ok) {
                const c = data.config;
                document.getElementById('cfgMcServerPath').value = c.mc_server_path || '';
                document.getElementById('cfgMcHost').value = c.mc_host || '127.0.0.1';
                document.getElementById('cfgMcPort').value = c.mc_port || '25565';
                document.getElementById('cfgQueryPort').value = c.query_port || '25565';
                document.getElementById('cfgRconHost').value = c.rcon_host || '127.0.0.1';
                document.getElementById('cfgRconPort').value = c.rcon_port || '25575';
                document.getElementById('cfgRconPassword').value = c.rcon_password || '';
                document.getElementById('cfgJavaPath').value = c.java_path || 'java';
                document.getElementById('cfgJavaXms').value = c.java_xms || '1G';
                document.getElementById('cfgJavaXmx').value = c.java_xmx || '4G';
                document.getElementById('cfgStartCommand').value = c.start_command || '';
                document.getElementById('cfgStopCommand').value = c.stop_command || 'stop';
                document.getElementById('cfgAutoRestart').value = c.auto_restart ? 'true' : 'false';
                document.getElementById('cfgBackupPath').value = c.backup_path || '';
                document.getElementById('configStatus').textContent = '已加载';
            }
        } catch(e) {
            document.getElementById('configStatus').textContent = '加载失败';
        }
    }

    document.getElementById('saveConfigBtn').addEventListener('click', async function() {
        const btn = this;
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>保存中</span>';

        try {
            const formData = new FormData();
            formData.append('mc_server_path', document.getElementById('cfgMcServerPath').value.trim());
            formData.append('mc_host', document.getElementById('cfgMcHost').value.trim());
            formData.append('mc_port', document.getElementById('cfgMcPort').value.trim());
            formData.append('query_port', document.getElementById('cfgQueryPort').value.trim());
            formData.append('rcon_host', document.getElementById('cfgRconHost').value.trim());
            formData.append('rcon_port', document.getElementById('cfgRconPort').value.trim());
            formData.append('rcon_password', document.getElementById('cfgRconPassword').value.trim());
            formData.append('java_path', document.getElementById('cfgJavaPath').value.trim());
            formData.append('java_xms', document.getElementById('cfgJavaXms').value.trim());
            formData.append('java_xmx', document.getElementById('cfgJavaXmx').value.trim());
            formData.append('start_command', document.getElementById('cfgStartCommand').value.trim());
            formData.append('stop_command', document.getElementById('cfgStopCommand').value.trim());
            formData.append('auto_restart', document.getElementById('cfgAutoRestart').value);
            formData.append('backup_path', document.getElementById('cfgBackupPath').value.trim());
            const res = await fetch('{{ route("admin.console.config.update") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: formData,
            });
            const data = await res.json();
            if (data && data.ok) {
                document.getElementById('configStatus').textContent = '已保存';
                appendHtml('<span class="text-purple-400 bg-purple-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 服务器配置已保存</span>');
                checkServerStatus();
                testConnection();
            } else {
                document.getElementById('configStatus').textContent = '保存失败';
                appendHtml('<span class="text-red-400 bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 配置保存失败: ' + escapeHtml(data.message || '未知错误') + '</span>');
            }
        } catch(e) {
            document.getElementById('configStatus').textContent = '网络错误';
            appendHtml('<span class="text-red-400 bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 网络错误: ' + escapeHtml(e.message) + '</span>');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });

    document.getElementById('testRconBtn').addEventListener('click', async function() {
        const btn = this;
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>测试中</span>';

        try {
            const formData = new FormData();
            formData.append('command', 'list');
            const res = await fetch('{{ route("admin.rcon") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: formData,
            });
            const data = await res.json();
            if (data && data.ok) {
                appendHtml('<span class="text-green-400 bg-green-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] RCON 连接测试成功</span>');
                setStatus('已连接', 'green');
            } else {
                appendHtml('<span class="text-red-400 bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] RCON 连接测试失败: ' + escapeHtml(data.message || '') + '</span>');
                setStatus('未连接', 'red');
            }
        } catch(e) {
            appendHtml('<span class="text-red-400 bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] RCON 测试网络错误: ' + escapeHtml(e.message) + '</span>');
            setStatus('未连接', 'red');
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });

    function scrollToBottom() {
        if (!logAutoScroll) return;
        output.scrollTop = output.scrollHeight;
    }

    function setStatus(text, color) {
        const colorMap = {
            green: 'bg-primary-50 text-primary-700 border-primary-200',
            red: 'bg-red-50 text-red-700 border-red-200',
            yellow: 'bg-amber-50 text-amber-700 border-amber-200',
            gray: 'bg-slate-100 text-slate-600 border-slate-200',
        };
        const dotMap = {
            green: 'bg-primary-500',
            red: 'bg-red-500',
            yellow: 'bg-amber-500',
            gray: 'bg-slate-400',
        };
        statusEl.className = 'badge text-xs sm:text-sm px-2.5 py-1 border ' + (colorMap[color] || colorMap.gray);
        statusEl.innerHTML = '<span class="inline-block w-2 h-2 rounded-full mr-1 ' + (dotMap[color] || dotMap.gray) + '"></span>' + text;
    }

    function appendLine(text, cls) {
        const line = document.createElement('div');
        line.className = (cls || 'text-slate-400') + ' leading-relaxed';
        line.textContent = text;
        output.appendChild(line);
        logLineCount++;
        trimOldLines();
        scrollToBottom();
    }

    function appendHtml(html) {
        const div = document.createElement('div');
        div.className = 'leading-relaxed';
        div.innerHTML = html;
        output.appendChild(div);
        logLineCount++;
        trimOldLines();
        scrollToBottom();
    }

    function trimOldLines() {
        while (output.children.length > LOG_MAX_LINES) {
            output.removeChild(output.firstChild);
            logLineCount--;
        }
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    // ========== 自适应日志轮询（核心） ==========
    function getLogPollInterval() {
        if (document.hidden) return LOG_POLL_HIDDEN;
        if (logErrorCount > 0) return Math.min(LOG_POLL_ERROR_BASE * Math.pow(2, logErrorCount - 1), 30000);
        if (logBurstCount > 0 && logBurstCount <= LOG_BURST_MAX) return LOG_POLL_FAST;
        if (logIdleCount >= LOG_IDLE_THRESHOLD) return LOG_POLL_IDLE;
        return LOG_POLL_NORMAL;
    }

    function scheduleLogPoll() {
        stopLogPolling();
        logTimer = setTimeout(runLogPoll, getLogPollInterval());
    }

    function stopLogPolling() {
        if (logTimer) { clearTimeout(logTimer); logTimer = null; }
    }

    async function runLogPoll() {
        if (logPaused) { scheduleLogPoll(); return; }
        if (logFetching) { scheduleLogPoll(); return; } // 请求去重：上一个请求未完成则跳过

        logFetching = true;
        try {
            const url = '{{ route("admin.console.log") }}?after=' + logPos + '&lines=200';
            const res = await fetch(url, { credentials: 'same-origin', cache: 'no-store' });
            const data = await res.json();
            if (!data || !data.ok) {
                logInfo.textContent = '日志错误: ' + (data ? data.message : '请求失败');
                logErrorCount++;
                logBurstCount = 0;
                logIdleCount = 0;
                scheduleLogPoll();
                return;
            }

            logFileSize = data.size;
            logErrorCount = 0; // 请求成功，重置错误计数

            if (data.lines.length === 0) {
                logIdleCount++;
                logBurstCount = 0; // 无新数据，退出爆发模式
                logInfo.textContent = '已是最新 · ' + formatSize(data.size) + ' · ' + logLineCount + ' 行';
                scheduleLogPoll();
                return;
            }

            // 有新数据：重置空闲计数，进入爆发模式
            logIdleCount = 0;
            if (logBurstCount < LOG_BURST_MAX) logBurstCount++;
            else logBurstCount = 0; // 爆发结束后回归正常

            const now = new Date();
            const timePrefix = '<span class="text-slate-600">' +
                String(now.getHours()).padStart(2,'0') + ':' +
                String(now.getMinutes()).padStart(2,'0') + ':' +
                String(now.getSeconds()).padStart(2,'0') + '</span> ';

            const lines = data.lines;
            // 使用 DocumentFragment 批量插入，一次性渲染
            const fragment = document.createDocumentFragment();
            lines.forEach(function(l) {
                const raw = l.raw;
                let cls = 'text-slate-400';
                let bg = '';

                if (l.chat) {
                    cls = 'text-green-300';
                    bg = 'bg-green-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4';
                } else if (/\b(joined the game|logged in)\b/i.test(raw)) {
                    cls = 'text-amber-300';
                    bg = 'bg-amber-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4';
                } else if (/\b(left the game|lost connection|disconnected)\b/i.test(raw)) {
                    cls = 'text-amber-400/70';
                    bg = 'bg-amber-500/5 -mx-3 sm:-mx-4 px-3 sm:px-4';
                } else if (/\b(WARN|ERROR|Exception|FATAL|SEVERE)\b/i.test(raw)) {
                    cls = 'text-red-400';
                    bg = 'bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4';
                } else if (/\bRCON (Client|Listener)\b/i.test(raw)) {
                    cls = 'text-slate-700';
                    bg = 'opacity-40';
                }

                const div = document.createElement('div');
                div.className = 'leading-relaxed';
                div.innerHTML = timePrefix + '<span class="' + cls + (bg ? ' ' + bg : '') + '">' + escapeHtml(raw) + '</span>';
                fragment.appendChild(div);
                logLineCount++;
            });
            output.appendChild(fragment);
            trimOldLines();
            scrollToBottom();

            logPos = data.pos;
            logInfo.textContent = formatSize(data.size) + ' · +' + lines.length + ' 行 · 共 ' + logLineCount + ' 行';
            saveState();

            // 爆发模式下立即再拉一次，不等待间隔
            if (logBurstCount > 0 && logBurstCount <= LOG_BURST_MAX) {
                logFetching = false;
                runLogPoll();
                return;
            }
        } catch(e) {
            logInfo.textContent = '网络错误: ' + e.message;
            logErrorCount++;
            logBurstCount = 0;
            logIdleCount = 0;
        } finally {
            logFetching = false;
        }
        scheduleLogPoll();
    }

    // 页面可见性变化时调整轮询
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && !logPaused) {
            stopLogPolling();
            logBurstCount = 1; // 回到页面时立即快速拉取
            runLogPoll();
        }
    });

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    logPauseBtn.addEventListener('click', function() {
        logPaused = !logPaused;
        if (logPaused) {
            logPauseBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>继续';
            logInfo.textContent = '已暂停 · ' + formatSize(logFileSize);
        } else {
            logPauseBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>暂停';
            logInfo.textContent = '恢复中...';
            runLogPoll();
        }
        saveState();
    });

    logAutoScrollBtn.addEventListener('click', function() {
        logAutoScroll = !logAutoScroll;
        if (logAutoScroll) {
            logAutoScrollBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>自动滚动';
            logJumpBottomBtn.classList.add('hidden');
            scrollToBottom();
        } else {
            logAutoScrollBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>已锁定';
            logJumpBottomBtn.classList.remove('hidden');
        }
        saveState();
    });

    const logJumpBottomBtn = document.getElementById('logJumpBottomBtn');
    logJumpBottomBtn.addEventListener('click', function() {
        output.scrollTop = output.scrollHeight;
    });

    // ========== 命令执行 ==========
    async function executeCommand(cmd) {
        if (executing) return;
        const command = cmd.trim();
        if (!command) return;

        if (cmdHistory.length === 0 || cmdHistory[cmdHistory.length - 1] !== command) {
            cmdHistory.push(command);
        }
        cmdHistoryIndex = cmdHistory.length;

        const wasAutoScroll = logAutoScroll;
        logAutoScroll = true;

        executing = true;
        sendBtn.disabled = true;
        sendBtn.textContent = '执行中...';
        setStatus('执行中...', 'yellow');

        const now = new Date();
        const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');

        appendHtml('<span class="text-amber-400 bg-amber-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[' + timeStr + '] <span class="text-amber-300 font-bold">$</span> ' + escapeHtml(command) + '</span>');

        try {
            const formData = new FormData();
            formData.append('command', command);
            const res = await fetch('{{ route("admin.rcon") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: formData,
            });
            const data = await res.json();

            if (data && data.ok) {
                if (data.response && data.response.trim()) {
                    const lines = data.response.split('\n');
                    lines.forEach(function(l) {
                        if (l.trim()) appendHtml('<span class="text-cyan-300 bg-cyan-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">' + escapeHtml(l) + '</span>');
                    });
                }
                setStatus('已连接', 'green');
            } else {
                const errMsg = (data && data.message) ? data.message : '执行失败';
                appendHtml('<span class="text-red-400 bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">' + escapeHtml(errMsg) + '</span>');
                setStatus('错误', 'red');
            }
        } catch(e) {
            appendHtml('<span class="text-red-400 bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">网络错误: ' + escapeHtml(e.message) + '</span>');
            setStatus('连接失败', 'red');
        } finally {
            executing = false;
            sendBtn.disabled = false;
            sendBtn.textContent = '执行';
            input.value = '';
            input.focus();
            logAutoScroll = wasAutoScroll;
        }
    }

    sendBtn.addEventListener('click', function() {
        executeCommand(input.value);
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            executeCommand(input.value);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (cmdHistory.length > 0) {
                if (cmdHistoryIndex > 0) cmdHistoryIndex--;
                input.value = cmdHistory[cmdHistoryIndex] || '';
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (cmdHistoryIndex < cmdHistory.length - 1) {
                cmdHistoryIndex++;
                input.value = cmdHistory[cmdHistoryIndex] || '';
            } else {
                cmdHistoryIndex = cmdHistory.length;
                input.value = '';
            }
        }
    });

    function clearOutput() {
        output.innerHTML = '';
        logLineCount = 0;
        logPos = 0;
        try { localStorage.removeItem(CACHE_KEY); } catch(e) {}
    }

    clearBtn.addEventListener('click', function() {
        clearOutput();
        appendLine('Minecraft 服务器控制台 — 实时日志 + 命令执行', 'text-slate-500');
        appendLine('---', 'text-slate-700');
        runLogPoll();
    });

    // 快捷命令
    document.querySelectorAll('.quick-cmd-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const cmd = this.getAttribute('data-cmd');
            input.value = cmd;
            input.focus();
            executeCommand(cmd);
        });
    });

    input.focus();

    // ========== 服务器状态检测与启动 ==========
    async function checkServerStatus() {
        try {
            const res = await fetch('{{ route("admin.console.status") }}', { credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.ok) {
                updateServerStatusUI(data.running);
            }
        } catch(e) {}
    }

    function updateServerStatusUI(running) {
        if (running) {
            serverStatusEl.className = 'badge text-xs sm:text-sm px-2.5 py-1 border bg-primary-50 text-primary-700 border-primary-200';
            serverStatusEl.innerHTML = '<span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1"></span>MC 运行中';
            startServerBtn.classList.add('hidden');
        } else {
            serverStatusEl.className = 'badge text-xs sm:text-sm px-2.5 py-1 border bg-red-50 text-red-700 border-red-200';
            serverStatusEl.innerHTML = '<span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span>MC 已停止';
            startServerBtn.classList.remove('hidden');
        }
    }

    startServerBtn.addEventListener('click', async function() {
        if (startServerBtn.disabled) return;
        startServerBtn.disabled = true;
        const origText = startServerBtn.innerHTML;
        startServerBtn.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>启动中...</span>';

        try {
            const res = await fetch('{{ route("admin.console.start") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (data && data.ok) {
                appendHtml('<span class="text-purple-400 bg-purple-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] ' + escapeHtml(data.message) + '</span>');
                appendHtml('<span class="text-purple-400/70 bg-purple-500/5 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 执行命令: ' + escapeHtml(data.command) + '</span>');
                if (data.cwd) appendHtml('<span class="text-purple-400/70 bg-purple-500/5 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 工作目录: ' + escapeHtml(data.cwd) + '</span>');

                let pollCount = 0;
                const pollTimer = setInterval(async () => {
                    pollCount++;
                    try {
                        const sRes = await fetch('{{ route("admin.console.status") }}', { credentials: 'same-origin' });
                        const sData = await sRes.json();
                        if (sData && sData.ok && sData.running) {
                            clearInterval(pollTimer);
                            updateServerStatusUI(true);
                            setStatus('已连接', 'green');
                            appendHtml('<span class="text-green-400 bg-green-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 服务器已启动成功！</span>');
                            startServerBtn.disabled = false;
                            startServerBtn.innerHTML = origText;
                            startServerBtn.classList.add('hidden');
                        } else if (pollCount >= 30) {
                            clearInterval(pollTimer);
                            appendHtml('<span class="text-amber-400 bg-amber-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 等待超时（90秒），请手动检查服务器状态</span>');
                            startServerBtn.disabled = false;
                            startServerBtn.innerHTML = origText;
                        }
                    } catch(e) {
                        if (pollCount >= 30) {
                            clearInterval(pollTimer);
                            startServerBtn.disabled = false;
                            startServerBtn.innerHTML = origText;
                        }
                    }
                }, 3000);
            } else {
                const errMsg = (data && data.message) ? data.message : '启动失败';
                appendHtml('<span class="text-red-400 bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] ' + escapeHtml(errMsg) + '</span>');
                startServerBtn.disabled = false;
                startServerBtn.innerHTML = origText;
            }
        } catch(e) {
            appendHtml('<span class="text-red-400 bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 网络错误: ' + escapeHtml(e.message) + '</span>');
            startServerBtn.disabled = false;
            startServerBtn.innerHTML = origText;
        }
    });

    // ========== 世界存档下载 ==========
    const downloadWorldBtn = document.getElementById('downloadWorldBtn');
    let downloading = false;
    downloadWorldBtn.addEventListener('click', async function() {
        if (downloading) return;
        const confirmed = confirm(
            '即将下载 MC 服务器世界存档。\n\n' +
            '注意：服务器运行中下载可能导致存档损坏！\n' +
            '建议先停止服务器再下载。\n\n' +
            '压缩打包可能需要几分钟，确定继续？'
        );
        if (!confirmed) return;

        downloading = true;
        downloadWorldBtn.disabled = true;
        const origHtml = downloadWorldBtn.innerHTML;
        downloadWorldBtn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>打包中...';

        appendHtml('<span class="text-purple-400 bg-purple-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 正在打包世界存档，请稍候...</span>');

        try {
            // 发起下载请求，后端会流式返回 zip 文件
            const res = await fetch('{{ route("admin.console.world-download") }}', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/zip' },
            });

            if (!res.ok) {
                // 尝试解析 JSON 错误信息
                const contentType = res.headers.get('content-type') || '';
                if (contentType.includes('json')) {
                    const err = await res.json();
                    throw new Error(err.message || '下载失败');
                }
                throw new Error('下载失败 (HTTP ' + res.status + ')');
            }

            // 将响应转为 Blob 并触发浏览器下载
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            // 从 Content-Disposition 头提取文件名，或使用默认名
            const disposition = res.headers.get('content-disposition') || '';
            const match = disposition.match(/filename="?([^";\n]+)"?/);
            a.download = match ? match[1] : 'world_backup.zip';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            appendHtml('<span class="text-green-400 bg-green-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 世界存档下载完成！</span>');
        } catch(e) {
            appendHtml('<span class="text-red-400 bg-red-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 下载失败: ' + escapeHtml(e.message) + '</span>');
        } finally {
            downloading = false;
            downloadWorldBtn.disabled = false;
            downloadWorldBtn.innerHTML = origHtml;
        }
    });

    // 初始检测
    checkServerStatus();
    setInterval(checkServerStatus, 30000);

    // 测试 RCON 连接（静默，仅更新状态）
    async function testConnection() {
        try {
            const formData = new FormData();
            formData.append('command', 'list');
            const res = await fetch('{{ route("admin.rcon") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: formData,
            });
            const data = await res.json();
            setStatus(data && data.ok ? '已连接' : '未连接', data && data.ok ? 'green' : 'red');
        } catch(e) {
            setStatus('未连接', 'red');
        }
    }
    testConnection();

    // 启动日志轮询
    const restored = loadState();

    if (logPaused) {
        logPauseBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>继续';
    }

    if (!logAutoScroll) {
        logAutoScrollBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>已锁定';
        logJumpBottomBtn.classList.remove('hidden');
    }

    if (restored) {
        logInfo.textContent = '从缓存恢复 · ' + formatSize(logFileSize);
    }
    runLogPoll();

    // ====== server.properties 面板 ======
    const loadPropsBtn = document.getElementById('loadPropsBtn');
    const savePropsBtn = document.getElementById('savePropsBtn');
    const propsContent = document.getElementById('propsContent');
    const propsSaveMsg = document.getElementById('propsSaveMsg');
    let originalProps = {};

    // 配置项中文说明 & 描述提示
    const propLabels = {
        // 游戏模式
        'gamemode':          '游戏模式',
        'difficulty':        '难度',
        'hardcore':          '硬核模式',
        'difficulty-lock':   '锁定难度',
        'force-gamemode':    '强制游戏模式',
        // 玩家管理
        'max-players':       '最大玩家数',
        'online-mode':       '正版验证',
        'white-list':        '白名单',
        'enforce-whitelist': '强制白名单',
        'player-idle-timeout': '挂机超时踢出（分钟）',
        'hide-online-players': '隐藏在线玩家',
        'prevent-proxy-connections': '防代理/VPN',
        // 世界设置
        'level-name':        '地图名称',
        'level-seed':        '世界种子',
        'level-type':        '世界类型',
        'generator-settings': '自定义生成器',
        'max-world-size':    '世界边界大小',
        'max-build-height':  '最大建筑高度',
        'spawn-protection':  '出生点保护半径',
        'view-distance':     '视距（区块）',
        'simulation-distance': '模拟距离（区块）',
        // 玩法规则
        'pvp':               'PVP 玩家对战',
        'allow-nether':      '允许下界',
        'allow-flight':      '允许飞行',
        'enable-command-block': '命令方块',
        'op-permission-level': 'OP 权限等级',
        'function-permission-level': '函数权限等级',
        // 生物与建筑
        'spawn-animals':     '生成动物',
        'spawn-monsters':    '生成怪物',
        'spawn-npcs':        '生成村民',
        'generate-structures': '生成村庄/遗迹',
        // 性能优化
        'max-tick-time':     '最大 Tick 时间（ms）',
        'sync-chunk-writes': '同步区块写入',
        'network-compression-threshold': '网络压缩阈值',
        'entity-broadcast-range-percentage': '实体广播范围%',
        'rate-limit':        '速率限制',
        'max-chained-neighbor-updates': '最大连锁更新',
        'use-native-transport': '原生网络传输',
        // 网络连接
        'server-ip':         '服务器 IP',
        'server-port':       '服务器端口',
        'query.port':        '查询端口',
        'enable-query':      '启用 GameSpy 查询',
        'enable-status':     '启用服务器列表状态',
        'enable-rcon':       '启用 RCON',
        'rcon.port':         'RCON 端口',
        'rcon.password':     'RCON 密码',
        // 广播与监控
        'broadcast-console-to-ops': '控制台广播给 OP',
        'broadcast-rcon-to-ops': 'RCON 广播给 OP',
        'enable-jmx-monitoring': 'JMX 性能监控',
        'text-filtering-config': '聊天文本过滤',
        'previews-chat':     '聊天预览',
        // 资源包
        'require-resource-pack': '强制资源包',
        'resource-pack':     '资源包下载地址',
        'resource-pack-prompt': '资源包提示语',
        'resource-pack-sha1': '资源包 SHA1 校验',
        // 显示
        'motd':              '服务器 MOTD 标题',
        'server-name':       '服务器名称',
    };
    // 配置项描述提示（hover 显示）
    const propHints = {
        'online-mode': '关闭后盗版玩家可进入，但安全性降低',
        'hardcore': '开启后玩家死亡即被封禁，无法重生',
        'difficulty-lock': '开启后锁定难度，游戏中无法更改',
        'force-gamemode': '开启后每次登录强制恢复为默认游戏模式',
        'hide-online-players': '开启后玩家列表不显示其他在线玩家',
        'prevent-proxy-connections': '开启后阻止使用 VPN/代理的玩家连接',
        'player-idle-timeout': '玩家挂机超过此时间（分钟）将被自动踢出，0=禁用',
        'enforce-whitelist': '开启后不在白名单的玩家会被立即踢出',
        'max-tick-time': '单个 tick 超过此时间会触发看门狗崩溃，-1=禁用',
        'sync-chunk-writes': '关闭可提升写入性能，但崩溃时可能丢失区块数据',
        'entity-broadcast-range-percentage': '控制实体可见范围，降低可减少网络负载',
        'use-native-transport': '使用 Linux epoll / Windows IOCP 优化网络性能',
        'broadcast-console-to-ops': '开启后控制台命令会广播给所有 OP 玩家',
        'broadcast-rcon-to-ops': '开启后 RCON 命令会广播给所有 OP 玩家',
        'require-resource-pack': '开启后玩家必须使用指定资源包才能进入',
        'max-chained-neighbor-updates': '限制连锁方块更新数量，防止红石机器卡服',
        'enable-jmx-monitoring': '开启 JMX 端口，允许外部工具监控 JVM 性能',
        'text-filtering-config': '启用聊天文本过滤（需客户端配合）',
        'previews-chat': '开启后输入聊天时显示预览',
        'enable-status': '关闭后服务器不会出现在多人游戏列表中',
        'enable-query': '启用 UDP 查询协议，支持外部工具获取服务器信息',
    };
    const selectOptions = {
        'gamemode': { 'survival': '生存', 'creative': '创造', 'adventure': '冒险', 'spectator': '旁观' },
        'difficulty': { 'peaceful': '和平', 'easy': '简单', 'normal': '普通', 'hard': '困难' },
        'level-type': { 'default': '默认', 'flat': '超平坦', 'largebiomes': '大型生物群系', 'amplified': '放大化', 'buffet': '自定义', 'caves': '洞穴' },
        'op-permission-level': { '1': '1 - 绕过出生点保护', '2': '2 - 命令方块+踢人封禁', '3': '3 - 多数管理命令', '4': '4 - 所有命令(含停止)' },
        'function-permission-level': { '1': '1 - 基础', '2': '2 - 中级', '3': '3 - 高级', '4': '4 - 全部' },
    };
    const booleanKeys = ['pvp', 'allow-flight', 'allow-nether', 'enable-command-block', 'white-list', 'enforce-whitelist', 'hardcore', 'force-gamemode', 'spawn-animals', 'spawn-monsters', 'spawn-npcs', 'generate-structures', 'enable-rcon', 'online-mode', 'hide-online-players', 'prevent-proxy-connections', 'difficulty-lock', 'enable-query', 'enable-status', 'sync-chunk-writes', 'use-native-transport', 'broadcast-console-to-ops', 'broadcast-rcon-to-ops', 'enable-jmx-monitoring', 'text-filtering-config', 'previews-chat', 'require-resource-pack'];
    const numberKeys = ['max-players', 'max-world-size', 'max-build-height', 'spawn-protection', 'view-distance', 'simulation-distance', 'server-port', 'query.port', 'rcon.port', 'player-idle-timeout', 'max-tick-time', 'network-compression-threshold', 'entity-broadcast-range-percentage', 'rate-limit', 'max-chained-neighbor-updates', 'op-permission-level', 'function-permission-level'];

    function renderForm(properties) {
        const categories = [
            { title: '游戏模式', icon: '🎮', keys: ['gamemode', 'difficulty', 'hardcore', 'difficulty-lock', 'force-gamemode'] },
            { title: '玩家管理', icon: '👥', keys: ['max-players', 'online-mode', 'white-list', 'enforce-whitelist', 'player-idle-timeout', 'hide-online-players', 'prevent-proxy-connections'] },
            { title: '世界设置', icon: '🌍', keys: ['level-name', 'level-seed', 'level-type', 'generator-settings', 'max-world-size', 'max-build-height', 'spawn-protection', 'view-distance', 'simulation-distance'] },
            { title: '玩法规则', icon: '⚔️', keys: ['pvp', 'allow-flight', 'allow-nether', 'enable-command-block', 'op-permission-level', 'function-permission-level'] },
            { title: '生物与建筑', icon: '🐄', keys: ['spawn-animals', 'spawn-monsters', 'spawn-npcs', 'generate-structures'] },
            { title: '性能优化', icon: '⚡', keys: ['max-tick-time', 'sync-chunk-writes', 'network-compression-threshold', 'entity-broadcast-range-percentage', 'rate-limit', 'max-chained-neighbor-updates', 'use-native-transport'] },
            { title: '网络连接', icon: '🌐', keys: ['server-ip', 'server-port', 'query.port', 'enable-query', 'enable-status', 'enable-rcon', 'rcon.port', 'rcon.password'] },
            { title: '广播与监控', icon: '📡', keys: ['broadcast-console-to-ops', 'broadcast-rcon-to-ops', 'enable-jmx-monitoring', 'text-filtering-config', 'previews-chat'] },
            { title: '资源包', icon: '📦', keys: ['require-resource-pack', 'resource-pack', 'resource-pack-prompt', 'resource-pack-sha1'] },
            { title: '服务器显示', icon: '🏷️', keys: ['motd', 'server-name'] },
        ];
        const used = new Set();
        let html = '<div class="space-y-3">';

        function renderField(k) {
            if (!(k in properties)) return '';
            used.add(k);
            const label = propLabels[k] || k;
            const hint = propHints[k] || '';
            const val = properties[k] == null ? '' : String(properties[k]);
            const id = 'prop_' + k.replace(/[^a-zA-Z0-9_-]/g, '_');
            let control = '';

            if (booleanKeys.includes(k)) {
                const checked = ['true', '1', 'yes', 'on'].includes(val.toLowerCase());
                control = '<label class="relative inline-flex items-center cursor-pointer">' +
                    '<input type="checkbox" class="sr-only peer prop-control" id="' + id + '" data-prop-key="' + escapeHtml(k) + '" ' + (checked ? 'checked' : '') + '>' +
                    '<span class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></span>' +
                    '<span class="ml-2.5 text-xs font-medium ' + (checked ? 'text-emerald-600' : 'text-slate-400') + '">' + (checked ? '开启' : '关闭') + '</span></label>';
            } else if (selectOptions[k]) {
                control = '<select class="input text-xs py-1.5 px-2 prop-control w-44" id="' + id + '" data-prop-key="' + escapeHtml(k) + '">';
                Object.entries(selectOptions[k]).forEach(([optVal, optLabel]) => {
                    control += '<option value="' + escapeHtml(optVal) + '"' + (val === optVal ? ' selected' : '') + '>' + escapeHtml(optLabel) + '</option>';
                });
                if (!Object.prototype.hasOwnProperty.call(selectOptions[k], val)) control += '<option value="' + escapeHtml(val) + '" selected>' + escapeHtml(val) + '</option>';
                control += '</select>';
            } else {
                const type = numberKeys.includes(k) ? 'number' : 'text';
                const cls = type === 'number' ? 'input text-xs py-1.5 px-2 prop-control w-28 font-mono' : 'input text-xs py-1.5 px-2 prop-control font-mono';
                control = '<input type="' + type + '" class="' + cls + '" id="' + id + '" data-prop-key="' + escapeHtml(k) + '" value="' + escapeHtml(val) + '" placeholder="' + escapeHtml(k) + '">';
            }
            const hintHtml = hint ? '<span class="text-[10px] text-slate-400 mt-0.5 block leading-tight" title="' + escapeHtml(hint) + '">' + escapeHtml(hint) + '</span>' : '';
            return '<div class="bg-white rounded-lg border border-slate-200 hover:border-slate-300 transition px-3 py-2.5"><div class="flex items-center justify-between gap-2"><div class="min-w-0 flex-1"><label class="block text-xs font-medium text-slate-700 truncate" for="' + id + '">' + escapeHtml(label) + '</label><span class="text-[10px] text-slate-400">' + escapeHtml(k) + '</span>' + hintHtml + '</div><div class="flex-shrink-0">' + control + '</div></div></div>';
        }

        categories.forEach(function(category) {
            const fields = category.keys.filter(k => k in properties);
            if (!fields.length) return;
            html += '<div class="rounded-xl border border-slate-200 bg-slate-50/60 overflow-hidden"><div class="flex items-center gap-2 px-3 py-2.5 bg-white border-b border-slate-100"><span class="text-base">' + category.icon + '</span><h3 class="text-sm font-semibold text-slate-700">' + category.title + '</h3><span class="text-[11px] text-slate-400 ml-auto">' + fields.length + ' 项</span></div><div class="p-2.5 space-y-1.5">';
            fields.forEach(k => { html += renderField(k); });
            html += '</div></div>';
        });

        // 未分类的配置
        const uncategorized = Object.keys(properties).filter(k => !used.has(k));
        if (uncategorized.length) {
            html += '<div class="rounded-xl border border-slate-200 bg-slate-50/60 overflow-hidden"><div class="flex items-center gap-2 px-3 py-2.5 bg-white border-b border-slate-100"><span class="text-base">📋</span><h3 class="text-sm font-semibold text-slate-700">其他设置</h3><span class="text-[11px] text-slate-400 ml-auto">' + uncategorized.length + ' 项</span></div><div class="p-2.5 space-y-1.5">';
            uncategorized.forEach(k => { html += renderField(k); });
            html += '</div></div>';
        }
        html += '</div>';
        propsContent.innerHTML = html;
        savePropsBtn.classList.remove('hidden');

        // 复选框切换文本
        propsContent.querySelectorAll('input[type="checkbox"]').forEach(function(el) {
            el.addEventListener('change', function() {
                const text = this.parentElement.querySelector('span:last-child');
                if (text) { text.textContent = this.checked ? '开启' : '关闭'; text.className = 'ml-2.5 text-xs font-medium ' + (this.checked ? 'text-emerald-600' : 'text-slate-400'); }
            });
        });
    }

    async function loadProperties() {
        loadPropsBtn.disabled = true;
        loadPropsBtn.textContent = '加载中...';
        try {
            const r = await fetch('{{ route("admin.console.properties") }}', { credentials: 'same-origin' });
            const d = await r.json();
            if (d.ok) {
                originalProps = d.properties || {};
                renderForm(originalProps);
            } else {
                propsContent.innerHTML = '<span class="text-red-500">' + escapeHtml(d.message) + '</span>';
            }
        } catch(e) {
            propsContent.innerHTML = '<span class="text-red-500">加载失败：' + escapeHtml(e.message) + '</span>';
        }
        loadPropsBtn.disabled = false;
        loadPropsBtn.textContent = '读取配置';
    }

    async function saveProperties() {
        const updates = {};
        Object.keys(originalProps).forEach(k => {
            const el = document.getElementById('prop_' + k.replace(/[^a-zA-Z0-9_-]/g, '_'));
            if (!el) return;
            let newVal;
            if (el.type === 'checkbox') {
                newVal = el.checked ? 'true' : 'false';
            } else {
                newVal = el.value.trim();
            }
            if (newVal !== originalProps[k]) updates[k] = newVal;
        });
        if (!Object.keys(updates).length) {
            propsSaveMsg.classList.remove('hidden');
            propsSaveMsg.className = 'text-xs text-amber-600 mt-2';
            propsSaveMsg.textContent = '没有改动';
            return;
        }
        savePropsBtn.disabled = true;
        savePropsBtn.textContent = '保存中...';
        propsSaveMsg.classList.add('hidden');
        try {
            const r = await fetch('{{ route("admin.console.properties.save") }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json','Content-Type': 'application/json'},
                credentials: 'same-origin',
                body: JSON.stringify({updates}),
            });
            const d = await r.json();
            propsSaveMsg.classList.remove('hidden');
            if (d.ok) {
                propsSaveMsg.className = 'text-xs text-primary-600 mt-2';
                propsSaveMsg.textContent = d.message;
                if (d.updated) { for (const [k,v] of Object.entries(d.updated)) { originalProps[k] = v; } }
            } else {
                propsSaveMsg.className = 'text-xs text-red-600 mt-2';
                propsSaveMsg.textContent = d.message;
            }
        } catch(e) {
            propsSaveMsg.classList.remove('hidden');
            propsSaveMsg.className = 'text-xs text-red-600 mt-2';
            propsSaveMsg.textContent = '保存失败：' + e.message;
        } finally {
            savePropsBtn.disabled = false;
            savePropsBtn.textContent = '保存规则';
        }
    }

    // 按钮事件绑定
    loadPropsBtn.addEventListener('click', loadProperties);
    savePropsBtn.addEventListener('click', saveProperties);
})();
</script>

@endsection