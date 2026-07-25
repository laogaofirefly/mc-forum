@extends('layouts.app')

@section('title', '注册')

@section('content')
<div class="max-w-md mx-auto">
    <div class="mc-card rounded-lg p-8">
        <h2 class="text-2xl font-bold text-center text-white mb-6">注册账号</h2>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">用户名</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">邮箱</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div>
                    <label for="mc_username" class="block text-sm font-medium text-gray-300 mb-1">MC 游戏名 (可选)</label>
                    <input id="mc_username" type="text" name="mc_username" value="{{ old('mc_username') }}"
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">密码</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">确认密码</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div class="pt-4">
                    <button type="submit" class="mc-button w-full text-white py-3 rounded-lg font-bold">
                        注册
                    </button>
                </div>
            </div>
        </form>
        <div class="mt-6 text-center text-gray-400 text-sm">
            已有账号？
            <a href="{{ route('login') }}" class="text-primary-400 hover:text-primary-300">立即登录</a>
        </div>
    </div>
</div>
@endsection
