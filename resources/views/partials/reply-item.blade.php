<div class="mc-card rounded-lg p-4" id="reply-{{ $reply->id }}">
    <div class="flex items-start space-x-3">
        <img src="{{ $reply->user->getAvatarUrl() }}" alt="{{ $reply->user->name }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded border-2 border-gray-600 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center flex-wrap gap-x-2 gap-y-1">
                    <a href="{{ route('profile.show', $reply->user) }}" class="text-primary-400 font-bold hover:underline">{{ $reply->user->name }}</a>
                    @if($reply->user->isAdmin())
                        <span class="text-xs bg-red-600 text-white px-2 py-0.5 rounded">管理员</span>
                    @endif
                    <span class="text-gray-500 text-xs sm:text-sm">{{ $reply->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex items-center space-x-1">
                    @auth
                        @if(auth()->id() === $reply->user_id || auth()->user()->isAdmin())
                            <a href="{{ route('replies.edit', $reply) }}" class="text-gray-500 hover:text-primary-400 transition text-sm px-2 py-1 rounded hover:bg-gray-700/50">
                                编辑
                            </a>
                            <form method="POST" action="{{ route('replies.destroy', $reply) }}" onsubmit="return confirm('确定删除这条回复吗？');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-500 hover:text-red-400 transition text-sm px-2 py-1 rounded hover:bg-gray-700/50">
                                    删除
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
            <div class="mt-2 text-gray-200 whitespace-pre-wrap break-words text-sm sm:text-base leading-relaxed">{{ $reply->body }}</div>
        </div>
    </div>
</div>
