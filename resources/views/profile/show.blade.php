@extends('layouts.app')

@section('title', $user->name . ' 的资料')

@section('content')
<div class="space-y-6">
    <div class="card p-6">
        <div class="flex items-start space-x-6">
            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full ring-2 ring-slate-100">
            <div class="flex-1">
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl font-bold text-slate-900">{{ $user->name }}</h1>
                    @if($user->isAdmin())
                        <span class="badge bg-red-100 text-red-700">管理员</span>
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
                    <p class="text-primary-600 mt-1">MC ID: {{ $user->mc_username }}</p>
                @endif
                @if($user->bio)
                    <p class="text-slate-700 mt-3">{{ $user->bio }}</p>
                @else
                    <p class="text-slate-400 mt-3 italic">这个人很懒，什么都没留下...</p>
                @endif
                <div class="grid grid-cols-3 gap-3 mt-4">
                    <div class="rounded-lg bg-slate-50 p-3 text-center">
                        <div class="text-lg font-bold text-primary-600">{{ $user->threads()->count() }}</div>
                        <div class="text-xs text-slate-500">帖子</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 text-center">
                        <div class="text-lg font-bold text-primary-600">{{ $user->replies()->count() }}</div>
                        <div class="text-xs text-slate-500">回复</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 text-center">
                        <div class="text-lg font-bold text-primary-600">{{ $user->created_at->format('Y-m-d') }}</div>
                        <div class="text-xs text-slate-500">注册于</div>
                    </div>
                </div>
                @auth
                    @if(auth()->id() === $user->id)
                        <div class="mt-4 flex space-x-3">
                            <a href="{{ route('profile.edit') }}" class="btn-secondary text-sm">
                                编辑资料
                            </a>
                            <a href="{{ route('profile.mc-bind') }}" class="btn-primary text-sm">
                                绑定 MC 账号
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-lg font-bold text-slate-900 mb-4">最近发布的帖子</h2>
        <div class="space-y-3">
            @foreach($threads as $thread)
                @include('partials.thread-card', ['thread' => $thread])
            @endforeach
            @if($threads->isEmpty())
                <div class="card p-6 text-center text-slate-400">
                    暂无帖子
                </div>
            @endif
        </div>
    </div>

    <div>
        <h2 class="text-lg font-bold text-slate-900 mb-4">最近的回复</h2>
        <div class="space-y-3">
            @foreach($replies as $reply)
                <div class="card p-4">
                    <div class="text-sm text-slate-500 mb-1">
                        回复了帖子
                        <a href="{{ route('threads.show', $reply->thread->slug) }}" class="text-primary-600 hover:text-primary-700 hover:underline">{{ $reply->thread->title }}</a>
                        <span class="ml-2">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-slate-700 truncate">{{ $reply->body }}</p>
                </div>
            @endforeach
            @if($replies->isEmpty())
                <div class="card p-6 text-center text-slate-400">
                    暂无回复
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
