<div class="mc-card rounded-lg p-4" id="serverStatusCard">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-bold text-primary-400 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/>
            </svg>
            服务器状态
        </h3>
        <div class="flex items-center gap-1">
            <span id="ssRefreshIcon" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:text-primary-400 cursor-pointer transition" title="立即刷新" onclick="refreshServerStatus(true)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </span>
            <span id="ssStatusDot" class="w-2.5 h-2.5 rounded-full {{ isset($serverStatus) && $serverStatus->is_online ? 'bg-green-500' : 'bg-red-500' }} animate-pulse"></span>
        </div>
    </div>
    <div class="space-y-3" id="ssBody">
        @if(isset($serverStatus) && $serverStatus)
            <div class="flex items-center justify-between">
                <span class="text-gray-400">状态</span>
                <span class="flex items-center">
                    @if($serverStatus->is_online)
                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        <span class="text-green-400 font-bold ss-status-text">在线</span>
                    @else
                        <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                        <span class="text-red-400 font-bold ss-status-text">离线</span>
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-400">玩家</span>
                <span class="text-white font-bold ss-players">{{ $serverStatus->players_online }} / {{ $serverStatus->players_max }}</span>
            </div>
            @if($serverStatus->is_online)
            <div class="pt-2 border-t border-gray-700">
                @if(!empty($serverStatus->players_json))
                    <div class="text-gray-400 text-sm mb-2">在线玩家</div>
                    <div class="flex flex-wrap gap-2 ss-players-list">
                        @foreach($serverStatus->players_json as $player)
                            @php
                                $playerName = isset($player['name']) ? preg_replace('/§./', '', $player['name']) : '未知';
                                $colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'];
                                $colorIndex = ord(strtoupper(substr($playerName, 0, 1))) % count($colors);
                            @endphp
                            <div class="flex items-center bg-gray-700/50 rounded px-2 py-1">
                                <span class="w-6 h-6 rounded-full {{ $colors[$colorIndex] }} mr-2 flex items-center justify-center text-xs font-bold text-white">
                                    {{ strtoupper(substr($playerName, 0, 1)) }}
                                </span>
                                <span class="text-gray-200 text-sm">{{ $playerName }}</span>
                            </div>
                        @endforeach
                    </div>
                @elseif($serverStatus->players_online > 0)
                    <p class="text-gray-500 text-sm text-center py-2 ss-tip">{{ $serverStatus->players_online }} 名玩家在线（服务器未公开玩家列表）</p>
                @else
                    <p class="text-gray-500 text-sm text-center py-2 ss-tip">暂无玩家在线</p>
                @endif
            </div>
            @endif
            @if($serverStatus->version)
            <div class="flex items-center justify-between">
                <span class="text-gray-400">版本</span>
                <span class="text-white text-sm ss-version">{{ $serverStatus->version }}</span>
            </div>
            @endif
            @if($serverStatus->motd)
            <div class="pt-2 border-t border-gray-700">
                <p class="text-gray-300 text-sm ss-motd">{{ $serverStatus->motd }}</p>
            </div>
            @endif
            @if($serverStatus->updated_at)
            <div class="text-xs text-gray-500 text-right ss-updated">
                更新于 {{ $serverStatus->updated_at->diffForHumans() }}
            </div>
            @endif
        @else
            <div class="text-center py-4">
                <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2 inline-block animate-pulse"></span>
                <span class="text-yellow-400">服务器连接中...</span>
            </div>
        @endif
    </div>
</div>

<script>
(function() {
    const colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'];
    function colorFor(name) {
        if (!name) return colors[0];
        const first = strtoupper(String(name).charAt(0));
        const code = first.charCodeAt ? first.charCodeAt(0) : 0;
        return colors[code % colors.length];
    }
    function strtoupper(s) { return s.toUpperCase ? s.toUpperCase() : s; }
    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    let lastRefreshTs = 0;
    async function refreshServerStatusImp(force) {
        const refreshIcon = document.getElementById('ssRefreshIcon');
        if (refreshIcon) refreshIcon.classList.add('animate-spin');
        try {
            let url = '{{ route('server-status') }}?json=1&_=' + Date.now();
            if (force) url += '&force=1';
            const res = await fetch(url, { credentials: 'same-origin', cache: 'no-store' });
            const data = await res.json();
            if (!data || !data.ok) return;
            lastRefreshTs = Date.now();

            // 更新在线状态
            const dot = document.getElementById('ssStatusDot');
            if (dot) {
                dot.className = 'w-2.5 h-2.5 rounded-full ' + (data.online ? 'bg-green-500' : 'bg-red-500') + ' animate-pulse';
            }
            const body = document.getElementById('ssBody');
            const csrf = document.querySelector('meta[name="csrf-token"]');

            const playersHtml = data.players_json && data.players_json.length
                ? '<div class="flex flex-wrap gap-2">' + data.players_json.map(function(p) {
                    const name = (p.name || '未知').replace(/§./g, '');
                    const cls = colorFor(name);
                    const letter = strtoupper(name.charAt(0));
                    return '<div class="flex items-center bg-gray-700/50 rounded px-2 py-1">' +
                        '<span class="w-6 h-6 rounded-full ' + cls + ' mr-2 flex items-center justify-center text-xs font-bold text-white">' + escapeHtml(letter) + '</span>' +
                        '<span class="text-gray-200 text-sm">' + escapeHtml(name) + '</span></div>';
                }).join('') + '</div>'
                : (data.players_online > 0
                    ? '<p class="text-gray-500 text-sm text-center py-2">' + data.players_online + ' 名玩家在线（服务器未公开玩家列表）</p>'
                    : '<p class="text-gray-500 text-sm text-center py-2">暂无玩家在线</p>');

            const motdHtml = data.motd ? '<div class="pt-2 border-t border-gray-700"><p class="text-gray-300 text-sm">' + escapeHtml(data.motd) + '</p></div>' : '';
            const versionHtml = data.version ? '<div class="flex items-center justify-between"><span class="text-gray-400">版本</span><span class="text-white text-sm">' + escapeHtml(data.version) + '</span></div>' : '';
            const updatedHtml = data.updated_at ? '<div class="text-xs text-gray-500 text-right">更新于 ' + escapeHtml(data.updated_at) + '</div>' : '';

            if (body) {
                body.innerHTML =
                    '<div class="flex items-center justify-between"><span class="text-gray-400">状态</span>' +
                    '<span class="flex items-center"><span class="w-3 h-3 ' + (data.online ? 'bg-green-500 animate-pulse' : 'bg-red-500') + ' rounded-full mr-2"></span>' +
                    '<span class="text-' + (data.online ? 'green' : 'red') + '-400 font-bold">' + (data.online ? '在线' : '离线') + '</span></span></div>' +
                    '<div class="flex items-center justify-between"><span class="text-gray-400">玩家</span>' +
                    '<span class="text-white font-bold">' + data.players_online + ' / ' + data.players_max + '</span></div>' +
                    (data.online ? '<div class="pt-2 border-t border-gray-700"><div class="text-gray-400 text-sm mb-2">在线玩家</div>' + playersHtml + '</div>' : '') +
                    versionHtml + motdHtml + updatedHtml;
            }
        } finally {
            if (refreshIcon) setTimeout(function() { refreshIcon.classList.remove('animate-spin'); }, 400);
        }
    }

    window.refreshServerStatus = function(force) {
        refreshServerStatusImp(!!force);
    };

    // 每 30 秒自动刷新一次
    setInterval(function() { window.refreshServerStatus(false); }, 30000);
})();
</script>
