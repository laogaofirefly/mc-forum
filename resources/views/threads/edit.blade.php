@extends('layouts.app')

@section('title', '编辑帖子 - ' . $thread->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mc-card rounded-lg p-5 sm:p-6">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-6">编辑帖子</h2>
        <form method="POST" action="{{ route('threads.update', $thread->slug) }}">
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-1">
                        标题 <span class="text-red-400">*</span>
                        <span class="text-gray-500 font-normal ml-1">(最多100字)</span>
                    </label>
                    <input id="title" type="text" name="title" value="{{ old('title', $thread->title) }}" required
                        data-maxlength="100"
                        class="mc-input w-full px-4 py-2 rounded-lg text-base @error('title') input-error @enderror">
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-300 mb-1">
                        内容 <span class="text-red-400">*</span>
                    </label>
                    <textarea id="body" name="body" rows="12" required data-maxlength="10000"
                        class="mc-input w-full px-4 py-3 rounded-lg text-base leading-relaxed @error('body') input-error @enderror">{{ old('body', $thread->body) }}</textarea>
                    @error('body')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between pt-4 gap-3">
                    <a href="{{ route('threads.show', $thread->slug) }}" class="text-center text-gray-400 hover:text-gray-300 transition px-4 py-2 rounded-lg border border-gray-700 hover:border-gray-500">
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
