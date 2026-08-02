@extends('layouts.app')

@section('title', '编辑资料')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card p-6">
        <h2 class="text-xl font-bold text-slate-900 mb-6">编辑个人资料</h2>

        {{-- 头像区域 --}}
        <div class="flex items-center space-x-4 mb-6 pb-6 border-b border-slate-100">
            <img src="{{ auth()->user()->getAvatarUrl() }}" alt="当前头像"
                class="w-20 h-20 rounded-full ring-2 ring-slate-100 object-cover bg-white">
            <div>
                <p class="text-sm font-medium text-slate-700 mb-1">头像</p>
                <p class="text-xs text-slate-500 mb-2">支持 JPG/PNG/WEBP/GIF，最大 2MB</p>
                <form id="avatarForm" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                    <button type="button" id="avatarBtn" class="btn-secondary text-sm">
                        📷 更换头像
                    </button>
                </form>
            </div>
        </div>

        
        {{-- 聊天背景图区域 --}}
        <div class="flex items-center space-x-4 mb-6 pb-6 border-b border-slate-100">
            <div class="w-40 h-24 rounded-lg bg-cover bg-center shadow-inner ring-1 ring-slate-200 overflow-hidden flex-shrink-0" style="{{ auth()->user()->chat_bg ? 'background-image: url('' . e(auth()->user()->getChatBgUrl()) . '')' : 'background: linear-gradient(135deg, #e2e8f0, #cbd5e1)' }}"></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-700 mb-1">🎨 游戏聊天背景图</p>
                <p class="text-xs text-slate-500 mb-2">支持 JPG/PNG/WEBP，最大 5MB。不设置则使用默认背景。</p>
                <div class="flex items-center gap-2 flex-wrap">
                    <form id="chatBgForm" action="{{ route('profile.chat-bg') }}" method="POST" enctype="multipart/form-data" class="contents">
                        @csrf
                        <input type="file" id="chatBgInput" name="chat_bg" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="document.getElementById('chatBgForm').submit()">
                        <button type="button" id="chatBgBtn" class="btn-secondary text-sm" onclick="document.getElementById('chatBgInput').click()">
                            🖼 选择背景图
                        </button>
                    </form>
                    @if(auth()->user()->chat_bg)
                    <form action="{{ route('profile.chat-bg.remove') }}" method="POST" class="contents" onsubmit="return confirm('确定要移除聊天背景图？')">
                        @csrf
                        <button type="submit" class="btn-secondary text-sm text-red-600 border-red-200 hover:bg-red-50">
                            ❌ 移除
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

<form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">用户名</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="input w-full">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">邮箱</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="input w-full">
                </div>
                <div>
                    <label for="bio" class="block text-sm font-medium text-slate-700 mb-1">个人简介</label>
                    <textarea id="bio" name="bio" rows="4"
                        class="input w-full" placeholder="介绍一下自己...">{{ old('bio', $user->bio) }}</textarea>
                    <p class="char-counter" id="bioCounter"></p>
                </div>
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('profile.show', $user) }}" class="btn-secondary">
                        ← 返回
                    </a>
                    <button type="submit" class="btn-primary">
                        保存修改
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var btn = document.getElementById('avatarBtn');
    var input = document.getElementById('avatarInput');
    var form = document.getElementById('avatarForm');
    if (!btn || !input || !form) return;

    btn.addEventListener('click', function(e) {
        e.preventDefault();
        input.click();
    });

    input.addEventListener('change', function() {
        if (!this.files || !this.files[0]) return;
        var file = this.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('图片大小不能超过 2MB');
            this.value = '';
            return;
        }
        form.submit();
    });

    // 简介字数统计
    var bio = document.getElementById('bio');
    var counter = document.getElementById('bioCounter');
    if (bio && counter) {
        function update() {
            var len = bio.value.length;
            counter.textContent = len + ' / 500';
            counter.className = 'char-counter' + (len > 500 ? ' error' : len > 400 ? ' warning' : '');
        }
        bio.addEventListener('input', update);
        update();
    }
})();
</script>
@endsection
