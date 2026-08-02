@extends('layouts.app')

@section('title', '用户详情 - ' . $user->name)

@section('content')
<div class="space-y-5">
    <div class="text-sm text-slate-500">
        <a href="{{ route('home') }}" class="text-primary-600 hover:text-primary-700">首页</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.users.index') }}" class="text-primary-600 hover:text-primary-700">用户管理</a>
        <span class="mx-2">/</span>
        <span class="text-slate-500">{{ $user->name }}</span>
    </div>

    {{-- 用户信息卡片 --}}
    <div class="card p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row items-start gap-4">
            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full ring-4 ring-slate-100 bg-white flex-shrink-0">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
                    @if($user->is_admin)
                        <span class="badge bg-amber-100 text-amber-700">管理员</span>
                    @endif
                    @if($user->is_blocked)
                        <span class="badge bg-red-100 text-red-700">已封禁</span>
                    @else
                        <span class="badge bg-emerald-100 text-emerald-700">正常</span>
                    @endif
                </div>
                <div class="text-sm text-slate-500 mt-1">ID: {{ $user->id }}</div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 text-sm">
                    <div>
                        <div class="text-xs text-slate-400">邮箱</div>
                        <div class="text-slate-700 truncate">{{ $user->email }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">注册时间</div>
                        <div class="text-slate-700">{{ $user->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">最后登录</div>
                        <div class="text-slate-700">{{ $user->updated_at->format('Y-m-d H:i') }}</div>
                    </div>
                    @if($user->mc_username)
                        <div>
                            <div class="text-xs text-slate-400">MC 账号</div>
                            <div class="text-slate-700">
                                {{ $user->mc_username }}
                                @if($user->mc_verified)<span class="text-primary-500 text-xs">✓已验证</span>@endif
                            </div>
                        </div>
                    @endif
                    @if($user->bio)
                        <div class="col-span-2 sm:col-span-3">
                            <div class="text-xs text-slate-400">个人简介</div>
                            <div class="text-slate-700">{{ $user->bio }}</div>
                        </div>
                    @endif
                    @if($user->is_blocked)
                        <div class="col-span-2 sm:col-span-3">
                            <div class="text-xs text-slate-400">封禁信息</div>
                            <div class="text-red-600">
                                {{ $user->blocked_at?->format('Y-m-d H:i') }}
                                @if($user->block_reason) · {{ $user->block_reason }} @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-2 w-full sm:w-auto">
                @if($user->is_blocked)
                    <form method="POST" action="{{ route('admin.users.unblock', $user) }}" onsubmit="return confirm('确定解封？');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-primary w-full px-4 py-2 text-sm">解封用户</button>
                    </form>
                @elseif(!$user->is_admin && $user->id !== auth()->id())
                    <button type="button" onclick="openBlockModal('{{ e($user->name) }}', '{{ route('admin.users.block', $user) }}')" class="btn-danger w-full px-4 py-2 text-sm">封禁用户</button>
                @endif
                <a href="{{ route('profile.show', $user) }}" class="btn-secondary w-full px-4 py-2 text-sm text-center">查看主页</a>
            </div>
        </div>
    </div>

    {{-- 活动统计 --}}
    <div class="grid grid-cols-3 gap-2.5 sm:gap-3">
        <div class="card p-3 sm:p-4 text-center">
            <div class="text-xs sm:text-sm text-slate-500">发帖数</div>
            <div class="text-xl sm:text-2xl font-bold text-primary-600">{{ $user->threads_count }}</div>
        </div>
        <div class="card p-3 sm:p-4 text-center">
            <div class="text-xs sm:text-sm text-slate-500">回复数</div>
            <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $user->replies_count }}</div>
        </div>
        <div class="card p-3 sm:p-4 text-center">
            <div class="text-xs sm:text-sm text-slate-500">点赞数</div>
            <div class="text-xl sm:text-2xl font-bold text-amber-600">{{ $user->likes_count }}</div>
        </div>
    </div>

    {{-- 最近发帖 --}}
    <div class="card p-4 sm:p-5">
        <h3 class="font-bold text-slate-900 mb-3">最近发帖</h3>
        @forelse($recentThreads as $thread)
            <a href="{{ route('threads.show', $thread->slug) }}" class="block py-2 border-b border-slate-100 last:border-0 hover:bg-slate-50 -mx-2 px-2 rounded">
                <div class="text-sm text-slate-700 truncate">{{ $thread->title }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ $thread->created_at->diffForHumans() }}</div>
            </a>
        @empty
            <p class="text-sm text-slate-400 py-2">暂无发帖</p>
        @endforelse
    </div>

    {{-- 最近回复 --}}
    <div class="card p-4 sm:p-5">
        <h3 class="font-bold text-slate-900 mb-3">最近回复</h3>
        @forelse($recentReplies as $reply)
            <div class="py-2 border-b border-slate-100 last:border-0">
                <div class="text-xs text-slate-400 mb-0.5">
                    回复了
                    <a href="{{ route('threads.show', $reply->thread->slug) }}" class="text-primary-600 hover:underline">{{ $reply->thread->title }}</a>
                    · {{ $reply->created_at->diffForHumans() }}
                </div>
                <div class="text-sm text-slate-600 line-clamp-2">{{ $reply->body }}</div>
            </div>
        @empty
            <p class="text-sm text-slate-400 py-2">暂无回复</p>
        @endforelse
    </div>
</div>

{{-- 封禁弹窗 --}}
<div id="blockModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl p-5 max-w-md w-full">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-900">封禁用户</h3>
            <button type="button" onclick="closeBlockModal()" class="text-slate-400 hover:text-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-sm text-slate-600 mb-3">确定要封禁 <b id="blockUserName" class="text-slate-900"></b> 吗？</p>
        <form id="blockForm" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">封禁原因（可选）</label>
                <input type="text" name="reason" maxlength="200" class="input w-full px-3 py-2 text-sm" placeholder="如：发布违规内容...">
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="closeBlockModal()" class="btn-secondary flex-1 py-2 text-sm">取消</button>
                <button type="submit" class="btn-danger flex-1 py-2 text-sm">确认封禁</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBlockModal(userName, actionUrl) {
        document.getElementById('blockUserName').textContent = userName;
        document.getElementById('blockForm').action = actionUrl;
        var modal = document.getElementById('blockModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeBlockModal() {
        var modal = document.getElementById('blockModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
