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
