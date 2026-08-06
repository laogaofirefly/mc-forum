{{-- $noCard: 当外部已有 card 包裹时传 true，避免双层嵌套 --}}
@if(!isset($noCard) || !$noCard)
<div class="card p-4 reveal" id="serverStatusCard">
@else
<div class="reveal" id="serverStatusCard">
@endif
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-base font-bold text-slate-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/>
            </svg>
            服务器状态
        </h3>
        <div class="flex items-center gap-2">
            <button type="button" id="ssRefreshIcon" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-md transition cursor-pointer" title="立即刷新" onclick="refreshServerStatus(true)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            <span id="ssStatusDot" class="w-2.5 h-2.5 rounded-full {{ isset($serverStatus) && $serverStatus->is_online ? 'bg-green-500' : 'bg-red-500' }} animate-pulse"></span>
        </div>
    </div>
    <div class="space-y-3" id="ssBody">
        @if(isset($serverStatus) && $serverStatus)
            <div class="flex items-center justify-between">
                <span class="text-slate-500 text-sm">状态</span>
                <span class="flex items-center">
                    @if($serverStatus->is_online)
                        <span class="w-2.5 h-2.5 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        <span class="text-green-600 font-semibold ss-status-text">在线</span>
                    @else
                        <span class="w-2.5 h-2.5 bg-red-500 rounded-full mr-2"></span>
                        <span class="text-red-500 font-semibold ss-status-text">离线</span>
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500 text-sm">玩家</span>
                <span class="text-slate-900 font-bold ss-players">{{ $serverStatus->players_online }} / {{ $serverStatus->players_max }}</span>
            </div>
            @if($serverStatus->is_online)
            <div class="pt-3 border-t border-slate-100">
                @if(!empty($serverStatus->players_json))
                    <div class="text-slate-500 text-xs mb-2">在线玩家</div>
                    <div class="flex flex-wrap gap-1.5 ss-players-list">
                        @foreach($serverStatus->players_json as $player)
                            @php
                                $playerName = isset($player['name']) ? preg_replace('/§./', '', $player['name']) : '未知';
                                $playerUuid = $player['id'] ?? ($player['uuid'] ?? '');
                                $avatarUrl = \App\Services\PlayerAvatarService::url($playerName, $playerUuid);
                            @endphp
                            <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg pl-1 pr-2 py-1">
                                <img src="{{ $avatarUrl }}" alt="{{ $playerName }}" data-fallback="{{ \App\Services\PlayerAvatarService::initialAvatar($playerName) }}" onerror="this.src=this.dataset.fallback;this.onerror=null" class="w-6 h-6 rounded-md mr-1.5 object-cover bg-white flex-shrink-0" loading="lazy">
                                <span class="text-slate-700 text-sm">{{ $playerName }}</span>
                            </div>
                        @endforeach
                    </div>
                @elseif($serverStatus->players_online > 0)
                    <p class="text-slate-400 text-sm text-center py-2 ss-tip">{{ $serverStatus->players_online }} 名玩家在线（服务器未公开玩家列表）</p>
                @else
                    <p class="text-slate-400 text-sm text-center py-2 ss-tip">暂无玩家在线</p>
                @endif
            </div>
            @endif
            @if($serverStatus->version)
            <div class="flex items-center justify-between">
                <span class="text-slate-500 text-sm">版本</span>
                <span class="text-slate-700 text-sm ss-version">{{ $serverStatus->version }}</span>
            </div>
            @endif
            @if($serverStatus->motd)
            <div class="pt-3 border-t border-slate-100">
                <p class="text-slate-600 text-sm ss-motd">{{ $serverStatus->motd }}</p>
            </div>
            @endif
            @if($serverStatus->updated_at)
            <div class="text-xs text-slate-400 text-right ss-updated">
                更新于 {{ $serverStatus->updated_at->diffForHumans() }}
            </div>
            @endif
        @else
            <div class="text-center py-4">
                <span class="w-2.5 h-2.5 bg-amber-400 rounded-full mr-2 inline-block animate-pulse"></span>
                <span class="text-amber-600">服务器连接中...</span>
            </div>
        @endif
    </div>
