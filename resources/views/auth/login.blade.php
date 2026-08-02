@extends('layouts.app')

@section('title', '登录')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
    <div class="card max-w-md w-full p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">欢迎回来</h2>
            <p class="text-slate-500 text-sm mt-1.5">登录你的 MC 论坛账号</p>
        </div>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="login" class="block text-sm font-medium text-slate-700 mb-1.5">用户名或邮箱</label>
                    <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
                        autocomplete="username" inputmode="text"
                        class="input w-full px-4 py-2.5 @error('login') input-error @enderror"
                        placeholder="输入用户名或邮箱">
                    @error('login')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">密码</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required
                            autocomplete="current-password"
                            class="input w-full px-4 py-2.5 pr-11 @error('password') input-error @enderror"
                            placeholder="输入密码">
                        <button type="button" id="togglePwd" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition p-1" aria-label="显示密码">
                            <svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.878l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="mr-2 rounded w-4 h-4 accent-primary-600" {{ old('remember') ? 'checked' : '' }}>
                        记住我（7天）
                    </label>
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full rounded-xl py-3 font-semibold text-base">
                        登录
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-200 text-center text-slate-500 text-sm">
            还没有账号？
            <a href="{{ route('register') }}" class="link-primary font-medium">立即注册</a>
        </div>
    </div>
</div>

<script>
    // 密码显示/隐藏
    const togglePwd = document.getElementById('togglePwd');
    const pwdInput = document.getElementById('password');
    if (togglePwd && pwdInput) {
        togglePwd.addEventListener('click', function() {
            const isPwd = pwdInput.type === 'password';
            pwdInput.type = isPwd ? 'text' : 'password';
            document.getElementById('eyeOpen').classList.toggle('hidden', isPwd);
            document.getElementById('eyeClosed').classList.toggle('hidden', !isPwd);
            togglePwd.setAttribute('aria-label', isPwd ? '隐藏密码' : '显示密码');
        });
    }
</script>
@endsection
