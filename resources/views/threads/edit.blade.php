@extends('layouts.app')

@section('title', '编辑帖子 - ' . $thread->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mc-card rounded-lg p-6">
        <h2 class="text-xl font-bold text-white mb-6">编辑帖子</h2>
        <form method="POST" action="{{ route('threads.update', $thread->slug) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-1">标题</label>
                    <input id="title" type="text" name="title" value="{{ old('title', $thread->title) }}" required
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-300 mb-1">内容</label>
                    <textarea id="body" name="body" rows="12" required
                        class="mc-input w-full px-4 py-2 rounded-lg">{{ old('body', $thread->body) }}</textarea>
                </div>
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('threads.show', $thread->slug) }}" class="text-gray-400 hover:text-gray-300 transition">
                        ← 返回帖子
                    </a>
                    <button type="submit" class="mc-button text-white px-6 py-2 rounded-lg font-bold">
                        保存修改
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
