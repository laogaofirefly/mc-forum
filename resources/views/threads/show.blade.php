@extends('layouts.app')

@section('title', $thread->title)

@section('content')
<div class="space-y-6">
    <div class="text-sm text-gray-400">
        <a href="{{ route('home') }}" class="hover:text-primary-400">首页</a>
        <span class="mx-2">/</span>
        <span class="text-gray-500">{{ $thread->title }}</span>
    </div>

    <div class="mc-card rounded-lg p-4 sm:p-6">
        <div class="flex items-start space-x-3 sm:space-x-4">
            <img src="{{ $thread->user->getAvatarUrl() }}" alt="{{ $thread->user->name }}" class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg border-2 border-gray-600 flex-shrink-0">
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between flex-wrap gap-2">
                    <h1 class="text-xl sm:text-2xl font-bold text-white break-words">
                        @if($thread->is_pinned)
                            <span class="text-yellow-400">📌</span>
                        @endif
                        {{ $thread->title }}
                    </h1>
                    <div class="flex items-center space-x-2 flex-shrink-0">
                        @auth
                            @if(auth()->id() === $thread->user_id || auth()->user()->isAdmin())
                                <a href="{{ route('threads.edit', $thread->slug) }}" class="text-gray-400 hover:text-primary-400 text-sm transition px-2 py-1 rounded">
                                    编辑
                                </a>
                                <form method="POST" action="{{ route('threads.destroy', $thread->slug) }}" onsubmit="return confirm('确定删除这个帖子吗？');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-400 text-sm transition px-2 py-1 rounded">
                                        删除
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
                <div class="flex items-center flex-wrap gap-x-3 gap-y-1 mt-2 text-sm text-gray-400">
                    <a href="{{ route('profile.show', $thread->user) }}" class="text-primary-400 hover:underline font-medium">{{ $thread->user->name }}</a>
                    @if($thread->user->isAdmin())
                        <span class="text-xs bg-red-600 text-white px-2 py-0.5 rounded">管理员</span>
                    @endif
                    <span>{{ $thread->created_at->diffForHumans() }}</span>
                    <span>{{ $thread->views_count }} 浏览</span>
                </div>
                <div class="mt-4 sm:mt-6 text-gray-200 whitespace-pre-wrap break-words leading-relaxed text-sm sm:text-base">
                    {{ $thread->body }}
                </div>
                <div class="flex items-center mt-4 sm:mt-6 pt-4 border-t border-gray-700">
                    <span class="text-gray-400 flex items-center text-sm">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        {{ $replyCount }} 回复
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-base sm:text-lg font-bold text-white mb-4">回复 ({{ $replyCount }})</h2>
        <div class="space-y-3">
            @foreach($thread->replies as $reply)
                @include('partials.reply-item', ['reply' => $reply])
            @endforeach
            @if($thread->replies->isEmpty())
                <div class="mc-card rounded-lg p-6 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    暂无回复，来说两句吧！
                </div>
            @endif
        </div>
    </div>

    @auth
        @if(!$thread->is_locked)
            <div class="mc-card rounded-lg p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-bold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    发表回复
                </h3>
                <form method="POST" action="{{ route('replies.store', $thread->slug) }}">
                    @csrf
                    <div class="space-y-3">
                        <textarea name="body" rows="4" required data-maxlength="5000"
                            class="mc-input w-full px-4 py-3 rounded-lg text-base @error('body') input-error @enderror"
                            placeholder="写下你的回复...">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">支持换行，最多5000字</span>
                            <button type="submit" class="mc-button text-white px-5 py-2.5 rounded-lg font-bold text-sm">
                                发表回复
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="mc-card rounded-lg p-6 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                该帖子已锁定，无法回复。
            </div>
        @endif
    @else
        <div class="mc-card rounded-lg p-6 text-center text-gray-400">
            请 <a href="{{ route('login') }}" class="text-primary-400 hover:underline font-medium">登录</a> 后发表回复。
        </div>
    @endauth
</div>
@endsection
