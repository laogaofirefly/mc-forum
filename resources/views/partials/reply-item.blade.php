<div class="card p-4" id="reply-{{ $reply->id }}">
    <div class="flex items-start space-x-3">
        <img src="{{ $reply->user->getAvatarUrl() }}" alt="{{ $reply->user->name }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full ring-2 ring-slate-100 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center flex-wrap gap-x-2 gap-y-1">
                    <a href="{{ route('profile.show', $reply->user) }}" class="text-slate-900 font-semibold hover:text-primary-600 transition text-sm">{{ $reply->user->name }}</a>
                    @if($reply->user->isAdmin())
                        <span class="badge bg-red-100 text-red-700">管理员</span>
                    @endif
                    <span class="text-slate-400 text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex items-center space-x-1">
                    @auth
                        <button
                            class="like-btn text-xs flex items-center space-x-1 px-2 py-1 rounded-full transition-colors {{ $reply->isLikedBy(auth()->user()) ? 'text-red-500 bg-red-50 hover:bg-red-100' : 'text-slate-400 hover:text-red-400 hover:bg-red-50' }}"
                            data-like-type="reply"
                            data-like-id="{{ $reply->id }}"
                            onclick="toggleLike(this)"
                        >
                            <svg class="w-4 h-4" fill="{{ $reply->isLikedBy(auth()->user()) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span class="like-count">{{ $reply->likesCount() }}</span>
                        </button>
                        @if(auth()->id() === $reply->user_id || auth()->user()->isAdmin())
                            <a href="{{ route('replies.edit', $reply) }}" class="text-slate-400 hover:text-primary-600 transition text-sm px-2 py-1 rounded hover:bg-slate-50">
                                编辑
                            </a>
                            <form method="POST" action="{{ route('replies.destroy', $reply) }}" onsubmit="return confirm('确定删除这条回复吗？');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition text-sm px-2 py-1 rounded hover:bg-slate-50">
                                    删除
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="text-xs flex items-center space-x-1 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span>{{ $reply->likesCount() }}</span>
                        </span>
                    @endauth
                </div>
            </div>
            <div class="mt-2 prose prose-slate prose-sm max-w-none break-words">
                {!! \App\Services\MarkdownService::toHtml($reply->body) !!}
            </div>
        </div>
    </div>
</div>
