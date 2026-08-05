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
        </div>
    </div>

    {{-- 服务器配置面板 --}}
    <div id="configPanel" class="card p-3 sm:p-4 hidden">
        <div class="flex items-center justify-between mb-3">
            <p class="font-medium text-slate-900 text-sm flex items-center gap-1.5">
                @include('layouts.partials.icons', ['name' => 'cog', 'class' => 'w-4 h-4'])服务器配置
            </p>
            <span id="configStatus" class="text-xs text-slate-400"></span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">MC 服务器路径</label>
                <input type="text" id="cfgMcServerPath" placeholder="/home/mc/server" class="input w-full text-sm py-2">
                <p class="text-[11px] text-slate-400 mt-0.5">日志、玩家数据等文件的根目录</p>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">RCON 主机</label>
                <input type="text" id="cfgRconHost" placeholder="127.0.0.1" class="input w-full text-sm py-2">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">RCON 端口</label>
                <input type="text" id="cfgRconPort" placeholder="25575" class="input w-full text-sm py-2">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">RCON 密码</label>
                <input type="password" id="cfgRconPassword" placeholder="输入 RCON 密码" class="input w-full text-sm py-2">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-500 mb-1">启动命令</label>
                <input type="text" id="cfgStartCommand" placeholder="cd /home/mc/server && java -Xmx4G -jar server.jar nogui" class="input w-full text-sm py-2 font-mono">
                <p class="text-[11px] text-slate-400 mt-0.5">仅在服务器未运行时可使用「启动服务器」按钮执行</p>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-3">
            <button type="button" id="saveConfigBtn" class="btn-primary text-xs px-4 py-2">
                @include('layouts.partials.icons', ['name' => 'check', 'class' => 'w-3.5 h-3.5 mr-1'])保存配置
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
        <div id="consoleOutput" class="bg-slate-950 text-green-400 font-mono text-xs sm:text-sm p-3 sm:p-4 overflow-y-auto overflow-x-hidden" style="height:calc(100vh - 380px);min-height:400px;">
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

    {{-- 服务器配置 ──}}
    <div class="card p-3 sm:p-4" id="serverPropsPanel">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-slate-700">⚙️ 服务器配置（server.properties）</p>
            <div class="flex items-center gap-2">
                <button type="button" id="loadPropsBtn" class="btn-secondary text-xs px-3 py-1">读取配置</button>
                <button type="button" id="savePropsBtn" class="btn-primary text-xs px-3 py-1 hidden">保存</button>
            </div>
        </div>
        <div id="propsContent" class="text-xs text-slate-500">点击「读取配置」加载 server.properties</div>
        <div id="propsSaveMsg" class="text-xs mt-2 hidden"></div>
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
        if (isOpen) loadServerConfig();
    });

    // 恢复配置面板状态
    try {
        if (localStorage.getItem(CONFIG_PANEL_KEY) === 'true') {
            configPanel.classList.remove('hidden');
            loadServerConfig();
        }
    } catch(e) {}

    async function loadServerConfig() {
        try {
            const res = await fetch('{{ route("admin.console.config") }}', { credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.ok) {
                document.getElementById('cfgMcServerPath').value = data.config.mc_server_path || '';
                document.getElementById('cfgRconHost').value = data.config.rcon_host || '127.0.0.1';
                document.getElementById('cfgRconPort').value = data.config.rcon_port || '25575';
                document.getElementById('cfgRconPassword').value = data.config.rcon_password || '';
                document.getElementById('cfgStartCommand').value = data.config.start_command || '';
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
            formData.append('rcon_host', document.getElementById('cfgRconHost').value.trim());
            formData.append('rcon_port', document.getElementById('cfgRconPort').value.trim());
            formData.append('rcon_password', document.getElementById('cfgRconPassword').value.trim());
            formData.append('start_command', document.getElementById('cfgStartCommand').value.trim());
            const res = await fetch('{{ route("admin.console.config.update") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: formData,
            });
            const data = await res.json();
            if (data && data.ok) {
                document.getElementById('configStatus').textContent = '已保存';
                appendHtml('<span class="text-purple-400 bg-purple-500/10 -mx-3 sm:-mx-4 px-3 sm:px-4">[系统] 配置已保存</span>');
                // 重新检测服务器状态
                checkServerStatus();
                // 重新测试 RCON
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

    // ========== 日志流 ==========
    function startLogPolling() {
        stopLogPolling();
        logTimer = setInterval(fetchLog, 2000);
    }

    function stopLogPolling() {
        if (logTimer) { clearInterval(logTimer); logTimer = null; }
    }

    async function fetchLog() {
        if (logPaused) return;
        try {
            const url = '{{ route("admin.console.log") }}?after=' + logPos + '&lines=200';
            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            if (!data || !data.ok) {
                logInfo.textContent = '日志错误: ' + (data ? data.message : '请求失败');
                return;
            }
            logFileSize = data.size;
            if (data.lines.length === 0) {
                logInfo.textContent = '已是最新 · ' + formatSize(data.size) + ' · ' + logLineCount + ' 行';
                return;
            }

            const now = new Date();
            const timePrefix = '<span class="text-slate-600">' +
                String(now.getHours()).padStart(2,'0') + ':' +
                String(now.getMinutes()).padStart(2,'0') + ':' +
                String(now.getSeconds()).padStart(2,'0') + '</span> ';

            const lines = data.lines;
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
                    // 屏蔽 RCON 连接/断开日志
                    cls = 'text-slate-700';
                    bg = 'opacity-40';
                }

                appendHtml(timePrefix + '<span class="' + cls + (bg ? ' ' + bg : '') + '">' + escapeHtml(raw) + '</span>');
            });

            logPos = data.pos;
            logInfo.textContent = formatSize(data.size) + ' · +' + lines.length + ' 行 · 共 ' + logLineCount + ' 行';
            saveState();
        } catch(e) {
            logInfo.textContent = '网络错误: ' + e.message;
        }
    }

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
            fetchLog();
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
        fetchLog();
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
    fetchLog();
    startLogPolling();

    // ====== server.properties 面板 ======
    const loadPropsBtn = document.getElementById('loadPropsBtn');
    const savePropsBtn = document.getElementById('savePropsBtn');
    const propsContent = document.getElementById('propsContent');
    const propsSaveMsg = document.getElementById('propsSaveMsg');
    let originalProps = {};

    // 常见配置项 + 中文说明
    const propLabels = {
        'motd':              '服务器标题 (motd)',
        'server-name':       '服务器名称',
        'gamemode':          '游戏模式',
        'difficulty':        '难度',
        'max-players':       '最大玩家数',
        'max-world-size':    '世界大小',
        'spawn-protection':  '出生点保护半径',
        'pvp':               'PVP',
        'allow-nether':      '允许下界',
        'allow-flight':      '允许飞行',
        'enable-command-block': '命令方块',
        'white-list':        '白名单',
        'enforce-whitelist': '强制白名单',
        'hardcore':          '硬核模式',
        'force-gamemode':    '强制游戏模式',
        'view-distance':     '视距',
        'simulation-distance': '模拟距离',
        'spawn-animals':     '生成动物',
        'spawn-monsters':    '生成怪物',
        'spawn-npcs':        '生成NPC',
        'generate-structures': '生成建筑',
        'level-seed':         '世界种子',
        'level-name':         '地图名',
        'level-type':         '世界类型',
        'enable-rcon':       'RCON',
        'rcon.password':     'RCON密码',
        'rcon.port':         'RCON端口',
        'server-port':       '服务器端口',
        'server-ip':         '服务器IP',
        'query.port':        '查询端口',
    };
    const selectOptions = {
        'gamemode': { 'survival': '生存', 'creative': '创造', 'adventure': '冒险', 'spectator': '旁观' },
        'difficulty': { 'peaceful': '和平', 'easy': '简单', 'normal': '普通', 'hard': '困难' },
        'level-type': { 'default': '默认', 'flat': '超平坦', 'largebiomes': '大型生物群系', 'amplified': '放大化' },
    };

    function renderForm(properties) {
        const categories = [
            { title: '基础设置', icon: '🎮', keys: ['motd', 'server-name', 'gamemode', 'difficulty', 'max-players', 'hardcore'] },
            { title: '世界设置', icon: '🌍', keys: ['level-name', 'level-seed', 'level-type', 'max-world-size', 'spawn-protection', 'view-distance', 'simulation-distance'] },
            { title: '游戏规则', icon: '⚔️', keys: ['pvp', 'allow-flight', 'allow-nether', 'enable-command-block', 'force-gamemode', 'white-list', 'enforce-whitelist'] },
            { title: '生物与建筑', icon: '🐄', keys: ['spawn-animals', 'spawn-monsters', 'spawn-npcs', 'generate-structures'] },
            { title: '网络与 RCON', icon: '🌐', keys: ['server-ip', 'server-port', 'query.port', 'enable-rcon', 'rcon.port', 'rcon.password'] },
        ];
        const booleanKeys = ['pvp', 'allow-flight', 'allow-nether', 'enable-command-block', 'white-list', 'enforce-whitelist', 'hardcore', 'force-gamemode', 'spawn-animals', 'spawn-monsters', 'spawn-npcs', 'generate-structures', 'enable-rcon'];
        const used = new Set();
        let html = '<div class="space-y-4">';

        function renderField(k) {
            if (!(k in properties)) return '';
            used.add(k);
            const label = propLabels[k] || k;
            const val = properties[k] == null ? '' : String(properties[k]);
            const id = 'prop_' + k.replace(/[^a-zA-Z0-9_-]/g, '_');
            let control = '';

            if (booleanKeys.includes(k)) {
                const checked = ['true', '1', 'yes', 'on'].includes(val.toLowerCase());
                control = '<label class="relative inline-flex items-center cursor-pointer">' +
                    '<input type="checkbox" class="sr-only peer prop-control" id="' + id + '" data-prop-key="' + escapeHtml(k) + '" ' + (checked ? 'checked' : '') + '>' +
                    '<span class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:bg-primary-500 after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></span>' +
                    '<span class="ml-2 text-xs text-slate-500">' + (checked ? '已开启' : '已关闭') + '</span></label>';
            } else if (selectOptions[k]) {
                control = '<select class="input text-sm prop-control" id="' + id + '" data-prop-key="' + escapeHtml(k) + '">';
                Object.entries(selectOptions[k]).forEach(([optVal, optLabel]) => {
                    control += '<option value="' + escapeHtml(optVal) + '"' + (val === optVal ? ' selected' : '') + '>' + escapeHtml(optLabel) + '</option>';
                });
                if (!Object.prototype.hasOwnProperty.call(selectOptions[k], val)) control += '<option value="' + escapeHtml(val) + '" selected>' + escapeHtml(val) + '</option>';
                control += '</select>';
            } else {
                const type = ['max-players', 'max-world-size', 'spawn-protection', 'view-distance', 'simulation-distance', 'server-port', 'query.port', 'rcon.port'].includes(k) ? 'number' : 'text';
                control = '<input type="' + type + '" class="input text-sm prop-control" id="' + id + '" data-prop-key="' + escapeHtml(k) + '" value="' + escapeHtml(val) + '" placeholder="' + escapeHtml(k) + '">';
            }
            return '<div class="rounded-xl border border-slate-200 bg-white p-3 hover:border-primary-300 transition"><div class="flex items-center justify-between gap-3"><div><label class="block text-sm font-medium text-slate-700" for="' + id + '">' + escapeHtml(label) + '</label><span class="text-[11px] text-slate-400">' + escapeHtml(k) + '</span></div>' + control + '</div></div>';
        }

        categories.forEach(function(category) {
            const fields = category.keys.filter(k => k in properties);
            if (!fields.length) return;
            html += '<section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 sm:p-4"><h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800 mb-3"><span class="text-lg">' + category.icon + '</span>' + category.title + '</h3><div class="grid grid-cols-1 lg:grid-cols-2 gap-3">';
            fields.forEach(k => { html += renderField(k); });
            html += '</div></section>';
        });

        Object.keys(properties).filter(k => !used.has(k)).forEach(function(k) {
            if (!used.size) html += '<section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3"><h3 class="text-sm font-semibold text-slate-800 mb-3">其他设置</h3><div class="grid grid-cols-1 lg:grid-cols-2 gap-3">';
            html += renderField(k);
        });
        if (Object.keys(properties).some(k => !used.has(k))) html += '</div></section>';
        html += '</div>';
        propsContent.innerHTML = html;
        savePropsBtn.classList.remove('hidden');

        propsContent.querySelectorAll('input[type="checkbox"]').forEach(function(el) {
            el.addEventListener('change', function() {
                const text = this.parentElement.querySelector('span:last-child');
                if (text) text.textContent = this.checked ? '已开启' : '已关闭';
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
        const keys = Object.keys(originalProps).concat(Object.keys(selectOptions).filter(k => !originalProps.hasOwnProperty(k)));
        Object.keys(originalProps).forEach(k => {
            const el = document.getElementById('prop_' + k);
            if (!el) return;
            const newVal = el.value.trim();
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
            savePropsBtn.textContent = '保存';
        }
    }

    // 按钮事件绑定
    loadPropsBtn.addEventListener('click', loadProperties);
    savePropsBtn.addEventListener('click', saveProperties);
    // 从导航栏进入“服务器配置”时自动定位并加载面板，避免看起来像入口失效。
    if (window.location.hash === '#serverPropsPanel' || new URLSearchParams(window.location.search).get('section') === 'properties') {
        setTimeout(function() {
            document.getElementById('serverPropsPanel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            loadProperties();
        }, 100);
    }
})();
</script>
<script>
// 独立的配置面板兜底初始化：即使控制台日志脚本异常，也不影响配置按钮。
(function () {
    function initServerPropertiesPanel() {
        const load = document.getElementById('loadPropsBtn');
        const save = document.getElementById('savePropsBtn');
        const content = document.getElementById('propsContent');
        if (!load || !content || load.dataset.bound === '1') return;
        load.dataset.bound = '1';
        load.addEventListener('click', async function () {
            load.disabled = true;
            load.textContent = '加载中...';
            try {
                const response = await fetch('{{ route("admin.console.properties") }}?_=' + Date.now(), {
                    credentials: 'same-origin', cache: 'no-store', headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (!data.ok) throw new Error(data.message || '读取配置失败');
                const props = data.properties || {};
                content.innerHTML = Object.entries(props).map(function ([key, value]) {
                    return '<label class="block mb-2"><span class="block text-xs text-slate-500 mb-1">' + escapeHtml(key) + '</span><input class="input text-sm w-full" data-fallback-prop="' + escapeHtml(key) + '" value="' + escapeHtml(value) + '"></label>';
                }).join('') || '<span class="text-slate-500">配置文件为空</span>';
                if (save) save.classList.remove('hidden');
            } catch (error) {
                content.innerHTML = '<span class="text-red-500">加载失败：' + escapeHtml(error.message) + '</span>';
            } finally {
                load.disabled = false;
                load.textContent = '读取配置';
            }
        });
        if (window.location.hash === '#serverPropsPanel' || new URLSearchParams(window.location.search).get('section') === 'properties') load.click();
    }
    function escapeHtml(value) {
        const node = document.createElement('div');
        node.textContent = value == null ? '' : String(value);
        return node.innerHTML;
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initServerPropertiesPanel);
    else initServerPropertiesPanel();
})();
</script>
@endsection