@extends('layouts.app')

@section('title', '登录')

@section('content')
<div class="max-w-md mx-auto">
    <div class="mc-card rounded-lg p-8">
        <h2 class="text-2xl font-bold text-center text-white mb-6">登录</h2>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">邮箱</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">密码</label>
                    <input id="password" type="password" name="password" required
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm text-gray-400">
                        <input type="checkbox" name="remember" class="mr-2 rounded">
                        记住我
                    </label>
                </div>
                <div class="pt-4">
                    <button type="submit" class="mc-button w-full text-white py-3 rounded-lg font-bold">
                        登录
                    </button>
                </div>
            </div>
        </form>
        <div class="mt-6 text-center text-gray-400 text-sm">
            还没有账号？
            <a href="{{ route('register') }}" class="text-primary-400 hover:text-primary-300">立即注册</a>
        </div>
    </div>
</div>
@endsection
