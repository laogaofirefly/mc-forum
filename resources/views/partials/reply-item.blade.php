<div class="card p-4" id="reply-{{ $reply->id }}">
    <div class="flex items-start space-x-3">
        <img src="{{ $reply->user->getAvatarUrl() }}" alt="{{ $reply->user->name }}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full ring-2 ring-slate-100 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center flex-wrap gap-x-2 gap-y-1">
                    <a href="{{ route('profile.show', $reply->user) }}" class="text-slate-900 font-semibold hover:text-primary-600 transition">{{ $reply->user->name }}</a>
                    @if($reply->user->isAdmin())
                        <span class="badge bg-red-100 text-red-700">管理员</span>
                    @endif
                    <span class="text-slate-400 text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex items-center space-x-1">
                    @auth
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
                    @endauth
                </div>
            </div>
            <div class="mt-2 prose prose-slate prose-sm max-w-none break-words
                        prose-headings:font-bold prose-headings:text-slate-900
                        prose-a:text-primary-600 prose-a:no-underline hover:prose-a:underline
                        prose-img:rounded-lg prose-img:shadow-sm
                        prose-code:text-pink-600 prose-code:bg-slate-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:before:content-none prose-code:after:content-none
                        prose-pre:bg-slate-800 prose-pre:text-slate-100">
                {!! \App\Services\MarkdownService::toHtml($reply->body) !!}
            </div>
        </div>
    </div>
</div>
