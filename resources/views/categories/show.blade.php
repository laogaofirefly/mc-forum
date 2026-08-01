@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center">
                <span class="text-3xl mr-3">{{ $category->icon ?? '📁' }}</span>
                {{ $category->name }}
            </h1>
            @if($category->description)
                <p class="text-gray-400 mt-1">{{ $category->description }}</p>
            @endif
        </div>
        @auth
            <a href="{{ route('threads.create') }}" class="mc-button text-white px-4 py-2 rounded-lg font-bold">
                + 发布新帖
            </a>
        @endauth
    </div>

    @if(!$pinnedThreads->isEmpty())
        <div>
            <h2 class="text-sm font-bold text-yellow-400 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 00.293 1.006L2 11v2h2v4h2v-4h4v4h2v-4h2v-2l-1.293-2.994A2 2 0 0015 7V5a2 2 0 00-2-2H5z"/>
                </svg>
                置顶帖子
            </h2>
            <div class="space-y-3">
                @foreach($pinnedThreads as $thread)
                    @include('partials.thread-card', ['thread' => $thread])
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <h2 class="text-sm font-bold text-gray-400 mb-3">全部帖子</h2>
        <div class="space-y-3">
            @foreach($normalThreads as $thread)
                @include('partials.thread-card', ['thread' => $thread])
            @endforeach
            @if($normalThreads->isEmpty() && $pinnedThreads->isEmpty())
                <div class="mc-card rounded-lg p-8 text-center text-gray-400">
                    暂无帖子，成为第一个发帖的人吧！
                </div>
            @endif
        </div>

        <div class="mt-6">
            {{ $normalThreads->links() }}
        </div>
    </div>
</div>
@endsection
