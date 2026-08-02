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
                    <div id="previewContent" class="card bg-slate-50 p-4 text-slate-700 whitespace-pre-wrap break-words min-h-[200px] text-sm leading-relaxed">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="body" class="block text-sm font-medium text-slate-700">
                            内容 <span class="text-red-500">*</span>
                            <span class="text-slate-500 font-normal ml-1">(支持换行)</span>
                        </label>
                        <button type="button" id="insertTipBtn" class="text-xs text-slate-500 hover:text-primary-600">💡 格式提示</button>
                    </div>
                    <textarea id="body" name="body" rows="10" required data-maxlength="10000"
                        class="input w-full px-4 py-3 text-base leading-relaxed @error('body') input-error @enderror"
                        placeholder="分享你的想法、建筑、技术、问题...

支持换行分段，内容清晰易读！">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between pt-4 gap-3">
                    <a href="{{ url()->previous() ?: route('home') }}" class="btn-secondary text-center px-4 py-2">
                        ← 返回
                    </a>
                    <button type="submit" class="btn-primary px-6 py-3 text-base">
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
                <li>📄 <b>正文</b>：内容清晰，适当分段方便阅读</li>
                <li>🖼️ <b>图片</b>：暂不支持图片上传，可用文字描述</li>
                <li>⚠️ <b>规则</b>：禁止发布违规、广告、恶意内容</li>
            </ul>
            <button type="button" id="closeTipBtn" class="btn-primary w-full py-2 mt-4">知道了</button>
        </div>
    </div>
</div>

<script>
    // 预览功能
    const previewBtn = document.getElementById('previewBtn');
    const previewArea = document.getElementById('previewArea');
    const previewContent = document.getElementById('previewContent');
    const titleInput = document.getElementById('title');
    const bodyInput = document.getElementById('body');
    let previewOn = false;

    function updatePreview() {
        if (!previewOn) return;
        const t = titleInput.value.trim() ? `<h3 class="text-lg font-bold text-primary-600 mb-3">${escapeHtml(titleInput.value)}</h3>` : '';
        const b = escapeHtml(bodyInput.value);
        previewContent.innerHTML = t + (b || '<span class="text-slate-400">（内容为空）</span>');
    }

    function escapeHtml(s) {
        const div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML.replace(/\n/g, '<br>');
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
