@extends('layouts.app')

@section('title', '首页')

@section('content')
<div class="space-y-6">
    <div class="mc-card rounded-lg p-6 bg-gradient-to-r from-primary-900/50 to-transparent">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">欢迎来到 MC 论坛</h1>
                <p class="text-gray-300">我的世界玩家社区 - 分享、交流、一起玩</p>
            </div>
            @auth
                <a href="{{ route('threads.create') }}" class="mc-button text-white px-6 py-3 rounded-lg font-bold">
                    + 发布新帖
                </a>
            @endauth
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white flex items-center">
                <svg class="w-6 h-6 mr-2 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                最新帖子
            </h2>
            <a href="{{ route('categories.index') }}" class="text-primary-400 hover:text-primary-300 text-sm">查看全部 →</a>
        </div>
        <div class="space-y-3">
            @foreach($latestThreads as $thread)
                @include('partials.thread-card', ['thread' => $thread])
            @endforeach
            @if($latestThreads->isEmpty())
                <div class="mc-card rounded-lg p-8 text-center text-gray-400">
                    暂无帖子，成为第一个发帖的人吧！
                </div>
            @endif
        </div>
    </div>

    <div>
        <h2 class="text-xl font-bold text-white flex items-center mb-4">
            <svg class="w-6 h-6 mr-2 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
            </svg>
            热门帖子
        </h2>
        <div class="mc-card rounded-lg divide-y divide-gray-700">
            @foreach($popularThreads as $index => $thread)
                <div class="p-4 hover:bg-gray-700/30 transition flex items-center space-x-3">
                    <span class="text-lg font-bold w-6 text-center {{ $index == 0 ? 'text-yellow-400' : ($index == 1 ? 'text-gray-300' : ($index == 2 ? 'text-orange-400' : 'text-gray-500')) }}">{{ $index + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('threads.show', $thread->slug) }}" class="text-gray-200 hover:text-primary-400 transition truncate block">
                            {{ $thread->title }}
                        </a>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $thread->views_count }} 浏览 · {{ $thread->replies_count }} 回复
                        </div>
                    </div>
                </div>
            @endforeach
            @if($popularThreads->isEmpty())
                <div class="p-8 text-center text-gray-400">
                    暂无热门帖子
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
