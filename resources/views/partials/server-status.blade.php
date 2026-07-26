<div class="mc-card rounded-lg p-4">
    <h3 class="text-lg font-bold text-primary-400 mb-3 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/>
        </svg>
        服务器状态
    </h3>
    @if(isset($serverStatus) && $serverStatus)
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-gray-400">状态</span>
                <span class="flex items-center">
                    @if($serverStatus->is_online)
                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        <span class="text-green-400 font-bold">在线</span>
                    @else
                        <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                        <span class="text-red-400 font-bold">离线</span>
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-400">玩家</span>
                <span class="text-white font-bold">{{ $serverStatus->players_online }} / {{ $serverStatus->players_max }}</span>
            </div>
            @if($serverStatus->is_online)
            <div class="pt-2 border-t border-gray-700">
                @if(!empty($serverStatus->players_json))
                    <div class="text-gray-400 text-sm mb-2">在线玩家</div>
                    <div class="flex flex-wrap gap-2">
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
                    <p class="text-gray-500 text-sm text-center py-2">{{ $serverStatus->players_online }} 名玩家在线（服务器未公开玩家列表）</p>
                @else
                    <p class="text-gray-500 text-sm text-center py-2">暂无玩家在线</p>
                @endif
            </div>
            @endif
            @if($serverStatus->version)
            <div class="flex items-center justify-between">
                <span class="text-gray-400">版本</span>
                <span class="text-white text-sm">{{ $serverStatus->version }}</span>
            </div>
            @endif
            @if($serverStatus->motd)
            <div class="pt-2 border-t border-gray-700">
                <p class="text-gray-300 text-sm">{{ $serverStatus->motd }}</p>
            </div>
            @endif
            @if($serverStatus->updated_at)
            <div class="text-xs text-gray-500 text-right">
                更新于 {{ $serverStatus->updated_at->diffForHumans() }}
            </div>
            @endif
        </div>
    @else
        <div class="text-center py-4">
            <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2 inline-block"></span>
            <span class="text-yellow-400">服务器连接中...</span>
        </div>
    @endif
</div>
