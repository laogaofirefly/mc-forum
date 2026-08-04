@extends('layouts.app')

@section('title', '全部帖子')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="page-title text-slate-900">全部帖子</h1>
        @auth
            <a href="{{ route('threads.create') }}" class="btn-primary inline-flex items-center gap-1.5 px-4 py-2 text-sm">
                @include('layouts.partials.icons', ['name' => 'plus', 'class' => 'w-4 h-4'])发布新帖
            </a>
        @endauth
    </div>

    <form method="GET" action="{{ route('threads.index') }}" class="flex flex-col sm:flex-row gap-2">
        <input type="text" name="q" value="{{ $search }}" placeholder="搜索帖子标题或内容..."
            class="input flex-1 px-4 py-2">
        <button type="submit" class="btn-primary px-3 py-2 text-sm" title="搜索">
            @include('layouts.partials.icons', ['name' => 'search', 'class' => 'w-5 h-5'])
        </button>
    </form>

    <div class="space-y-3">
        @foreach($threads as $thread)
            <a href="{{ route('threads.show', $thread->slug) }}" class="card card-hover p-3 sm:p-4 block group">
                <div class="flex items-start space-x-3">
                    <img src="{{ $thread->user->getAvatarUrl() }}" alt="{{ $thread->user->name }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full ring-2 ring-slate-100 flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-base sm:text-lg">
                            <span class="text-slate-900 group-hover:text-primary-700 transition break-words">
                                @if($thread->is_pinned)
                                    @include('layouts.partials.icons', ['name' => 'pin', 'class' => 'w-4 h-4 inline text-amber-500'])
                                @endif
                                {{ $thread->title }}
                            </span>
                        </h4>
                        <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-xs sm:text-sm text-slate-500 mt-1">
                            <span class="text-primary-600">{{ $thread->user->name }}</span>
                            <span>{{ $thread->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center space-x-4 mt-2 text-xs sm:text-sm">
                            <span class="text-slate-500 flex items-center">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                {{ $thread->views_count ?? 0 }}
                            </span>
                            <span class="text-slate-500 flex items-center">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ $thread->replies_count }}
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach

        @if($threads->isEmpty())
            <div class="card p-8 text-center text-slate-400">
                暂无帖子
            </div>
        @endif
    </div>

    <div class="pt-2">
        {{ $threads->links() }}
    </div>
</div>
@endsection
