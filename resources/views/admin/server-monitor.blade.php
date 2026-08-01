@extends('layouts.app')

@section('title', '服务器性能监控')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white flex items-center">
                <span class="mr-2">📊</span>服务器性能监控
            </h1>
            <p class="text-gray-400 text-xs sm:text-sm mt-1">仅管理员可访问 · 实时监控服务器运行状态</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="monitorStatus" class="text-xs sm:text-sm px-2.5 py-1 rounded-full bg-green-900/40 text-green-300 border border-green-700/50">
                <span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span>
                实时刷新中
            </span>
            <button type="button" id="refreshMetricsBtn" class="text-xs sm:text-sm px-3 py-1.5 rounded-md bg-primary-700/40 text-primary-200 border border-primary-600/50 hover:bg-primary-700/60 transition font-medium">
                🔄 立即刷新
            </button>
        </div>
    </div>

    {{-- 系统基础运行信息 --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4">
        <div class="mc-card rounded-lg p-3 sm:p-4">
            <div class="text-xs sm:text-sm text-gray-400 mb-1">注册用户</div>
            <div class="text-xl sm:text-2xl font-bold text-primary-400">{{ $stats['total_users'] }}</div>
            <div class="text-xs text-gray-500 mt-1">今日 +{{ $stats['today_registrations'] }}</div>
        </div>
        <div class="mc-card rounded-lg p-3 sm:p-4">
            <div class="text-xs sm:text-sm text-gray-400 mb-1">帖子总数</div>
            <div class="text-xl sm:text-2xl font-bold text-primary-400">{{ $stats['total_threads'] }}</div>
            <div class="text-xs text-gray-500 mt-1">今日 +{{ $stats['today_threads'] }}</div>
        </div>
        <div class="mc-card rounded-lg p-3 sm:p-4">
            <div class="text-xs sm:text-sm text-gray-400 mb-1">回复总数</div>
            <div class="text-xl sm:text-2xl font-bold text-primary-400">{{ $stats['total_replies'] }}</div>
            <div class="text-xs text-gray-500 mt-1">论坛内容健康</div>
        </div>
        <div class="mc-card rounded-lg p-3 sm:p-4">
            <div class="text-xs sm:text-sm text-gray-400 mb-1">MC 服务器</div>
            <div class="text-xl sm:text-2xl font-bold {{ $serverStatus && $serverStatus->is_online ? 'text-green-400' : 'text-red-400' }}">
                {{ $serverStatus && $serverStatus->is_online ? $serverStatus->players_online . ' / ' . $serverStatus->players_max : '离线' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ $serverStatus && $serverStatus->is_online ? '运行中' : '未连接' }}</div>
        </div>
    </div>

    {{-- 服务器系统状态 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="mc-card rounded-lg p-4 sm:p-5">
            <h2 class="text-base sm:text-lg font-bold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                系统环境
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-xs sm:text-sm">
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">操作系统</span><span class="text-gray-200">{{ $system['os'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">Web 服务</span><span class="text-gray-200">{{ $system['server_software'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">PHP 版本</span><span class="text-gray-200">{{ $system['php_version'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">PHP 运行</span><span class="text-gray-200">{{ $system['sapi'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">Laravel</span><span class="text-gray-200">{{ $system['laravel_version'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">数据库驱动</span><span class="text-gray-200">{{ $system['db_driver'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">MC 服务器地址</span><span class="text-gray-200">{{ $system['mc_host'] }}:{{ $system['mc_port'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">时区</span><span class="text-gray-200">{{ $system['timezone'] }}</span></div>
            </div>
        </div>

        <div class="mc-card rounded-lg p-4 sm:p-5">
            <h2 class="text-base sm:text-lg font-bold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                PHP 资源配置
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-xs sm:text-sm">
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">内存限制</span><span class="text-gray-200 font-medium">{{ $system['php_memory_limit'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">当前使用</span><span id="metricMem" class="text-primary-400 font-medium">-</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">上传最大</span><span class="text-gray-200 font-medium">{{ $system['php_upload_max'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">POST 最大</span><span class="text-gray-200 font-medium">{{ $system['php_post_max'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">执行超时</span><span class="text-gray-200 font-medium">{{ $system['php_max_exec'] }}</span></div>
                <div class="flex justify-between py-1.5 border-b border-gray-700/50"><span class="text-gray-400">内存峰值</span><span id="metricMemPeak" class="text-primary-400 font-medium">-</span></div>
            </div>
            @if($load)
                <div class="mt-4 p-3 rounded-lg bg-primary-900/20 border border-primary-700/30">
                    <div class="text-xs sm:text-sm text-gray-300 font-medium mb-1.5">系统负载（Load Average）</div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs sm:text-sm">
                        <div><div class="text-gray-400">1分钟</div><div class="text-primary-300 font-bold text-base sm:text-lg">{{ round($load[0] ?? 0, 2) }}</div></div>
                        <div><div class="text-gray-400">5分钟</div><div class="text-primary-300 font-bold text-base sm:text-lg">{{ round($load[1] ?? 0, 2) }}</div></div>
                        <div><div class="text-gray-400">15分钟</div><div class="text-primary-300 font-bold text-base sm:text-lg">{{ round($load[2] ?? 0, 2) }}</div></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- 磁盘使用（如果有） --}}
    @if($disk)
    <div class="mc-card rounded-lg p-4 sm:p-5">
        <h2 class="text-base sm:text-lg font-bold text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
            磁盘空间（系统盘）
        </h2>
        <div class="grid grid-cols-3 gap-2.5 mb-3 text-xs sm:text-sm">
            <div><div class="text-gray-400">已用</div><div class="text-base sm:text-lg font-bold text-yellow-400">{{ $disk['used'] }}</div></div>
            <div><div class="text-gray-400">可用</div><div class="text-base sm:text-lg font-bold text-green-400">{{ $disk['free'] }}</div></div>
            <div><div class="text-gray-400">总量</div><div class="text-base sm:text-lg font-bold text-gray-200">{{ $disk['total'] }}</div></div>
        </div>
        <div class="w-full h-3 sm:h-4 bg-gray-800 rounded-full overflow-hidden border border-gray-700">
            <div class="h-full rounded-full transition-all" style="width: min({{ $disk['percent'] }}%, 100%); background: linear-gradient(90deg, #4ade80 0%, #22c55e 50%, #eab308 80%, #ef4444 100%);"></div>
        </div>
        <div class="text-right mt-1 text-xs sm:text-sm font-medium {{ $disk['percent'] > 85 ? 'text-red-400' : ($disk['percent'] > 60 ? 'text-yellow-400' : 'text-gray-400') }}">
            使用率 {{ $disk['percent'] }}%
        </div>
    </div>
    @endif

    {{-- MC 服务器状态实时卡片 --}}
    <div class="mc-card rounded-lg p-4 sm:p-5" id="monitor-mc-card">
        <h2 class="text-base sm:text-lg font-bold text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
            MC 服务器状态
            <span id="mcStatusDot" class="ml-auto w-2.5 h-2.5 rounded-full bg-yellow-400 animate-pulse"></span>
        </h2>
        @include('partials.server-status')
    </div>

    {{-- 近期帖子统计（迷你） --}}
    <div class="mc-card rounded-lg p-4 sm:p-5">
        <h2 class="text-base sm:text-lg font-bold text-white mb-3 flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            应用实时指标
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs sm:text-sm" id="appMetrics">
            <div class="rounded-md bg-slate-900/50 p-2 sm:p-3 border border-gray-700/50">
                <div class="text-gray-400">今日新帖</div>
                <div class="text-lg font-bold text-primary-300 app-m-today-th">{{ $stats['today_threads'] }}</div>
            </div>
            <div class="rounded-md bg-slate-900/50 p-2 sm:p-3 border border-gray-700/50">
                <div class="text-gray-400">今日注册</div>
                <div class="text-lg font-bold text-primary-300 app-m-today-u">{{ $stats['today_registrations'] }}</div>
            </div>
            <div class="rounded-md bg-slate-900/50 p-2 sm:p-3 border border-gray-700/50">
                <div class="text-gray-400">总帖子数</div>
                <div class="text-lg font-bold text-gray-200 app-m-th">{{ $stats['total_threads'] }}</div>
            </div>
            <div class="rounded-md bg-slate-900/50 p-2 sm:p-3 border border-gray-700/50">
                <div class="text-gray-400">总用户数</div>
                <div class="text-lg font-bold text-gray-200 app-m-u">{{ $stats['total_users'] }}</div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const statusEl = document.getElementById('monitorStatus');
    const refreshBtn = document.getElementById('refreshMetricsBtn');
    const memEl = document.getElementById('metricMem');
    const memPeakEl = document.getElementById('metricMemPeak');
    const mcDot = document.getElementById('mcStatusDot');
    const appMTodayTh = document.querySelector('.app-m-today-th');
    const appMTodayU = document.querySelector('.app-m-today-u');
    const appMTh = document.querySelector('.app-m-th');
    const appMU = document.querySelector('.app-m-u');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function setStatus(text, color) {
        const colorMap = {
            green: 'bg-green-900/40 text-green-300 border-green-700/50',
            yellow: 'bg-yellow-900/40 text-yellow-200 border-yellow-700/50',
            red: 'bg-red-900/40 text-red-300 border-red-700/50',
        };
        statusEl.className = 'text-xs sm:text-sm px-2.5 py-1 rounded-full border ' + (colorMap[color] || colorMap.green);
        statusEl.innerHTML = text;
    }

    function setMcOnlineColor(online) {
        if (!mcDot) return;
        mcDot.className = 'ml-auto w-2.5 h-2.5 rounded-full ' + (online ? 'bg-green-400' : 'bg-red-400') + ' animate-pulse';
    }

    async function refresh() {
        try {
            setStatus('<span class="inline-block w-2 h-2 bg-yellow-400 rounded-full mr-1 animate-pulse"></span> 刷新中...', 'yellow');
            const res = await fetch('{{ route("admin.monitor.metrics") }}', {
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (data.ok) {
                memEl.textContent = (data.php_memory_usage ?? 0) + ' MB';
                memPeakEl.textContent = (data.php_memory_peak ?? 0) + ' MB';
                if (data.app) {
                    if (appMTodayTh) appMTodayTh.textContent = data.app.today_threads;
                    if (appMTodayU) appMTodayU.textContent = data.app.today_users;
                    if (appMTh) appMTh.textContent = data.app.total_threads;
                    if (appMU) appMU.textContent = data.app.total_users;
                }
                if (data.mc) setMcOnlineColor(data.mc.online);
                setStatus('<span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span> 实时刷新中', 'green');
            } else {
                setStatus('<span class="inline-block w-2 h-2 bg-yellow-400 rounded-full mr-1"></span> 响应异常', 'yellow');
            }
        } catch (e) {
            setStatus('<span class="inline-block w-2 h-2 bg-red-400 rounded-full mr-1"></span> 刷新失败，稍后重试', 'red');
        }
    }

    refreshBtn.addEventListener('click', refresh);

    // 每 10 秒刷新一次指标 + 触发服务器状态 AJAX
    setInterval(function() {
        refresh();
        // 触发服务器状态组件的刷新（如果有）
        if (typeof window.refreshServerStatus === 'function') {
            window.refreshServerStatus(true);
        }
    }, 10000);
})();
</script>
@endsection
