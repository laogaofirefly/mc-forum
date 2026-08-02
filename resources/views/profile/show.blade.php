@extends('layouts.app')

@section('title', $user->name . ' 的资料')

@section('content')
<div class="space-y-5">
    {{-- 个人信息卡片 --}}
    <div class="card overflow-hidden">
        {{-- 顶部横幅 --}}
        <div style="height: 96px; background: linear-gradient(135deg, #34d399 0%, #059669 100%);"></div>

        <div class="px-5 sm:px-8 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:space-x-6 -mt-12 sm:-mt-14">
                {{-- 头像区域 --}}
                <div class="flex flex-col items-center sm:items-start">
                    <div class="relative">
                        <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}"
                            class="w-24 h-24 sm:w-28 sm:h-28 rounded-full ring-4 ring-white shadow-lg bg-white object-cover">
                        @auth
                            @if(auth()->id() === $user->id)
                                <button type="button" id="avatarBtn"
                                    class="absolute bottom-1 right-1 w-8 h-8 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-md transition"
                                    title="更换头像">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                                <form id="avatarForm" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="hidden">
                                    @csrf
                                    <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>

                {{-- 用户信息 --}}
                <div class="flex-1 mt-3 sm:mt-0 sm:pb-2 text-center sm:text-left">
                    <div class="flex items-center justify-center sm:justify-start space-x-2 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
                        @if($user->isAdmin())
                            <span class="badge bg-amber-100 text-amber-800">管理员</span>
                        @endif
                        @if($user->mc_verified)
                            <span class="badge bg-primary-100 text-primary-700 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                MC已验证
                            </span>
                        @endif
                    </div>
                    @if($user->mc_username)
                        <p class="text-primary-600 text-sm mt-1">🎮 MC ID: {{ $user->mc_username }}</p>
                    @endif
                </div>
            </div>

            {{-- 个人简介 --}}
            <div class="mt-4">
                @if($user->bio)
                    <p class="text-slate-700 leading-relaxed">{{ $user->bio }}</p>
                @else
                    <p class="text-slate-400 italic">这个人很懒，什么都没留下...</p>
                @endif
            </div>

            {{-- 统计数据 --}}
            <div class="grid grid-cols-3 gap-3 mt-5">
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <div class="text-xl font-bold text-primary-600">{{ $user->threads()->count() }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">帖子</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <div class="text-xl font-bold text-primary-600">{{ $user->replies()->count() }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">回复</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <div class="text-xl font-bold text-primary-600">{{ $user->created_at->format('Y-m-d') }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">注册于</div>
                </div>
            </div>

            {{-- 操作按钮 --}}
            @auth
                @if(auth()->id() === $user->id)
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn-secondary text-sm">
                            ✏️ 编辑资料
                        </a>
                        <a href="{{ route('profile.mc-bind') }}" class="btn-primary text-sm">
                            🎮 绑定 MC 账号
                        </a>
                    </div>
                @endif
            @endauth
        </div>
    </div>

    {{-- 最近帖子 --}}
    <div>
        <h2 class="text-base font-bold text-slate-900 mb-3 flex items-center">
            <span class="mr-2">📝</span>最近发布的帖子
        </h2>
        <div class="space-y-3">
            @foreach($threads as $thread)
                @include('partials.thread-card', ['thread' => $thread])
            @endforeach
            @if($threads->isEmpty())
                <div class="card p-6 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm">暂无帖子</p>
                </div>
            @endif
        </div>
    </div>

</div>

@auth
    @if(auth()->id() === $user->id)
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
                // 简单校验
                if (file.size > 2 * 1024 * 1024) {
                    alert('图片大小不能超过 2MB');
                    this.value = '';
                    return;
                }
                // 提交表单
                form.submit();
            });
        })();
        </script>
    @endif
@endauth
@endsection
