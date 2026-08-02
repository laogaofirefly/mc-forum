@extends('layouts.app')

@section('title', '首页')

@section('content')
<div class="space-y-5 sm:space-y-6">

    {{-- 顶部欢迎+发帖入口 --}}
    <div class="card p-5 sm:p-7 bg-gradient-to-br from-primary-500 to-primary-700 border-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mb-1.5 flex items-center">
                    <span class="mr-2">⛏️</span>MC 玩家论坛
                </h1>
                <p class="text-primary-50 text-sm sm:text-base">分享你的建筑、生存、红石技巧，和 MC 玩家一起交流</p>
            </div>
            @auth
                <a href="{{ route('threads.create') }}" class="bg-white text-primary-700 hover:bg-primary-50 px-6 py-3 rounded-xl font-bold text-center whitespace-nowrap inline-block transition shadow-sm">
                    ✏️ 发布新帖
                </a>
            @else
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl text-white border-2 border-white/40 hover:border-white transition text-center font-medium">
                        登录
                    </a>
                    <a href="{{ route('register') }}" class="bg-white text-primary-700 hover:bg-primary-50 px-6 py-2.5 rounded-xl font-bold text-center whitespace-nowrap transition shadow-sm">
                        立即注册
                    </a>
                </div>
            @endauth
        </div>
    </div>

    {{-- 最新帖子（显示4个） --}}
    <div class="card p-4 sm:p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-slate-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                最新帖子
            </h2>
            <a href="{{ route('threads.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                查看全部 →
            </a>
        </div>
        <div class="space-y-2.5 sm:space-y-3">
            @foreach($latestThreads as $thread)
                <div class="card card-hover p-3 sm:p-4">
                    <div class="flex items-start space-x-3">
                        <img src="{{ $thread->user->getAvatarUrl() }}" alt="{{ $thread->user->name }}" class="w-9 h-9 sm:w-11 sm:h-11 rounded-full ring-2 ring-slate-100 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-sm sm:text-base leading-snug">
                                <a href="{{ route('threads.show', $thread->slug) }}" class="text-slate-900 hover:text-primary-600 transition break-words">
                                    @if($thread->is_pinned)
                                        <span class="text-amber-500 text-xs sm:text-sm mr-0.5">📌</span>
                                    @endif
                                    {{ $thread->title }}
                                </a>
                            </h4>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-500 mt-1.5">
                                <a href="{{ route('profile.show', $thread->user) }}" class="text-primary-600 hover:underline">{{ $thread->user->name }}</a>
                                <span>·</span>
                                <span>{{ $thread->created_at->diffForHumans() }}</span>
                                <span>·</span>
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    {{ $thread->replies_count ?? $thread->replies->count() }}
                                </span>
                                <span class="hidden sm:inline">·</span>
                                <span class="hidden sm:inline flex items-center">
                                    <svg class="w-3.5 h-3.5 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    {{ $thread->views_count ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            @if($latestThreads->isEmpty())
                <div class="py-10 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>暂无帖子，成为第一个发帖的人吧！</p>
                </div>
            @endif
        </div>
    </div>

    {{-- 服务器状态 --}}
    <div class="card p-4 sm:p-5">
        @include('partials.server-status')
    </div>

</div>
@endsection
