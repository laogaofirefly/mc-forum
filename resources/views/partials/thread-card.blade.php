<div class="mc-card rounded-lg p-4 hover:border-primary-500/50 transition">
    <div class="flex items-start space-x-3">
        <img src="{{ $thread->user->getAvatarUrl() }}" alt="{{ $thread->user->name }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded border-2 border-gray-600 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <h4 class="font-bold text-base sm:text-lg">
                <a href="{{ route('threads.show', $thread->slug) }}" class="text-primary-400 hover:text-primary-300 transition">
                    @if($thread->is_pinned)
                        <span class="text-yellow-400 text-sm">📌</span>
                    @endif
                    {{ $thread->title }}
                </a>
            </h4>
            <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-xs sm:text-sm text-gray-400 mt-1">
                <a href="{{ route('profile.show', $thread->user) }}" class="text-primary-400 hover:underline">{{ $thread->user->name }}</a>
                <span>{{ $thread->created_at->diffForHumans() }}</span>
            </div>
            <div class="flex items-center space-x-4 mt-2 text-xs sm:text-sm">
                <span class="text-gray-500 flex items-center">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ $thread->views_count ?? 0 }}
                </span>
                <span class="text-gray-500 flex items-center">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    {{ $thread->replies_count ?? $thread->replies->count() }}
                </span>
                @if($thread->latestReply)
                    <span class="text-gray-600 hidden sm:inline">最后回复 {{ $thread->latestReply->created_at->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
