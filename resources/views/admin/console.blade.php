@extends('layouts.app')

@section('title', '服务器控制台')

@section('content')
<div class="space-y-3 sm:space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="page-title text-slate-900 flex items-center">
                @include('layouts.partials.icons', ['name' => 'terminal', 'class' => 'w-6 h-6 mr-2 flex-shrink-0'])服务器控制台
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1" id="pageSubtitle">实时日志 + RCON 命令控制台</p>
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
            <button type="button" id="startServerBtn" class="btn-primary text-xs sm:text-sm px-3 py-1.5 hidden" title="启动 MC 服务器">
                @include('layouts.partials.icons', ['name' => 'play', 'class' => 'w-4 h-4 mr-1'])启动服务器
            </button>
            <button type="button" id="clearConsoleBtn" class="btn-secondary text-xs sm:text-sm px-3 py-1.5">
                @include('layouts.partials.icons', ['name' => 'scroll', 'class' => 'w-4 h-4'])清屏
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

<script>
(function() {
    const output = document.getElementById('consoleOutput');
    const input = document.getElementById('consoleInput');
    const sendBtn = document.getElementById('sendCommandBtn');
    const clearBtn = document.getElementById('clearConsoleBtn');
    const statusEl = document.getElementById('rconStatus');
    const serverStatusEl = document.getElementById('serverStatus');
    const startServerBtn = document.getElementById('startServerBtn');
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
            // 只恢复 30 分钟内的缓存
            if (state.ts && Date.now() - state.ts < 30 * 60 * 1000) {
                if (typeof state.logPos === 'number' && state.logPos > 0) logPos = state.logPos;
                if (typeof state.logAutoScroll === 'boolean') logAutoScroll = state.logAutoScroll;
                if (typeof state.logPaused === 'boolean') logPaused = state.logPaused;
                return true;
            }
        } catch(e) {}
        return false;
    }

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

        // 暂停日志滚动防止被命令输出打断
        const wasAutoScroll = logAutoScroll;
        logAutoScroll = true;

        executing = true;
        sendBtn.disabled = true;
        sendBtn.textContent = '执行中...';
        setStatus('执行中...', 'yellow');

        const now = new Date();
        const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');

        // 命令输入行：青色背景高亮
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
        // 清除 localStorage 缓存
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

    // 初始焦点
    input.focus();

    // ========== 服务器状态检测与启动 ==========
    async function checkServerStatus() {
        try {
            const res = await fetch('{{ route("admin.console.status") }}', { credentials: 'same-origin' });
            const data = await res.json();
            if (data && data.ok) {
                updateServerStatusUI(data.running);
            }
        } catch(e) {
            // 静默失败
        }
    }

    function updateServerStatusUI(running) {
        if (running) {
            serverStatusEl.className = 'badge text-xs sm:text-sm px-2.5 py-1 border bg-primary-50 text-primary-700 border-primary-200';
            serverStatusEl.innerHTML = '<span class="inline-block w-2 h-2 bg-primary-500 rounded-full mr-1"></span>MC 运行中';
            startServerBtn.classList.add('hidden');
        } else {
            serverStatusEl.className = 'badge text-xs sm:text-sm px-2.5 py-1 border bg-red-50 text-red-700 border-red-200';
            serverStatusEl.innerHTML = '<span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span>MC 已停止';
            @if(!empty(config('services.minecraft.start_command')))
            startServerBtn.classList.remove('hidden');
            @endif
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
    (async function testConnection() {
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
    })();

    // 启动日志轮询
    // 恢复缓存状态
    const restored = loadState();

    // 恢复暂停按钮状态
    if (logPaused) {
        logPauseBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>继续';
    }

    // 恢复自动滚动按钮状态
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
        const keys = Object.keys(properties);
        let html = '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">';
        keys.forEach(function(k) {
            const label = propLabels[k] || k;
            const val = properties[k] || '';
            html += '<div class="flex flex-col gap-1"><label class="text-slate-600 font-medium" for="prop_' + escapeHtml(k) + '">' + escapeHtml(label) + '</label>';
            if (selectOptions[k]) {
                html += '<select class="input text-xs py-1" name="' + escapeHtml(k) + '" id="prop_' + escapeHtml(k) + '">';
                Object.entries(selectOptions[k]).forEach(([optVal, optLabel]) => {
                    html += '<option value="' + escapeHtml(optVal) + '"' + (val === optVal ? ' selected' : '') + '>' + escapeHtml(optLabel) + '</option>';
                });
                html += '</select>';
            } else {
                html += '<input class="input text-xs py-1" id="prop_' + escapeHtml(k) + '" value="' + escapeHtml(val) + '" placeholder="' + escapeHtml(k) + '">';
            }
            html += '</div>';
        });
        html += '</div>';
        propsContent.innerHTML = html;
        savePropsBtn.classList.remove('hidden');
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
})();
</script>
@endsection