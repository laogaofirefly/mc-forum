@extends('layouts.app')

@section('title', '绑定 MC 账号')

@section('content')
<div class="max-w-md mx-auto">
    <div class="mc-card rounded-lg p-6">
        <h2 class="text-xl font-bold text-white mb-2">绑定 MC 账号</h2>
        <p class="text-gray-400 text-sm mb-6">绑定你的 Minecraft 账号，展示你的游戏头像</p>

        @if($user->mc_verified && $user->mc_username)
            <div class="text-center mb-6 p-4 bg-green-900/30 rounded-lg border border-green-600">
                <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->mc_username }}" class="w-20 h-20 mx-auto mb-3 rounded border-4 border-green-500">
                <p class="text-green-400 font-bold">已绑定: {{ $user->mc_username }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.mc-bind.update') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="mc_username" class="block text-sm font-medium text-gray-300 mb-1">MC 游戏名</label>
                    <input id="mc_username" type="text" name="mc_username" value="{{ old('mc_username', $user->mc_username) }}" required
                        class="mc-input w-full px-4 py-2 rounded-lg" placeholder="输入你的 Minecraft 游戏名">
                </div>
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('profile.show', $user) }}" class="text-gray-400 hover:text-gray-300 transition">
                        ← 返回
                    </a>
                    <button type="submit" class="mc-button text-white px-6 py-2 rounded-lg font-bold">
                        验证并绑定
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
