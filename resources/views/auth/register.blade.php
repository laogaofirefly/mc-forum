@extends('layouts.app')

@section('title', '注册')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
    <div class="card max-w-md w-full p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">创建账号</h2>
            <p class="text-slate-500 text-sm mt-1.5">加入 MC 玩家社区</p>
        </div>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                        用户名 <span class="text-red-500">*</span>
                        <span class="text-slate-400 font-normal ml-1">(2-30位，中文/字母/数字/下划线)</span>
                    </label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        autocomplete="username" inputmode="text" data-maxlength="30"
                        class="input w-full px-4 py-2.5 @error('name') input-error @enderror"
                        placeholder="请输入用户名">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                        邮箱 <span class="text-red-500">*</span>
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        autocomplete="email" inputmode="email"
                        class="input w-full px-4 py-2.5 @error('email') input-error @enderror"
                        placeholder="your@email.com">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mc_username" class="block text-sm font-medium text-slate-700 mb-1.5">
                        MC 游戏名
                        <span class="text-slate-400 font-normal ml-1">(可选，最多16位)</span>
                    </label>
                    <input id="mc_username" type="text" name="mc_username" value="{{ old('mc_username') }}"
                        autocomplete="off" inputmode="text" data-maxlength="16"
                        class="input w-full px-4 py-2.5 @error('mc_username') input-error @enderror"
                        placeholder="你的 Minecraft 游戏名">
                    @error('mc_username')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                        密码 <span class="text-red-500">*</span>
                        <span class="text-slate-400 font-normal ml-1">(至少8位)</span>
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            data-maxlength="100" data-counter-id="password-counter"
                            class="input w-full px-4 py-2.5 pr-11 @error('password') input-error @enderror"
                            placeholder="请输入密码">
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
                    <div id="password-counter" class="char-counter"></div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <div id="pwdStrength" class="mt-2 hidden">
                        <div class="flex items-center gap-2">
                            <div id="pwdStrengthBars" class="flex gap-1 flex-1">
                                <div class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></div>
                                <div class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></div>
                                <div class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></div>
                                <div class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></div>
                                <div class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors"></div>
                            </div>
                            <span id="pwdStrengthLabel" class="text-xs font-medium text-slate-500 whitespace-nowrap"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                        确认密码 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="input w-full px-4 py-2.5 pr-11"
                            placeholder="再次输入密码">
                        <div id="pwdMatchCheck" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div id="pwdMatchX" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>
                    <p id="pwdMatch" class="form-error hidden">两次输入的密码不一致</p>
                </div>

                <div class="pt-1">
                    <label class="flex items-start text-sm text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="agree" class="mt-1 mr-2 rounded w-4 h-4 flex-shrink-0 accent-primary-600" {{ old('agree') ? 'checked' : '' }}>
                        <span>我已阅读并同意
                            <a href="#" class="link-primary font-medium">用户协议</a>
                            和
                            <a href="#" class="link-primary font-medium">隐私政策</a>
                        </span>
                    </label>
                    @error('agree')
                        <p class="form-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full rounded-xl py-3 font-semibold text-sm">
                        注册账号
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-200 text-center text-slate-500 text-sm">
            已有账号？
            <a href="{{ route('login') }}" class="link-primary font-medium">立即登录</a>
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

    // 密码强度检测
    const pwdStrength = document.getElementById('pwdStrength');
    const pwdStrengthLabel = document.getElementById('pwdStrengthLabel');
    if (pwdInput && pwdStrength) {
        const bars = document.getElementById('pwdStrengthBars').querySelectorAll('div');
        pwdInput.addEventListener('input', function() {
            const v = this.value;
            if (!v) { pwdStrength.classList.add('hidden'); return; }
            pwdStrength.classList.remove('hidden');
            let score = 0;
            if (v.length >= 8) score++;
            if (v.length >= 12) score++;
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^a-zA-Z0-9]/.test(v)) score++;
            const levels = [
                { text: '弱', color: '#ef4444' },
                { text: '较弱', color: '#f59e0b' },
                { text: '一般', color: '#eab308' },
                { text: '较强', color: '#84cc16' },
                { text: '很强', color: '#22c55e' },
            ];
            const idx = Math.min(score, 4);
            const lvl = levels[idx];
            pwdStrengthLabel.textContent = '密码强度：' + lvl.text;
            pwdStrengthLabel.style.color = lvl.color;
            bars.forEach(function(bar, i) {
                bar.style.backgroundColor = i <= idx ? lvl.color : '';
            });
        });
    }

    // 两次密码一致性实时校验
    const pwdConfirm = document.getElementById('password_confirmation');
    const pwdMatch = document.getElementById('pwdMatch');
    const pwdMatchCheck = document.getElementById('pwdMatchCheck');
    const pwdMatchX = document.getElementById('pwdMatchX');
    function checkMatch() {
        if (!pwdConfirm.value) {
            pwdMatch.classList.add('hidden');
            pwdConfirm.classList.remove('input-error');
            pwdMatchCheck.classList.add('hidden');
            pwdMatchX.classList.add('hidden');
            return;
        }
        if (pwdInput.value !== pwdConfirm.value) {
            pwdMatch.classList.remove('hidden');
            pwdConfirm.classList.add('input-error');
            pwdMatchCheck.classList.add('hidden');
            pwdMatchX.classList.remove('hidden');
        } else {
            pwdMatch.classList.add('hidden');
            pwdConfirm.classList.remove('input-error');
            pwdMatchCheck.classList.remove('hidden');
            pwdMatchX.classList.add('hidden');
        }
    }
    if (pwdConfirm) {
        pwdConfirm.addEventListener('input', checkMatch);
        pwdInput.addEventListener('input', function() { if (pwdConfirm.value) checkMatch(); });
    }
</script>
@endsection