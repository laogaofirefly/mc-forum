<div class="mc-card rounded-lg p-4 hover:border-primary-500/50 transition">
    <div class="flex items-start space-x-3">
        <img src="{{ $thread->user->getAvatarUrl() }}" alt="{{ $thread->user->name }}" class="w-12 h-12 rounded border-2 border-gray-600 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between">
                <h4 class="font-bold text-lg">
                    <a href="{{ route('threads.show', $thread->slug) }}" class="text-primary-400 hover:text-primary-300 transition">
                        @if($thread->is_pinned)
                            <span class="text-yellow-400 text-sm">📌</span>
                        @endif
                        {{ $thread->title }}
                    </a>
                </h4>
            </div>
            <div class="flex items-center space-x-4 text-sm text-gray-400 mt-1">
                <span>
                    <a href="{{ route('profile.show', $thread->user) }}" class="text-primary-400 hover:underline">{{ $thread->user->name }}</a>
                </span>
                <span>{{ $thread->created_at->diffForHumans() }}</span>
                @if(isset($thread->category))
                <span>
                    <a href="{{ route('categories.show', $thread->category->slug) }}" class="text-gray-400 hover:text-primary-400 transition">
                        {{ $thread->category->name }}
                    </a>
                </span>
                @endif
            </div>
            <div class="flex items-center space-x-4 mt-2 text-sm">
                <span class="text-gray-500">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ $thread->views_count }}
                </span>
                <span class="text-gray-500">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                    </svg>
                    {{ $thread->replies_count ?? $thread->replies->count() }}
                </span>
                <span class="text-gray-500">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                    </svg>
                    {{ $thread->likes_count ?? 0 }}
                </span>
            </div>
        </div>
    </div>
</div>
