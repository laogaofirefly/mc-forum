@extends('layouts.app')

@section('title', '服务器控制台')

@section('content')
<div class="space-y-3 sm:space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="page-title text-slate-900 flex items-center">
                @include('layouts.partials.icons', ['name' => 'terminal', 'class' => 'w-6 h-6 mr-2 flex-shrink-0'])服务器控制台
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1" id="pageSubtitle">通过 RCON 向 MC 服务器发送命令</p>
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

    {{-- 模式切换标签 --}}
    <div class="flex gap-1 bg-slate-100 rounded-lg p-1 w-fit">
        <button type="button" id="tabCmd" class="px-4 py-1.5 rounded-md text-sm font-medium transition bg-white text-slate-800 shadow-sm">
            @include('layouts.partials.icons', ['name' => 'terminal', 'class' => 'w-4 h-4 inline mr-1'])命令
        </button>
        <button type="button" id="tabLog" class="px-4 py-1.5 rounded-md text-sm font-medium transition text-slate-500 hover:text-slate-700">
            @include('layouts.partials.icons', ['name' => 'scroll', 'class' => 'w-4 h-4 inline mr-1'])日志
        </button>
    </div>

    <div class="card overflow-hidden">
        {{-- 终端输出区 --}}
        <div id="consoleOutput" class="bg-slate-950 text-green-400 font-mono text-xs sm:text-sm p-3 sm:p-4 overflow-y-auto overflow-x-hidden" style="height:calc(100vh - 360px);min-height:400px;">
            <div class="text-slate-500">Minecraft 服务器控制台 — 输入命令后按 Enter 执行</div>
            <div class="text-slate-600">可用命令: help, list, say, whitelist, ban, kick, op, deop, gamemode, time, weather, tp, give, 等</div>
            <div class="text-slate-600">禁止命令: stop, restart</div>
            <div class="text-slate-700">---</div>
        </div>

        {{-- 命令模式输入区 --}}
        <div id="cmdInputArea" class="border-t border-slate-700 bg-slate-900 px-3 py-2.5 flex items-center gap-2">
            <span class="text-green-500 font-mono text-sm flex-shrink-0 select-none">$</span>
            <input
                type="text"
                id="consoleInput"
                autocomplete="off"
                placeholder="输入命令，如 list、say hello..."
                class="flex-1 bg-transparent border-none outline-none text-green-300 font-mono text-sm placeholder-slate-600"
            >
            <button type="button" id="sendCommandBtn" class="btn-primary text-xs sm:text-sm px-3 py-1.5 flex-shrink-0">
                执行
            </button>
        </div>

        {{-- 日志模式控制栏 --}}
        <div id="logControlBar" class="border-t border-slate-700 bg-slate-900 px-3 py-2.5 flex items-center gap-2 hidden">
            <span class="text-xs text-slate-500 flex-1" id="logInfo">等待加载...</span>
            <button type="button" id="logPauseBtn" class="btn-secondary text-xs px-2.5 py-1 flex items-center gap-1">
                @include('layouts.partials.icons', ['name' => 'pause', 'class' => 'w-3.5 h-3.5'])暂停
            </button>
            <button type="button" id="logAutoScrollBtn" class="btn-secondary text-xs px-2.5 py-1 flex items-center gap-1">
                @include('layouts.partials.icons', ['name' => 'chevron-down', 'class' => 'w-3.5 h-3.5'])自动滚动
            </button>
        </div>
    </div>

    {{-- 命令模式：快捷命令 --}}
    <div id="quickCmdsCard" class="card p-3 sm:p-4">
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
            ];
            @endphp
            @foreach($quickCommands as $cmd => $label)
                <button type="button"
                    class="quick-cmd-btn text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700 transition text-slate-600"
                    data-cmd="{{ $cmd }}"
                    title="{{ $label }}">
                    /{{ $cmd }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- 日志模式：图例 --}}
    <div id="logLegend" class="card p-3 sm:p-4 hidden">
        <div class="flex flex-wrap gap-3 text-xs text-slate-500">
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-green-500/30"></span> 聊天消息</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-amber-500/30"></span> 玩家进出</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-slate-500/30"></span> 系统信息</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-red-500/30"></span> 警告/错误</span>
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
    const subtitle = document.getElementById('pageSubtitle');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 模式切换
    const tabCmd = document.getElementById('tabCmd');
    const tabLog = document.getElementById('tabLog');
    const cmdInputArea = document.getElementById('cmdInputArea');
    const logControlBar = document.getElementById('logControlBar');
    const quickCmdsCard = document.getElementById('quickCmdsCard');
    const logLegend = document.getElementById('logLegend');
    const logPauseBtn = document.getElementById('logPauseBtn');
    const logAutoScrollBtn = document.getElementById('logAutoScrollBtn');
    const logInfo = document.getElementById('logInfo');

    let currentMode = 'cmd'; // 'cmd' | 'log'
    let logPaused = false;
    let logAutoScroll = true;
    let logTimer = null;
    let logPos = 0;
    let logFileSize = 0;
    let cmdHistory = [];
    let cmdHistoryIndex = -1;
    let executing = false;

    function scrollToBottom() {
        if (currentMode === 'log' && !logAutoScroll) return;
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
        scrollToBottom();
    }

    function appendHtml(html) {
        const div = document.createElement('div');
        div.className = 'leading-relaxed';
        div.innerHTML = html;
        output.appendChild(div);
        scrollToBottom();
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    // ========== 模式切换 ==========
    function switchMode(mode) {
        currentMode = mode;
        if (mode === 'cmd') {
            tabCmd.className = 'px-4 py-1.5 rounded-md text-sm font-medium transition bg-white text-slate-800 shadow-sm';
            tabLog.className = 'px-4 py-1.5 rounded-md text-sm font-medium transition text-slate-500 hover:text-slate-700';
            cmdInputArea.classList.remove('hidden');
            logControlBar.classList.add('hidden');
            quickCmdsCard.classList.remove('hidden');
            logLegend.classList.add('hidden');
            subtitle.textContent = '通过 RCON 向 MC 服务器发送命令';
            stopLogPolling();
            clearOutput();
            appendLine('Minecraft 服务器控制台 — 输入命令后按 Enter 执行', 'text-slate-500');
            appendLine('可用命令: help, list, say, whitelist, ban, kick, op, deop, gamemode, time, weather, tp, give, 等', 'text-slate-600');
            appendLine('禁止命令: stop, restart', 'text-slate-600');
            appendLine('---', 'text-slate-700');
            input.focus();
        } else {
            tabCmd.className = 'px-4 py-1.5 rounded-md text-sm font-medium transition text-slate-500 hover:text-slate-700';
            tabLog.className = 'px-4 py-1.5 rounded-md text-sm font-medium transition bg-white text-slate-800 shadow-sm';
            cmdInputArea.classList.add('hidden');
            logControlBar.classList.remove('hidden');
            quickCmdsCard.classList.add('hidden');
            logLegend.classList.remove('hidden');
            subtitle.textContent = '实时查看 MC 服务器日志（latest.log）';
            clearOutput();
            appendLine('正在加载日志...', 'text-slate-500');
            logPos = 0;
            logFileSize = 0;
            logPaused = false;
            logPauseBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>暂停';
            logAutoScroll = true;
            logAutoScrollBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>自动滚动';
            fetchLog();
            startLogPolling();
        }
    }

    tabCmd.addEventListener('click', function() { if (currentMode !== 'cmd') switchMode('cmd'); });
    tabLog.addEventListener('click', function() { if (currentMode !== 'log') switchMode('log'); });

    // ========== 日志模式 ==========
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
                logInfo.textContent = '错误: ' + (data ? data.message : '请求失败');
                return;
            }
            logFileSize = data.size;
            if (data.lines.length === 0) {
                logInfo.textContent = '已是最新 · ' + formatSize(data.size);
                return;
            }

            const lines = data.lines;
            lines.forEach(function(l) {
                const raw = l.raw;
                let cls = 'text-slate-400';
                let bg = '';

                if (l.chat) {
                    // 聊天消息高亮
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
                } else if (/\b(INFO)\b/i.test(raw)) {
                    cls = 'text-slate-400';
                }

                appendHtml('<span class="' + cls + (bg ? ' ' + bg : '') + '">' + escapeHtml(raw) + '</span>');
            });

            logPos = data.pos;
            logInfo.textContent = formatSize(data.size) + ' · 已加载 ' + lines.length + ' 行';
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
            logPauseBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>继续';
            logInfo.textContent = '已暂停 · ' + formatSize(logFileSize);
        } else {
            logPauseBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>暂停';
            logInfo.textContent = '恢复中...';
            fetchLog();
        }
    });

    logAutoScrollBtn.addEventListener('click', function() {
        logAutoScroll = !logAutoScroll;
        if (logAutoScroll) {
            logAutoScrollBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>自动滚动';
            scrollToBottom();
        } else {
            logAutoScrollBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>已锁定';
        }
    });

    // ========== 命令模式 ==========
    async function executeCommand(cmd) {
        if (executing) return;
        const command = cmd.trim();
        if (!command) return;

        if (cmdHistory.length === 0 || cmdHistory[cmdHistory.length - 1] !== command) {
            cmdHistory.push(command);
        }
        cmdHistoryIndex = cmdHistory.length;

        executing = true;
        sendBtn.disabled = true;
        sendBtn.textContent = '执行中...';
        setStatus('执行中...', 'yellow');

        const now = new Date();
        const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');
        appendLine('[' + timeStr + '] $ ' + command, 'text-amber-400');

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
                if (data.response) {
                    const lines = data.response.split('\n');
                    lines.forEach(function(l) {
                        if (l.trim()) appendLine(l, 'text-green-300');
                    });
                }
                setStatus('已连接', 'green');
            } else {
                const errMsg = (data && data.message) ? data.message : '执行失败';
                appendLine('错误: ' + errMsg, 'text-red-400');
                setStatus('错误', 'red');
            }
        } catch(e) {
            appendLine('网络错误: ' + e.message, 'text-red-400');
            setStatus('连接失败', 'red');
        } finally {
            executing = false;
            sendBtn.disabled = false;
            sendBtn.textContent = '执行';
            input.value = '';
            input.focus();
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
    }

    clearBtn.addEventListener('click', function() {
        clearOutput();
        if (currentMode === 'cmd') {
            appendLine('Minecraft 服务器控制台 — 输入命令后按 Enter 执行', 'text-slate-500');
            appendLine('---', 'text-slate-700');
        } else {
            appendLine('日志已清空，正在重新加载...', 'text-slate-500');
            logPos = 0;
            fetchLog();
        }
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
                appendLine('[系统] ' + data.message, 'text-amber-400');
                appendLine('[系统] 执行命令: ' + data.command, 'text-slate-500');
                if (data.cwd) appendLine('[系统] 工作目录: ' + data.cwd, 'text-slate-500');
                // 开始轮询等待服务器启动
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
                            appendLine('[系统] 服务器已启动成功！', 'text-green-400');
                            startServerBtn.disabled = false;
                            startServerBtn.innerHTML = origText;
                            startServerBtn.classList.add('hidden');
                        } else if (pollCount >= 30) {
                            clearInterval(pollTimer);
                            appendLine('[系统] 等待超时（90秒），请手动检查服务器状态', 'text-amber-400');
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
                appendLine('[系统] 错误: ' + errMsg, 'text-red-400');
                startServerBtn.disabled = false;
                startServerBtn.innerHTML = origText;
            }
        } catch(e) {
            appendLine('[系统] 网络错误: ' + e.message, 'text-red-400');
            startServerBtn.disabled = false;
            startServerBtn.innerHTML = origText;
        }
    });

    // 初始检测
    checkServerStatus();
    // 每 30 秒检测一次服务器状态
    setInterval(checkServerStatus, 30000);
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
            if (data && data.ok) {
                setStatus('已连接', 'green');
            } else {
                const msg = (data && data.message) ? data.message : '无法连接';
                setStatus('未连接', 'red');
                appendLine('RCON 连接测试失败: ' + msg, 'text-red-400');
            }
        } catch(e) {
            setStatus('未连接', 'red');
            appendLine('RCON 连接测试失败: ' + e.message, 'text-red-400');
        }
    })();
})();
</script>
@endsection