@extends('layouts.app')

@section('title', $user->name . ' 的资料')

@section('content')
<div class="space-y-6">
    <div class="mc-card rounded-lg p-6">
        <div class="flex items-start space-x-6">
            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-lg border-4 border-primary-500">
            <div class="flex-1">
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
                    @if($user->isAdmin())
                        <span class="bg-red-600 text-white text-xs px-2 py-1 rounded font-bold">管理员</span>
                    @endif
                    @if($user->mc_verified)
                        <span class="bg-green-600 text-white text-xs px-2 py-1 rounded flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            MC已验证
                        </span>
                    @endif
                </div>
                @if($user->mc_username)
                    <p class="text-primary-400 mt-1">MC ID: {{ $user->mc_username }}</p>
                @endif
                @if($user->bio)
                    <p class="text-gray-300 mt-3">{{ $user->bio }}</p>
                @else
                    <p class="text-gray-500 mt-3 italic">这个人很懒，什么都没留下...</p>
                @endif
                <div class="flex items-center space-x-6 mt-4 text-sm text-gray-400">
                    <span>帖子: {{ $user->threads()->count() }}</span>
                    <span>回复: {{ $user->replies()->count() }}</span>
                    <span>注册于: {{ $user->created_at->format('Y-m-d') }}</span>
                </div>
                @auth
                    @if(auth()->id() === $user->id)
                        <div class="mt-4 space-x-3">
                            <a href="{{ route('profile.edit') }}" class="inline-block mc-button text-white px-4 py-2 rounded text-sm font-bold">
                                编辑资料
                            </a>
                            <a href="{{ route('profile.mc-bind') }}" class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-bold transition">
                                绑定 MC 账号
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-xl font-bold text-white mb-4">最近发布的帖子</h2>
        <div class="space-y-3">
            @foreach($threads as $thread)
                @include('partials.thread-card', ['thread' => $thread])
            @endforeach
            @if($threads->isEmpty())
                <div class="mc-card rounded-lg p-6 text-center text-gray-400">
                    暂无帖子
                </div>
            @endif
        </div>
    </div>

    <div>
        <h2 class="text-xl font-bold text-white mb-4">最近的回复</h2>
        <div class="space-y-3">
            @foreach($replies as $reply)
                <div class="mc-card rounded-lg p-4">
                    <div class="text-sm text-gray-400 mb-1">
                        回复了帖子
                        <a href="{{ route('threads.show', $reply->thread->slug) }}" class="text-primary-400 hover:underline">{{ $reply->thread->title }}</a>
                        <span class="ml-2">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-300 truncate">{{ $reply->body }}</p>
                </div>
            @endforeach
            @if($replies->isEmpty())
                <div class="mc-card rounded-lg p-6 text-center text-gray-400">
                    暂无回复
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
