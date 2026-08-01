@extends('layouts.app')

@section('title', '注册')

@section('content')
<div class="max-w-md mx-auto">
    <div class="mc-card rounded-lg p-5 sm:p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-primary-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white">创建账号</h2>
            <p class="text-gray-400 text-sm mt-1">加入 MC 玩家社区</p>
        </div>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">
                        用户名 <span class="text-red-400">*</span>
                        <span class="text-gray-500 font-normal ml-1">(2-30位，中文/字母/数字/下划线)</span>
                    </label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        autocomplete="username" inputmode="text" data-maxlength="30"
                        class="mc-input w-full px-4 py-2 rounded-lg @error('name') input-error @enderror"
                        placeholder="请输入用户名">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">
                        邮箱 <span class="text-red-400">*</span>
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        autocomplete="email" inputmode="email"
                        class="mc-input w-full px-4 py-2 rounded-lg @error('email') input-error @enderror"
                        placeholder="your@email.com">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mc_username" class="block text-sm font-medium text-gray-300 mb-1">
                        MC 游戏名
                        <span class="text-gray-500 font-normal ml-1">(可选，最多16位)</span>
                    </label>
                    <input id="mc_username" type="text" name="mc_username" value="{{ old('mc_username') }}"
                        autocomplete="off" inputmode="text" data-maxlength="16"
                        class="mc-input w-full px-4 py-2 rounded-lg @error('mc_username') input-error @enderror"
                        placeholder="你的 Minecraft 游戏名">
                    @error('mc_username')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-300">
                            密码 <span class="text-red-400">*</span>
                            <span class="text-gray-500 font-normal ml-1">(至少8位)</span>
                        </label>
                        <button type="button" id="togglePwd" class="text-xs text-primary-400 hover:text-primary-300">
                            显示
                        </button>
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        data-maxlength="100"
                        class="mc-input w-full px-4 py-2 rounded-lg @error('password') input-error @enderror"
                        placeholder="请输入密码">
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <div id="pwdStrength" class="mt-2 text-xs"></div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">
                        确认密码 <span class="text-red-400">*</span>
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="mc-input w-full px-4 py-2 rounded-lg"
                        placeholder="再次输入密码">
                    <p id="pwdMatch" class="form-error hidden">两次输入的密码不一致</p>
                </div>

                <div class="pt-1">
                    <label class="flex items-start text-sm text-gray-400 cursor-pointer select-none">
                        <input type="checkbox" name="agree" class="mt-1 mr-2 rounded w-4 h-4 flex-shrink-0" {{ old('agree') ? 'checked' : '' }}>
                        <span>我已阅读并同意
                            <a href="#" class="text-primary-400 hover:text-primary-300">用户协议</a>
                            和
                            <a href="#" class="text-primary-400 hover:text-primary-300">隐私政策</a>
                        </span>
                    </label>
                    @error('agree')
                        <p class="form-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="mc-button w-full text-white py-3 rounded-lg font-bold text-base">
                        注册账号
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-700 text-center text-gray-400 text-sm">
            已有账号？
            <a href="{{ route('login') }}" class="text-primary-400 hover:text-primary-300 font-medium">立即登录</a>
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
                togglePwd.textContent = '隐藏';
            } else {
                pwdInput.type = 'password';
                togglePwd.textContent = '显示';
            }
        });
    }

    // 密码强度检测
    const pwdStrength = document.getElementById('pwdStrength');
    if (pwdInput && pwdStrength) {
        pwdInput.addEventListener('input', function() {
            const v = this.value;
            if (!v) { pwdStrength.textContent = ''; return; }
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
                { text: '较强', color: '#22c55e' },
                { text: '很强', color: '#16a34a' },
            ];
            const lvl = levels[Math.min(score, 4)];
            pwdStrength.textContent = '密码强度：' + lvl.text;
            pwdStrength.style.color = lvl.color;
        });
    }

    // 两次密码一致性实时校验
    const pwdConfirm = document.getElementById('password_confirmation');
    const pwdMatch = document.getElementById('pwdMatch');
    function checkMatch() {
        if (!pwdConfirm.value) { pwdMatch.classList.add('hidden'); return; }
        if (pwdInput.value !== pwdConfirm.value) {
            pwdMatch.classList.remove('hidden');
            pwdConfirm.classList.add('input-error');
        } else {
            pwdMatch.classList.add('hidden');
            pwdConfirm.classList.remove('input-error');
        }
    }
    if (pwdConfirm) {
        pwdConfirm.addEventListener('input', checkMatch);
        pwdInput.addEventListener('input', function() { if (pwdConfirm.value) checkMatch(); });
    }
</script>
@endsection
