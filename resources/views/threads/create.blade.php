@extends('layouts.app')

@section('title', '发布新帖')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card p-5 sm:p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900">发布新帖</h2>
            <button type="button" id="previewBtn" class="text-sm text-primary-600 hover:text-primary-700 px-3 py-2 rounded-md border border-primary-200 hover:border-primary-400 transition hidden sm:inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                预览
            </button>
        </div>

        <form method="POST" action="{{ route('threads.store') }}" id="threadForm" novalidate>
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-1">
                        标题 <span class="text-red-500">*</span>
                        <span class="text-slate-500 font-normal ml-1">(最多100字)</span>
                    </label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" required
                        autocomplete="off" inputmode="text" data-maxlength="100"
                        class="input w-full px-4 py-2 text-base @error('title') input-error @enderror"
                        placeholder="请输入帖子标题，简洁明了地说明主题">
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div id="previewArea" class="hidden">
                    <label class="block text-sm font-medium text-slate-700 mb-1">预览</label>
                    <div id="previewContent" class="card bg-slate-50 p-4 prose prose-slate prose-sm max-w-none min-h-[200px]">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="body" class="block text-sm font-medium text-slate-700">
                            内容 <span class="text-red-500">*</span>
                            <span class="text-slate-500 font-normal ml-1">(支持 Markdown 和图片)</span>
                        </label>
                        <button type="button" id="insertTipBtn" class="text-xs text-slate-500 hover:text-primary-600">💡 格式提示</button>
                    </div>
                    @include('partials.markdown-editor', [
                        'name' => 'body',
                        'value' => old('body'),
                        'rows' => 12,
                        'placeholder' => "分享你的想法、建筑、技术、问题...\n\n支持 Markdown 语法，可插入图片！",
                        'maxlength' => 10000,
                    ])
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between pt-4 gap-3">
                    <a href="{{ url()->previous() ?: route('home') }}" class="btn-secondary text-center px-4 py-2">
                        ← 返回
                    </a>
                    <button type="submit" class="btn-primary px-6 py-3 text-sm">
                        📝 发布帖子
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="tipModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl p-5 max-w-md w-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">发帖格式提示</h3>
                <button type="button" id="closeTip" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <ul class="space-y-2 text-sm text-slate-700">
                <li>📝 <b>标题</b>：简洁明了，突出主题（最多100字）</li>
                <li>📄 <b>正文</b>：支持 Markdown 语法，自动格式化</li>
                <li>🖼️ <b>图片</b>：点击工具栏图片按钮上传，或直接粘贴/拖拽图片</li>
                <li>⌨️ <b>快捷键</b>：Ctrl+B 加粗、Ctrl+I 斜体、Ctrl+K 链接</li>
                <li>⚠️ <b>规则</b>：禁止发布违规、广告、恶意内容</li>
            </ul>
            <button type="button" id="closeTipBtn" class="btn-primary w-full py-2 mt-4">知道了</button>
        </div>
    </div>
</div>

<script src="/js/markdown-editor.js"></script>
<script>
    // 预览功能：实时调用后端接口渲染 Markdown
    const previewBtn = document.getElementById('previewBtn');
    const previewArea = document.getElementById('previewArea');
    const previewContent = document.getElementById('previewContent');
    const titleInput = document.getElementById('title');
    const bodyInput = document.getElementById('body');
    let previewOn = false;
    let previewTimer = null;

    function updatePreview() {
        if (!previewOn) return;
        clearTimeout(previewTimer);
        previewTimer = setTimeout(function() {
            const body = bodyInput.value;
            if (!body.trim()) {
                previewContent.innerHTML = '<span class="text-slate-400">（内容为空）</span>';
                return;
            }
            // 简单本地预览：换行 + 基础 markdown（避免频繁请求后端）
            previewContent.innerHTML = renderMarkdownLocal(body);
        }, 200);
    }

    // 极简本地 Markdown 预览（仅用于编辑时预览，正式展示由后端 league/commonmark 渲染）
    function renderMarkdownLocal(text) {
        let html = text;
        // 转义 HTML
        html = html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        // 图片 ![alt](url)
        html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img alt="$1" src="$2" class="my-2 rounded-lg max-w-full" loading="lazy">');
        // 链接 [text](url)
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-primary-600 hover:underline" target="_blank" rel="noopener">$1</a>');
        // 标题
        html = html.replace(/^### (.+)$/gm, '<h3 class="text-base font-bold mt-3 mb-1">$1</h3>');
        html = html.replace(/^## (.+)$/gm, '<h2 class="text-lg font-bold mt-3 mb-1">$1</h2>');
        html = html.replace(/^# (.+)$/gm, '<h1 class="text-xl font-bold mt-3 mb-1">$1</h1>');
        // 引用
        html = html.replace(/^&gt; (.+)$/gm, '<blockquote class="border-l-4 border-primary-300 pl-3 text-slate-600 my-2">$1</blockquote>');
        // 代码块
        html = html.replace(/```[\s\S]*?```/g, function(m) {
            return '<pre class="bg-slate-800 text-slate-100 p-3 rounded-lg my-2 overflow-x-auto text-xs"><code>' + m.slice(3, -3).replace(/^\w*\n/, '') + '</code></pre>';
        });
        // 行内代码
        html = html.replace(/`([^`]+)`/g, '<code class="bg-slate-100 text-pink-600 px-1.5 py-0.5 rounded text-sm">$1</code>');
        // 粗体
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        // 斜体
        html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
        // 列表
        html = html.replace(/^- (.+)$/gm, '<li class="ml-4 list-disc">$1</li>');
        html = html.replace(/(<li[^>]*>.*<\/li>\n?)+/g, function(m) { return '<ul class="my-1">' + m + '</ul>'; });
        // 换行
        html = html.replace(/\n/g, '<br>');
        return html;
    }

    if (previewBtn) {
        previewBtn.classList.remove('hidden');
        previewBtn.addEventListener('click', function() {
            previewOn = !previewOn;
            previewArea.classList.toggle('hidden', !previewOn);
            previewBtn.classList.toggle('bg-primary-50', previewOn);
            if (previewOn) {
                updatePreview();
                previewBtn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>编辑';
            } else {
                previewBtn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>预览';
            }
        });
        titleInput.addEventListener('input', updatePreview);
        bodyInput.addEventListener('input', updatePreview);
    }

    // 发帖提示弹窗
    const tipBtn = document.getElementById('insertTipBtn');
    const tipModal = document.getElementById('tipModal');
    const closeTip = document.getElementById('closeTip');
    const closeTipBtn = document.getElementById('closeTipBtn');
    function openTip() { tipModal.classList.remove('hidden'); tipModal.classList.add('flex'); }
    function hideTip() { tipModal.classList.add('hidden'); tipModal.classList.remove('flex'); }
    if (tipBtn) tipBtn.addEventListener('click', openTip);
    if (closeTip) closeTip.addEventListener('click', hideTip);
    if (closeTipBtn) closeTipBtn.addEventListener('click', hideTip);
    tipModal.addEventListener('click', function(e) { if (e.target === tipModal) hideTip(); });

    // 未保存提醒
    const form = document.getElementById('threadForm');
    let touched = false;
    [titleInput, bodyInput].forEach(function(el) {
        if (el) el.addEventListener('input', function() { touched = true; });
    });
    window.addEventListener('beforeunload', function(e) {
        if (touched && !form.dataset.submitted) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    form.addEventListener('submit', function() { form.dataset.submitted = '1'; });
</script>
@endsection
