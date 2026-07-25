<div class="mc-card rounded-lg p-4" id="reply-{{ $reply->id }}">
    <div class="flex items-start space-x-3">
        <img src="{{ $reply->user->getAvatarUrl() }}" alt="{{ $reply->user->name }}" class="w-10 h-10 rounded border-2 border-gray-600 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('profile.show', $reply->user) }}" class="text-primary-400 font-bold hover:underline">{{ $reply->user->name }}</a>
                    @if($reply->user->isAdmin())
                        <span class="text-xs bg-red-600 text-white px-2 py-0.5 rounded">管理员</span>
                    @endif
                    <span class="text-gray-500 text-sm">{{ $reply->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    @auth
                        <form method="POST" action="{{ route('like.toggle', ['type' => 'reply', 'id' => $reply->id]) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-red-400 transition text-sm flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="{{ $reply->isLikedBy(auth()->user()) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                {{ $reply->likesCount() }}
                            </button>
                        </form>
                    @endauth
                    @guest
                        <span class="text-gray-500 text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            {{ $reply->likesCount() }}
                        </span>
                    @endguest
                    @auth
                        @if(auth()->id() === $reply->user_id || auth()->user()->isAdmin())
                            <div class="relative group">
                                <button class="text-gray-500 hover:text-gray-300 p-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-1 w-32 bg-gray-800 rounded shadow-lg border border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition z-10">
                                    <a href="{{ route('replies.edit', $reply) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">编辑</a>
                                    <form method="POST" action="{{ route('replies.destroy', $reply) }}" onsubmit="return confirm('确定删除这条回复吗？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-gray-700">删除</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
            <div class="mt-2 text-gray-200 whitespace-pre-wrap">{{ $reply->body }}</div>
        </div>
    </div>
</div>
