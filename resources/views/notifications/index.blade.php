@extends('layouts.app')

@section('title', '消息通知')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 flex items-center">
                <span class="mr-2">🔔</span>消息通知
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">别人回复你帖子的记录</p>
        </div>
        @if($unreadCount > 0)
            <span class="badge bg-red-100 text-red-700">
                {{$unreadCount}} 条未读
            </span>
        @else
            <span class="badge bg-slate-100 text-slate-600">已全部读完</span>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($replies as $reply)
            <div class="card p-4 card-hover">
                <div class="flex items-start gap-3">
                    <a href="{{ route('profile.show', $reply->user) }}" class="flex-shrink-0">
                        <img src="{{ $reply->user->getAvatarUrl() }}" alt="{{ $reply->user->name }}"
                            class="w-10 h-10 rounded-full ring-2 ring-slate-100 bg-white object-cover">
                    </a>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-slate-600 mb-1">
                            <a href="{{ route('profile.show', $reply->user) }}" class="text-primary-600 hover:text-primary-700 hover:underline font-medium">{{ $reply->user->name }}</a>
                            回复了你的帖子
                            <a href="{{ route('threads.show', $reply->thread->slug) }}#reply-{{ $reply->id }}"
                               class="text-slate-900 hover:text-primary-600 hover:underline font-medium">{{ $reply->thread->title }}</a>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 prose prose-slate prose-sm max-w-none border border-slate-100">
                            {!! \App\Services\MarkdownService::toHtml($reply->body) !!}
                        </div>
                        <div class="text-xs text-slate-400 mt-1.5">{{ $reply->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="font-medium text-slate-500">暂无通知</p>
                <p class="mt-1 text-xs">当有人回复你的帖子时，会在这里显示</p>
            </div>
        @endforelse
    </div>

    @if($replies->count() >= 50)
        <div class="text-center text-xs text-slate-400 py-2">仅显示最近 50 条通知</div>
    @endif
</div>
@endsection
