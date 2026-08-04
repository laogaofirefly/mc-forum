@extends('layouts.app')

@section('title', '服务器成员')

@section('content')
<div class="space-y-5">
    {{-- 顶部标题与统计 --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="page-title text-slate-900 flex items-center">
                @include('layouts.partials.icons', ['name' => 'users', 'class' => 'w-6 h-6 mr-2 flex-shrink-0'])服务器成员
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">登录过 MC 服务器的所有玩家</p>
        </div>

        @if($ok)
            <div class="flex items-center gap-2 flex-wrap">
                <span class="badge bg-green-100 text-green-700">
                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-1"></span>在线 {{ $onlineCount }} / {{ $maxPlayers }}
                </span>
                <span class="badge bg-slate-100 text-slate-600">
                    总成员 {{ $total }}
                </span>
            </div>
        @endif
    </div>

    @if(! $ok)
        {{-- 错误提示 --}}
        <div class="card p-6 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="font-medium text-slate-700">无法读取成员列表</p>
            <p class="mt-1 text-xs text-slate-500">{{ $message }}</p>
            <p class="mt-3 text-xs text-slate-400 leading-relaxed">
                请在网站根目录的 <code class="bg-slate-100 px-1 py-0.5 rounded">.env</code> 文件中配置：<br>
                <code class="bg-slate-100 px-1 py-0.5 rounded">MC_SERVER_PATH=/你的/MC/服务器/根目录</code>
            </p>
        </div>
    @else
        {{-- 搜索框 --}}
        <form method="GET" action="{{ route('players.index') }}" class="flex gap-2">
            <input type="text" name="q" value="{{ $keyword }}" placeholder="搜索玩家名..."
                class="input flex-1">
            <button type="submit" class="btn-primary px-5 rounded-lg text-sm">
                @include('layouts.partials.icons', ['name' => 'search', 'class' => 'w-5 h-5'])
            </button>
            @if($keyword !== '')
                <a href="{{ route('players.index') }}" class="btn-secondary px-4 rounded-lg text-sm">清除</a>
            @endif
        </form>

        {{-- 成员列表 --}}
        @if(count($players) > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                @foreach($players as $player)
                    <div class="card p-4 text-center card-hover {{ $player['online'] ? 'ring-2 ring-green-200' : '' }}">
                        <div class="relative inline-block">
                            @if($player['bound'])
                                <a href="{{ route('profile.show', $player['bound_user_id']) }}" title="查看网站主页：{{ $player['bound_user_name'] }}">
                                    <img src="{{ $player['avatar'] }}" alt="{{ $player['name'] }}"
                                        class="w-16 h-16 rounded-full mx-auto bg-slate-100 object-cover ring-2 ring-primary-200"
                                        loading="lazy">
                                </a>
                            @else
                                <img src="{{ $player['avatar'] }}" alt="{{ $player['name'] }}"
                                    class="w-16 h-16 rounded-full mx-auto bg-slate-100 object-cover ring-2 ring-slate-100"
                                    loading="lazy">
                            @endif
                            @if($player['online'])
                                <span class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 border-2 border-white rounded-full" title="在线"></span>
                            @endif
                        </div>
                        <div class="mt-2 font-medium text-slate-900 text-sm truncate" title="{{ $player['name'] }}">
                            @if($player['bound'])
                                <a href="{{ route('profile.show', $player['bound_user_id']) }}" class="hover:text-primary-600 hover:underline">
                                    {{ $player['name'] }}
                                </a>
                            @else
                                {{ $player['name'] }}
                            @endif
                        </div>
                        <div class="mt-0.5 flex items-center justify-center gap-1 flex-wrap">
                            @if($player['bound'])
                                <span class="badge bg-primary-50 text-primary-600 text-[10px] px-1.5 py-0.5" title="已绑定网站账号：{{ $player['bound_user_name'] }}">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    已绑定
                                </span>
                            @endif
                            <span class="text-xs {{ $player['online'] ? 'text-green-600' : 'text-slate-400' }}">
                                @if($player['online'])
                                    在线
                                @else
                                    离线
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($keyword !== '')
                <div class="text-center text-xs text-slate-400 py-2">
                    搜索到 {{ count($players) }} 个匹配的玩家
                </div>
            @endif
        @else
            <div class="card p-8 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="font-medium text-slate-500">
                    @if($keyword !== '')
                        没有匹配「{{ $keyword }}」的玩家
                    @else
                        暂无成员数据
                    @endif
                </p>
            </div>
        @endif
    @endif
</div>
@endsection
