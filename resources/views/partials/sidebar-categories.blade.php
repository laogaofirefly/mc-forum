<div class="mc-card rounded-lg p-4">
    <h3 class="text-lg font-bold text-primary-400 mb-3 flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
        </svg>
        板块列表
    </h3>
    <ul class="space-y-2">
        @foreach($categories ?? [] as $category)
            <li>
                <a href="{{ route('categories.show', $category->slug) }}" class="flex items-center justify-between p-2 rounded hover:bg-gray-700/50 transition">
                    <span class="text-gray-200 hover:text-primary-400 transition text-sm">
                        @if($category->icon)
                            <span class="mr-2">{{ $category->icon }}</span>
                        @endif
                        {{ $category->name }}
                    </span>
                    <span class="text-xs text-gray-500 bg-gray-700 px-2 py-1 rounded">{{ $category->threads_count ?? 0 }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
