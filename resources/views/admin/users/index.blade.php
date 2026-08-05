@extends('layouts.app')

@section('title', '用户管理')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="page-title text-slate-900 flex items-center">
                @include('layouts.partials.icons', ['name' => 'users', 'class' => 'w-6 h-6 mr-2 flex-shrink-0'])用户管理
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">监控所有注册用户 · 封禁/解封</p>
        </div>
        <a href="{{ route('admin.console') }}" class="btn-secondary text-sm px-4 py-2">
            @include('layouts.partials.icons', ['name' => 'terminal', 'class' => 'w-4 h-4'])服务器控制台
        </a>
    </div>

    {{-- 统计卡片 --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 sm:gap-3">
        <a href="{{ route('admin.users.index') }}" class="card p-3 sm:p-4 card-hover {{ $filter === 'all' ? 'ring-2 ring-primary-400' : '' }}">
            <div class="text-xs sm:text-sm text-slate-500">总用户</div>
            <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-400 mt-1">今日 +{{ $stats['today'] }}</div>
        </a>
        <a href="{{ route('admin.users.index', ['filter' => 'blocked']) }}{{ $search ? '&q=' . urlencode($search) : '' }}" class="card p-3 sm:p-4 card-hover {{ $filter === 'blocked' ? 'ring-2 ring-red-400' : '' }}">
            <div class="text-xs sm:text-sm text-slate-500">已封禁</div>
            <div class="text-xl sm:text-2xl font-bold text-red-600">{{ $stats['blocked'] }}</div>
            <div class="text-xs text-slate-400 mt-1">点击查看</div>
        </a>
        <a href="{{ route('admin.users.index', ['filter' => 'admin']) }}{{ $search ? '&q=' . urlencode($search) : '' }}" class="card p-3 sm:p-4 card-hover {{ $filter === 'admin' ? 'ring-2 ring-amber-400' : '' }}">
            <div class="text-xs sm:text-sm text-slate-500">管理员</div>
            <div class="text-xl sm:text-2xl font-bold text-amber-600">{{ $stats['admins'] }}</div>
            <div class="text-xs text-slate-400 mt-1">点击查看</div>
        </a>
        <a href="{{ route('admin.users.index', ['filter' => 'mc_bound']) }}{{ $search ? '&q=' . urlencode($search) : '' }}" class="card p-3 sm:p-4 card-hover {{ $filter === 'mc_bound' ? 'ring-2 ring-primary-400' : '' }}">
            <div class="text-xs sm:text-sm text-slate-500">已绑 MC</div>
            <div class="text-xl sm:text-2xl font-bold text-primary-600">{{ $stats['mc_bound'] }}</div>
            <div class="text-xs text-slate-400 mt-1">点击查看</div>
        </a>
        <div class="card p-3 sm:p-4">
            <div class="text-xs sm:text-sm text-slate-500">今日新增</div>
            <div class="text-xl sm:text-2xl font-bold text-emerald-600">{{ $stats['today'] }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ now()->format('m-d') }}</div>
        </div>
    </div>

    {{-- 搜索 --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
        <input type="text" name="q" value="{{ $search }}" placeholder="搜索用户名 / 邮箱 / MC昵称..."
            class="input flex-1 px-4 py-2 text-sm">
        @if($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
        <button type="submit" class="btn-primary px-5 py-2 text-sm whitespace-nowrap">@include('layouts.partials.icons', ['name' => 'search', 'class' => 'w-5 h-5'])</button>
        @if($search || $filter !== 'all')
            <a href="{{ route('admin.users.index') }}" class="btn-secondary px-4 py-2 text-sm">清除</a>
        @endif
    </form>

    {{-- 用户列表 --}}
    <div class="card overflow-hidden">
        {{-- 桌面端表格 --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium">用户</th>
                        <th class="text-left px-4 py-3 font-medium">邮箱</th>
                        <th class="text-left px-4 py-3 font-medium">MC账号</th>
                        <th class="text-center px-4 py-3 font-medium">帖子</th>
                        <th class="text-center px-4 py-3 font-medium">回复</th>
                        <th class="text-left px-4 py-3 font-medium">注册时间</th>
                        <th class="text-center px-4 py-3 font-medium">状态</th>
                        <th class="text-center px-4 py-3 font-medium">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 {{ $user->is_blocked ? 'bg-red-50/40' : '' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $user->getAvatarUrl() }}" alt="" class="w-9 h-9 rounded-full ring-2 ring-slate-100 bg-white">
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-slate-900 hover:text-primary-600 block truncate">{{ $user->name }}</a>
                                        <span class="text-xs text-slate-400">ID: {{ $user->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @if($user->mc_username)
                                    <span class="text-slate-700">{{ $user->mc_username }}</span>
                                    @if($user->mc_verified)
                                        @include('layouts.partials.icons', ['name' => 'check', 'class' => 'w-3 h-3 inline text-primary-500'])
                                    @endif
                                @else
                                    <span class="text-slate-300">未绑定</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $user->threads_count }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $user->replies_count }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($user->is_admin)
                                    <span class="badge bg-amber-100 text-amber-700">管理员</span>
                                @elseif($user->is_blocked)
                                    <span class="badge bg-red-100 text-red-700">已封禁</span>
                                @else
                                    <span class="badge bg-emerald-100 text-emerald-700">正常</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-slate-400 hover:text-primary-600 text-xs px-2 py-1 rounded hover:bg-slate-100" title="详情">详情</a>
                                    @if($user->is_blocked)
                                        <form method="POST" action="{{ route('admin.users.unblock', $user) }}" onsubmit="return confirm('确定解封 {{ $user->name }} 吗？');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-700 text-xs px-2 py-1 rounded hover:bg-emerald-50">解封</button>
                                        </form>
                                    @elseif(!$user->is_admin && $user->id !== auth()->id())
                                        <button type="button" onclick="openBlockModal({{ $user->id }}, '{{ e($user->name) }}')" class="text-red-500 hover:text-red-600 text-xs px-2 py-1 rounded hover:bg-red-50">封禁</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                <p class="text-base mb-1">未找到匹配的用户</p>
                                <p class="text-xs">@if($search) 搜索 "{{ $search }}" 无结果 @else 当前筛选条件下无用户 @endif</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 移动端卡片 --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($users as $user)
                <div class="p-4 {{ $user->is_blocked ? 'bg-red-50/40' : '' }}">
                    <div class="flex items-start gap-3">
                        <img src="{{ $user->getAvatarUrl() }}" alt="" class="w-10 h-10 rounded-full ring-2 ring-slate-100 bg-white flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-slate-900 hover:text-primary-600">{{ $user->name }}</a>
                                @if($user->is_admin)
                                    <span class="badge bg-amber-100 text-amber-700">管理员</span>
                                @elseif($user->is_blocked)
                                    <span class="badge bg-red-100 text-red-700">已封禁</span>
                                @else
                                    <span class="badge bg-emerald-100 text-emerald-700">正常</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $user->email }}</div>
                            <div class="flex items-center gap-3 mt-2 text-xs text-slate-500">
                                <span>帖 {{ $user->threads_count }}</span>
                                <span>复 {{ $user->replies_count }}</span>
                                <span>{{ $user->created_at->format('m-d H:i') }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-primary-600 text-xs">详情</a>
                                @if($user->is_blocked)
                                    <form method="POST" action="{{ route('admin.users.unblock', $user) }}" onsubmit="return confirm('确定解封 {{ $user->name }} 吗？');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-emerald-600 text-xs">解封</button>
                                    </form>
                                @elseif(!$user->is_admin && $user->id !== auth()->id())
                                    <button type="button" onclick="openBlockModal({{ $user->id }}, '{{ e($user->name) }}')" class="text-red-500 text-xs">封禁</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    <p>未找到匹配的用户</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 分页 --}}
    @if($users->hasPages())
        <div class="flex justify-center">
            {{ $users->links() }}
        </div>
    @endif
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
        <p class="text-sm text-slate-600 mb-3">确定要封禁 <b id="blockUserName" class="text-slate-900"></b> 吗？封禁后该用户将无法登录。</p>
        <form id="blockForm" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">封禁原因（可选）</label>
                <input type="text" name="reason" maxlength="200" class="input w-full px-3 py-2 text-sm" placeholder="如：发布违规内容、广告刷屏...">
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="closeBlockModal()" class="btn-secondary flex-1 py-2 text-sm">取消</button>
                <button type="submit" class="btn-danger flex-1 py-2 text-sm">确认封禁</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBlockModal(userId, userName) {
        document.getElementById('blockUserName').textContent = userName;
        document.getElementById('blockForm').action = '/admin/users/' + userId + '/block';
        var modal = document.getElementById('blockModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeBlockModal() {
        var modal = document.getElementById('blockModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    document.getElementById('blockModal').addEventListener('click', function(e) {
        if (e.target === this) closeBlockModal();
    });
</script>
@endsection
