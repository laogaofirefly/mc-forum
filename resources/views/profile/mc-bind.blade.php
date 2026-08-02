@extends('layouts.app')

@section('title', '绑定 MC 账号')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card p-6">
        <h2 class="text-xl font-bold text-slate-900 mb-2">绑定 MC 账号</h2>
        <p class="text-slate-500 text-sm mb-6">绑定你的 Minecraft 账号，展示你的游戏头像</p>

        @if($user->mc_verified && $user->mc_username)
            <div class="text-center mb-6 p-4 bg-primary-50 rounded-lg">
                <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->mc_username }}" class="w-20 h-20 mx-auto mb-3 rounded-full ring-2 ring-slate-100">
                <p class="text-primary-700 font-bold">已绑定: {{ $user->mc_username }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.mc-bind.update') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="mc_username" class="block text-sm font-medium text-slate-700 mb-1">MC 游戏名</label>
                    <input id="mc_username" type="text" name="mc_username" value="{{ old('mc_username', $user->mc_username) }}" required
                        class="input w-full" placeholder="输入你的 Minecraft 游戏名">
                </div>
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('profile.show', $user) }}" class="btn-secondary">
                        ← 返回
                    </a>
                    <button type="submit" class="btn-primary">
                        验证并绑定
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
