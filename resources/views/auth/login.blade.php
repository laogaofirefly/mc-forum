@extends('layouts.app')

@section('title', '登录')

@section('content')
<div class="max-w-md mx-auto">
    <div class="mc-card rounded-lg p-5 sm:p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-primary-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white">欢迎回来</h2>
            <p class="text-gray-400 text-sm mt-1">登录你的 MC 论坛账号</p>
        </div>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-300 mb-1">用户名或邮箱</label>
                    <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
                        autocomplete="username" inputmode="text"
                        class="mc-input w-full px-4 py-2 rounded-lg @error('login') input-error @enderror"
                        placeholder="输入用户名或邮箱">
                    @error('login')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-300">密码</label>
                        <button type="button" id="togglePwd" class="text-xs text-primary-400 hover:text-primary-300">
                            显示密码
                        </button>
                    </div>
                    <input id="password" type="password" name="password" required
                        autocomplete="current-password"
                        class="mc-input w-full px-4 py-2 rounded-lg @error('password') input-error @enderror"
                        placeholder="输入密码">
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm text-gray-400 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="mr-2 rounded w-4 h-4" {{ old('remember') ? 'checked' : '' }}>
                        记住我（7天）
                    </label>
                </div>
                <div class="pt-2">
                    <button type="submit" class="mc-button w-full text-white py-3 rounded-lg font-bold text-base">
                        登录
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-700 text-center text-gray-400 text-sm">
            还没有账号？
            <a href="{{ route('register') }}" class="text-primary-400 hover:text-primary-300 font-medium">立即注册</a>
        </div>
    </div>
</div>

<script>
    // 密码显示/隐藏
    const togglePwd = document.getElementById('togglePwd');
    const pwdInput = document.getElementById('password');
    if (togglePwd && pwdInput) {
        togglePwd.addEventListener('click', function() {
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                togglePwd.textContent = '隐藏密码';
            } else {
                pwdInput.type = 'password';
                togglePwd.textContent = '显示密码';
            }
        });
    }
</script>
@endsection
