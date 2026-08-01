@extends('layouts.app')

@section('title', '编辑回复')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mc-card rounded-lg p-5 sm:p-6">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-6">编辑回复</h2>
        <form method="POST" action="{{ route('replies.update', $reply) }}">
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-300 mb-1">
                        回复内容 <span class="text-red-400">*</span>
                    </label>
                    <textarea id="body" name="body" rows="6" required data-maxlength="5000"
                        class="mc-input w-full px-4 py-3 rounded-lg text-base leading-relaxed @error('body') input-error @enderror">{{ old('body', $reply->body) }}</textarea>
                    @error('body')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between pt-4 gap-3">
                    <a href="{{ route('threads.show', $reply->thread->slug) }}" class="text-center text-gray-400 hover:text-gray-300 transition px-4 py-2 rounded-lg border border-gray-700 hover:border-gray-500">
                        ← 返回帖子
                    </a>
                    <button type="submit" class="mc-button text-white px-6 py-3 rounded-lg font-bold text-base">
                        保存修改
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
