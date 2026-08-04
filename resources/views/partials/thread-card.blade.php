<div class="card card-hover p-4">
    <div class="flex items-start space-x-3">
        <img src="{{ $thread->user->getAvatarUrl() }}" alt="{{ $thread->user->name }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full ring-2 ring-slate-100 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <h4 class="font-semibold text-base sm:text-lg">
                <a href="{{ route('threads.show', $thread->slug) }}" class="text-slate-900 hover:text-primary-600 transition inline-flex items-center gap-1.5">
                    @if($thread->is_pinned)
                        @include('layouts.partials.icons', ['name' => 'pin', 'class' => 'w-4 h-4 inline text-amber-500'])
                    @endif
                    <span>{{ $thread->title }}</span>
                </a>
            </h4>
            <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500 mt-1">
                <a href="{{ route('profile.show', $thread->user) }}" class="text-primary-600 hover:underline">{{ $thread->user->name }}</a>
                <span>{{ $thread->created_at->diffForHumans() }}</span>
            </div>
            <div class="flex items-center space-x-4 mt-2 text-xs text-slate-500">
                <span class="flex items-center">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ $thread->views_count ?? 0 }}
                </span>
                <span class="flex items-center">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    {{ $thread->replies_count ?? $thread->replies->count() }}
                </span>
                @if($thread->latestReply)
                    <span class="text-slate-400 hidden sm:inline">最后回复 {{ $thread->latestReply->created_at->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