</div>

<script>
(function() {
    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    async function refreshServerStatusImp(force) {
        const refreshIcon = document.getElementById('ssRefreshIcon');
        if (refreshIcon) refreshIcon.classList.add('animate-spin');
        try {
            let url = '{{ route('server-status') }}?json=1&_=' + Date.now();
            if (force) url += '&force=1';
            const res = await fetch(url, { credentials: 'same-origin', cache: 'no-store' });
            const data = await res.json();
            if (!data || !data.ok) return;

            const dot = document.getElementById('ssStatusDot');
            if (dot) {
                dot.className = 'w-2.5 h-2.5 rounded-full ' + (data.online ? 'bg-green-500' : 'bg-red-500') + ' animate-pulse';
            }
            const body = document.getElementById('ssBody');

            const playersHtml = data.players_json && data.players_json.length
                ? '<div class="flex flex-wrap gap-1.5">' + data.players_json.map(function(p) {
                    const name = (p.name || '未知').replace(/§./g, '');
                    const avatar = p.avatar || '';
                    return '<div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg pl-1 pr-2 py-1">' +
                        (avatar ? '<img src="' + escapeHtml(avatar) + '" alt="' + escapeHtml(name) + '" data-fallback="' + escapeHtml(p.avatar_fallback || '') + '" onerror="if(this.dataset.fallback){this.src=this.dataset.fallback;this.onerror=null}" class="w-6 h-6 rounded-md mr-1.5 object-cover bg-white flex-shrink-0" loading="lazy">' : '') +
                        '<span class="text-slate-700 text-sm">' + escapeHtml(name) + '</span></div>';
                }).join('') + '</div>'
                : (data.players_online > 0
                    ? '<p class="text-slate-400 text-sm text-center py-2">' + data.players_online + ' 名玩家在线（服务器未公开玩家列表）</p>'
                    : '<p class="text-slate-400 text-sm text-center py-2">暂无玩家在线</p>');

            const motdHtml = data.motd ? '<div class="pt-3 border-t border-slate-100"><p class="text-slate-600 text-sm">' + escapeHtml(data.motd) + '</p></div>' : '';
            const versionHtml = data.version ? '<div class="flex items-center justify-between"><span class="text-slate-500 text-sm">版本</span><span class="text-slate-700 text-sm">' + escapeHtml(data.version) + '</span></div>' : '';
            const updatedHtml = data.updated_at ? '<div class="text-xs text-slate-400 text-right">更新于 ' + escapeHtml(data.updated_at) + '</div>' : '';

            if (body) {
                body.innerHTML =
                    '<div class="flex items-center justify-between"><span class="text-slate-500 text-sm">状态</span>' +
                    '<span class="flex items-center"><span class="w-2.5 h-2.5 ' + (data.online ? 'bg-green-500 animate-pulse' : 'bg-red-500') + ' rounded-full mr-2"></span>' +
                    '<span class="text-' + (data.online ? 'green' : 'red') + '-600 font-semibold">' + (data.online ? '在线' : '离线') + '</span></span></div>' +
                    '<div class="flex items-center justify-between"><span class="text-slate-500 text-sm">玩家</span>' +
                    '<span class="text-slate-900 font-bold">' + data.players_online + ' / ' + data.players_max + '</span></div>' +
                    (data.online ? '<div class="pt-3 border-t border-slate-100"><div class="text-slate-500 text-xs mb-2">在线玩家</div>' + playersHtml + '</div>' : '') +
                    versionHtml + motdHtml + updatedHtml;
            }
        } finally {
            if (refreshIcon) setTimeout(function() { refreshIcon.classList.remove('animate-spin'); }, 400);
        }
    }

    window.refreshServerStatus = function(force) {
        refreshServerStatusImp(!!force);
    };

    // 页面加载后立即刷新一次（修复首次进入不刷新的 bug）
    window.refreshServerStatus(false);
    setInterval(function() { window.refreshServerStatus(false); }, 30000);
})();
</script>
