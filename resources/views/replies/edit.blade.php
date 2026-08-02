@extends('layouts.app')

@section('title', '编辑回复')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card p-5 sm:p-6">
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-6">编辑回复</h2>
        <form method="POST" action="{{ route('replies.update', $reply) }}">
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <div>
                    <label for="body" class="block text-sm font-medium text-slate-700 mb-1">
                        回复内容 <span class="text-red-500">*</span>
                        <span class="text-slate-500 font-normal ml-1">(支持 Markdown 和图片)</span>
                    </label>
                    @include('partials.markdown-editor', [
                        'name' => 'body',
                        'value' => old('body', $reply->body),
                        'rows' => 8,
                        'placeholder' => '支持 Markdown 语法，可插入图片...',
                        'maxlength' => 5000,
                    ])
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between pt-4 gap-3">
                    <a href="{{ route('threads.show', $reply->thread->slug) }}" class="btn-secondary text-center">
                        ← 返回帖子
                    </a>
                    <button type="submit" class="btn-primary text-sm">
                        保存修改
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="/js/markdown-editor.js"></script>
@endsection
