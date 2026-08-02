/**
 * Markdown 编辑器
 *
 * 自动绑定页面上所有 .markdown-editor 元素，提供：
 *  - 工具栏：加粗/斜体/标题/引用/代码/列表/链接/图片上传
 *  - 快捷键：Ctrl+B 加粗、Ctrl+I 斜体、Ctrl+K 链接
 *  - 图片上传：点击图片按钮选图，或直接粘贴图片、拖拽图片到编辑器
 *  - 上传成功后自动在光标位置插入 ![](url) 语法
 */
(function() {
    'use strict';

    function initEditor(editor) {
        if (editor.dataset.mdBound) return;
        editor.dataset.mdBound = '1';

        var textarea = editor.querySelector('textarea');
        var fileInput = editor.querySelector('.md-file-input');
        var tip = editor.querySelector('.md-upload-tip');
        var uploadUrl = editor.dataset.uploadUrl;
        var csrf = editor.dataset.csrf;

        // ===== 工具栏按钮 =====
        var buttons = editor.querySelectorAll('.md-btn[data-md]');
        buttons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var type = btn.dataset.md;
                if (type === 'image') {
                    fileInput.click();
                } else {
                    applyFormat(type);
                }
            });
        });

        // ===== 快捷键 =====
        textarea.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                var key = e.key.toLowerCase();
                if (key === 'b') { e.preventDefault(); applyFormat('bold'); }
                else if (key === 'i') { e.preventDefault(); applyFormat('italic'); }
                else if (key === 'k') { e.preventDefault(); applyFormat('link'); }
            }
        });

        // ===== 粘贴图片 =====
        textarea.addEventListener('paste', function(e) {
            var items = e.clipboardData && e.clipboardData.items;
            if (!items) return;
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image/') === 0) {
                    e.preventDefault();
                    var file = items[i].getAsFile();
                    if (file) uploadFile(file);
                    return;
                }
            }
        });

        // ===== 拖拽图片 =====
        textarea.addEventListener('dragover', function(e) {
            e.preventDefault();
            textarea.style.borderColor = '#10b981';
        });
        textarea.addEventListener('dragleave', function() {
            textarea.style.borderColor = '';
        });
        textarea.addEventListener('drop', function(e) {
            textarea.style.borderColor = '';
            if (!e.dataTransfer || !e.dataTransfer.files) return;
            for (var i = 0; i < e.dataTransfer.files.length; i++) {
                if (e.dataTransfer.files[i].type.indexOf('image/') === 0) {
                    e.preventDefault();
                    uploadFile(e.dataTransfer.files[i]);
                }
            }
        });

        // ===== 文件选择 =====
        fileInput.addEventListener('change', function() {
            if (fileInput.files && fileInput.files[0]) {
                uploadFile(fileInput.files[0]);
                fileInput.value = '';
            }
        });

        // ===== 工具栏应用格式 =====
        function applyFormat(type) {
            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            var text = textarea.value;
            var selected = text.substring(start, end);
            var before = text.substring(0, start);
            var after = text.substring(end);
            var insertion = '';
            var newCursorStart = start;
            var newCursorEnd = start;

            switch (type) {
                case 'bold':
                    insertion = '**' + (selected || '加粗文字') + '**';
                    newCursorStart = start + 2;
                    newCursorEnd = newCursorStart + (selected || '加粗文字').length;
                    break;
                case 'italic':
                    insertion = '*' + (selected || '斜体文字') + '*';
                    newCursorStart = start + 1;
                    newCursorEnd = newCursorStart + (selected || '斜体文字').length;
                    break;
                case 'h2':
                    insertion = '## ' + (selected || '标题');
                    newCursorStart = newCursorEnd = start + insertion.length;
                    if (!selected) { newCursorStart = start + 3; newCursorEnd = newCursorStart + 2; }
                    break;
                case 'quote':
                    insertion = '> ' + (selected || '引用内容');
                    newCursorStart = newCursorEnd = start + insertion.length;
                    break;
                case 'code':
                    insertion = '`' + (selected || '代码') + '`';
                    newCursorStart = start + 1;
                    newCursorEnd = newCursorStart + (selected || '代码').length;
                    break;
                case 'codeblock':
                    insertion = '\n```\n' + (selected || '代码块') + '\n```\n';
                    newCursorStart = newCursorEnd = start + insertion.length;
                    break;
                case 'ul':
                    insertion = '- ' + (selected || '列表项');
                    newCursorStart = newCursorEnd = start + insertion.length;
                    break;
                case 'link':
                    var url = prompt('请输入链接地址：', 'https://');
                    if (!url) return;
                    insertion = '[' + (selected || '链接文字') + '](' + url + ')';
                    newCursorStart = start + 1;
                    newCursorEnd = newCursorStart + (selected || '链接文字').length;
                    break;
                default:
                    return;
            }

            textarea.value = before + insertion + after;
            textarea.focus();
            textarea.setSelectionRange(newCursorStart, newCursorEnd);
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }

        // ===== 上传图片 =====
        function uploadFile(file) {
            if (!file) return;

            // 类型检查
            var allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (allowed.indexOf(file.type) === -1) {
                showTip('仅支持 JPG / PNG / WEBP / GIF 格式', 'error');
                return;
            }
            // 大小检查
            if (file.size > 5 * 1024 * 1024) {
                showTip('图片大小不能超过 5MB', 'error');
                return;
            }

            var formData = new FormData();
            formData.append('file', file);

            // 设置上传中状态
            var imageBtn = editor.querySelector('.md-btn-image');
            if (imageBtn) imageBtn.classList.add('uploading');
            showTip('正在上传 ' + file.name + ' ...', 'info');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onload = function() {
                if (imageBtn) imageBtn.classList.remove('uploading');
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.ok && res.markdown) {
                            insertAtCursor(res.markdown);
                            showTip('上传成功', 'success');
                            setTimeout(function() { showTip('', 'hide'); }, 1500);
                        } else {
                            showTip(res.message || '上传失败', 'error');
                        }
                    } catch (e) {
                        showTip('解析响应失败', 'error');
                    }
                } else {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        showTip(res.message || '上传失败 (' + xhr.status + ')', 'error');
                    } catch (e) {
                        showTip('上传失败 (' + xhr.status + ')', 'error');
                    }
                }
            };
            xhr.onerror = function() {
                if (imageBtn) imageBtn.classList.remove('uploading');
                showTip('网络错误，上传失败', 'error');
            };
            xhr.send(formData);
        }

        function insertAtCursor(text) {
            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            var before = textarea.value.substring(0, start);
            var after = textarea.value.substring(end);

            // 如果不在行首，前面加换行
            if (before.length > 0 && before[before.length - 1] !== '\n') {
                text = '\n' + text;
            }
            // 后面补换行
            if (after.length > 0 && after[0] !== '\n') {
                text = text + '\n';
            }

            textarea.value = before + text + after;
            var newPos = start + text.length;
            textarea.focus();
            textarea.setSelectionRange(newPos, newPos);
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }

        function showTip(msg, type) {
            if (!tip) return;
            if (type === 'hide') {
                tip.classList.add('hidden');
                return;
            }
            tip.classList.remove('hidden');
            tip.textContent = msg;
            tip.style.color = type === 'error' ? '#ef4444' : (type === 'success' ? '#10b981' : '#64748b');
        }
    }

    // 绑定页面上所有编辑器
    function initAll() {
        document.querySelectorAll('.markdown-editor:not([data-md-bound])').forEach(initEditor);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
