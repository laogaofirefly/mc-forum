@extends('layouts.app')

@section('title', '编辑回复')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mc-card rounded-lg p-6">
        <h2 class="text-xl font-bold text-white mb-6">编辑回复</h2>
        <form method="POST" action="{{ route('replies.update', $reply) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-300 mb-1">回复内容</label>
                    <textarea id="body" name="body" rows="6" required
                        class="mc-input w-full px-4 py-2 rounded-lg">{{ old('body', $reply->body) }}</textarea>
                </div>
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('threads.show', $reply->thread->slug) }}" class="text-gray-400 hover:text-gray-300 transition">
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
