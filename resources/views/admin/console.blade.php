@extends('layouts.app')

@section('title', '服务器控制台')

@section('content')
<div class="space-y-3 sm:space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="page-title text-slate-900 flex items-center">
                @include('layouts.partials.icons', ['name' => 'terminal', 'class' => 'w-6 h-6 mr-2 flex-shrink-0'])服务器控制台
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">通过 RCON 向 MC 服务器发送命令</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="rconStatus" class="badge text-xs sm:text-sm px-2.5 py-1 border bg-slate-100 text-slate-600 border-slate-200">
                <span class="inline-block w-2 h-2 bg-slate-400 rounded-full mr-1"></span>
                未连接
            </span>
            <button type="button" id="clearConsoleBtn" class="btn-secondary text-xs sm:text-sm px-3 py-1.5">
                @include('layouts.partials.icons', ['name' => 'scroll', 'class' => 'w-4 h-4'])清屏
            </button>
        </div>
    </div>

    <div class="card overflow-hidden">
        {{-- 终端输出区 --}}
        <div id="consoleOutput" class="bg-slate-950 text-green-400 font-mono text-xs sm:text-sm p-3 sm:p-4 overflow-y-auto space-y-0.5" style="height:calc(100vh - 300px);min-height:400px;">
            <div class="text-slate-500">Minecraft 服务器控制台 — 输入命令后按 Enter 执行</div>
            <div class="text-slate-600">可用命令: help, list, say, whitelist, ban, kick, op, deop, gamemode, time, weather, tp, give, 等</div>
            <div class="text-slate-600">禁止命令: stop, restart</div>
            <div class="text-slate-700">---</div>
        </div>

        {{-- 命令输入区 --}}
        <div class="border-t border-slate-700 bg-slate-900 px-3 py-2.5 flex items-center gap-2">
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
</div>

<script>
(function() {
    const output = document.getElementById('consoleOutput');
    const input = document.getElementById('consoleInput');
    const sendBtn = document.getElementById('sendCommandBtn');
    const clearBtn = document.getElementById('clearConsoleBtn');
    const statusEl = document.getElementById('rconStatus');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let history = [];
    let historyIndex = -1;
    let executing = false;

    function scrollToBottom() {
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
        statusEl.innerHTML = '<span class="inline-block w-2 h-2 rounded-full mr-1 ' + (dotMap[color] || dotMap.gray) + (!executing ? ' animate-pulse' : '') + '"></span>' + text;
    }

    function appendLine(text, cls) {
        const line = document.createElement('div');
        line.className = cls || 'text-slate-400';
        line.textContent = text;
        output.appendChild(line);
        scrollToBottom();
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    async function executeCommand(cmd) {
        if (executing) return;
        const command = cmd.trim();
        if (!command) return;

        // 添加到历史
        if (history.length === 0 || history[history.length - 1] !== command) {
            history.push(command);
        }
        historyIndex = history.length;

        executing = true;
        sendBtn.disabled = true;
        sendBtn.textContent = '执行中...';
        setStatus('执行中...', 'yellow');

        // 显示输入的命令
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
                    // 多行响应按换行分割
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

    // 发送按钮
    sendBtn.addEventListener('click', function() {
        executeCommand(input.value);
    });

    // 回车发送
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            executeCommand(input.value);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (history.length > 0) {
                if (historyIndex > 0) historyIndex--;
                input.value = history[historyIndex] || '';
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (historyIndex < history.length - 1) {
                historyIndex++;
                input.value = history[historyIndex] || '';
            } else {
                historyIndex = history.length;
                input.value = '';
            }
        }
    });

    // 清屏
    clearBtn.addEventListener('click', function() {
        output.innerHTML = '';
        appendLine('Minecraft 服务器控制台 — 输入命令后按 Enter 执行', 'text-slate-500');
        appendLine('---', 'text-slate-700');
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

    // 测试 RCON 连接
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
                appendLine('RCON 连接成功，服务器在线', 'text-green-500');
            } else {
                const msg = (data && data.message) ? data.message : '无法连接';
                setStatus('未连接', 'red');
                appendLine('连接测试失败: ' + msg, 'text-red-400');
            }
        } catch(e) {
            setStatus('未连接', 'red');
            appendLine('连接测试失败: ' + e.message, 'text-red-400');
        }
    })();
})();
</script>
@endsection