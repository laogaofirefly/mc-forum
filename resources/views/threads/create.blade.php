@extends('layouts.app')

@section('title', '发布新帖')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mc-card rounded-lg p-6">
        <h2 class="text-xl font-bold text-white mb-6">发布新帖</h2>
        <form method="POST" action="{{ route('threads.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-300 mb-1">选择板块</label>
                    <select id="category_id" name="category_id" required
                        class="mc-input w-full px-4 py-2 rounded-lg">
                        <option value="">请选择板块</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $selectedCategory ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->icon ?? '📁' }} {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-1">标题</label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" required
                        class="mc-input w-full px-4 py-2 rounded-lg" placeholder="请输入帖子标题">
                </div>
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-300 mb-1">内容</label>
                    <textarea id="body" name="body" rows="12" required
                        class="mc-input w-full px-4 py-2 rounded-lg" placeholder="请输入帖子内容">{{ old('body') }}</textarea>
                </div>
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ URL::previous() }}" class="text-gray-400 hover:text-gray-300 transition">
                        ← 返回
                    </a>
                    <button type="submit" class="mc-button text-white px-6 py-2 rounded-lg font-bold">
                        发布帖子
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
