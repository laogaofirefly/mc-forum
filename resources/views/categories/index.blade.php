@extends('layouts.app')

@section('title', '板块列表')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">所有板块</h1>
    </div>

    <div class="grid gap-4">
        @foreach($categories as $category)
            <div class="mc-card rounded-lg p-5 hover:border-primary-500/50 transition">
                <div class="flex items-start space-x-4">
                    <div class="w-14 h-14 bg-primary-900 rounded-lg flex items-center justify-center text-2xl flex-shrink-0">
                        {{ $category->icon ?? '📁' }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold">
                                <a href="{{ route('categories.show', $category->slug) }}" class="text-primary-400 hover:text-primary-300 transition">
                                    {{ $category->name }}
                                </a>
                            </h3>
                            <span class="text-sm text-gray-400">{{ $category->threads_count }} 个帖子</span>
                        </div>
                        @if($category->description)
                            <p class="text-gray-400 text-sm mt-1">{{ $category->description }}</p>
                        @endif
                        @if($category->latestThread)
                            <div class="mt-3 pt-3 border-t border-gray-700 text-sm">
                                <span class="text-gray-500">最新帖子: </span>
                                <a href="{{ route('threads.show', $category->latestThread->slug) }}" class="text-gray-300 hover:text-primary-400 transition">
                                    {{ $category->latestThread->title }}
                                </a>
                                <span class="text-gray-500 ml-2">{{ $category->latestThread->created_at->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($categories->isEmpty())
            <div class="mc-card rounded-lg p-8 text-center text-gray-400">
                暂无板块
            </div>
        @endif
    </div>
</div>
@endsection
