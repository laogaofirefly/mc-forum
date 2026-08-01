@extends('layouts.app')

@section('title', '首页')

@section('content')
<div class="space-y-6">
    <div class="mc-card rounded-lg p-5 sm:p-6 bg-gradient-to-r from-primary-900/50 to-transparent">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">欢迎来到 MC 论坛</h1>
                <p class="text-gray-300 text-sm sm:text-base">我的世界玩家社区 - 分享、交流、一起玩</p>
            </div>
            @auth
                <a href="{{ route('threads.create') }}" class="mc-button text-white px-5 sm:px-6 py-2.5 sm:py-3 rounded-lg font-bold text-sm sm:text-base whitespace-nowrap">
                    + 发布新帖
                </a>
            @endauth
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-white flex items-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                最新帖子
            </h2>
        </div>
        <div class="space-y-3">
            @foreach($latestThreads as $thread)
                @include('partials.thread-card', ['thread' => $thread])
            @endforeach
            @if($latestThreads->isEmpty())
                <div class="mc-card rounded-lg p-8 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>暂无帖子，成为第一个发帖的人吧！</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
