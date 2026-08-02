@php
    // 可复用的 Markdown 编辑器组件
    // 用法：@include('partials.markdown-editor', ['name' => 'body', 'value' => $old, 'rows' => 10, 'placeholder' => '...', 'maxlength' => 10000])
    $name = $name ?? 'body';
    $value = $value ?? old($name);
    $rows = $rows ?? 10;
    $placeholder = $placeholder ?? '支持 Markdown 语法，可插入图片...';
    $maxlength = $maxlength ?? 10000;
    $errorKey = $errorKey ?? $name;
@endphp

<div class="markdown-editor" data-upload-url="{{ route('upload-image') }}" data-csrf="{{ csrf_token() }}">
    {{-- 工具栏 --}}
    <div class="flex flex-wrap items-center gap-1 mb-1.5 px-1">
        <button type="button" class="md-btn" data-md="bold" title="加粗 (Ctrl+B)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6V4z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6v-8z"/></svg>
        </button>
        <button type="button" class="md-btn" data-md="italic" title="斜体 (Ctrl+I)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 4h-6M14 20H8M15 4L9 20"/></svg>
        </button>
        <button type="button" class="md-btn" data-md="h2" title="标题">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6v12M12 6v12M4 12h8M16 6l2 0M18 6l-2 6 4 0"/></svg>
        </button>
        <span class="md-divider"></span>
        <button type="button" class="md-btn" data-md="quote" title="引用">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-8h4V4H4v8h3l-1 8zM21 12h-4l1 8h4v-8h-1z"/></svg>
        </button>
        <button type="button" class="md-btn" data-md="code" title="行内代码">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        </button>
        <button type="button" class="md-btn" data-md="codeblock" title="代码块">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4zM8 10l2 2-2 2M14 14h2"/></svg>
        </button>
        <button type="button" class="md-btn" data-md="ul" title="无序列表">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h.01M4 12h.01M4 18h.01M8 6h13M8 12h13M8 18h13"/></svg>
        </button>
        <button type="button" class="md-btn" data-md="link" title="链接 (Ctrl+K)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        </button>
        <button type="button" class="md-btn md-btn-image" data-md="image" title="上传图片">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </button>

        <span class="flex-1"></span>
        <span class="text-xs text-slate-400">支持 Markdown</span>
    </div>

    {{-- 隐藏的文件选择 --}}
    <input type="file" class="md-file-input hidden" accept="image/jpeg,image/png,image/webp,image/gif">

    {{-- 文本框 --}}
    <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows }}" required
        data-maxlength="{{ $maxlength }}"
        class="input w-full px-4 py-3 text-base leading-relaxed @error($errorKey) input-error @enderror"
        placeholder="{{ $placeholder }}">{{ $value }}</textarea>

    @error($errorKey)
        <p class="form-error">{{ $message }}</p>
    @enderror

    {{-- 上传提示 --}}
    <div class="md-upload-tip text-xs text-slate-400 mt-1 hidden"></div>
</div>

<style>
    .md-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        color: #475569;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .md-btn:hover { background-color: #f1f5f9; color: #059669; }
    .md-btn:active { transform: scale(0.95); }
    .md-btn-image { position: relative; }
    .md-btn.uploading { pointer-events: none; opacity: 0.6; }
    .md-divider { display: inline-block; width: 1px; height: 20px; background: #e2e8f0; margin: 0 4px; }
</style>
