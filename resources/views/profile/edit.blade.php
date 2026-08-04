@extends('layouts.app')

@section('title', '编辑资料')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card p-6">
        <h2 class="page-title text-slate-900 mb-6">编辑个人资料</h2>

        {{-- 头像区域 --}}
        <div class="flex items-center space-x-4 mb-6 pb-6 border-b border-slate-100">
            <img src="{{ auth()->user()->getAvatarUrl() }}" alt="当前头像"
                class="w-20 h-20 rounded-full ring-2 ring-slate-100 object-cover bg-white" id="currentAvatar">
            <div>
                <p class="text-sm font-medium text-slate-700 mb-1">头像</p>
                <p class="text-xs text-slate-500 mb-2">支持 JPG/PNG/WEBP/GIF，最大 2MB，可裁剪</p>
                <form id="avatarForm" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                    <input type="hidden" name="crop_x" id="cropX" value="0">
                    <input type="hidden" name="crop_y" id="cropY" value="0">
                    <input type="hidden" name="crop_scale" id="cropScale" value="1">
                    <input type="hidden" name="crop_size" id="cropSize" value="300">
                    <button type="button" id="avatarBtn" class="btn-secondary text-sm">
                        @include('layouts.partials.icons', ['name' => 'camera', 'class' => 'w-4 h-4']) 更换头像
                    </button>
                </form>
            </div>
        </div>

        {{-- 头像裁剪弹窗 --}}
        <div id="cropModal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900 text-lg">裁剪头像</h3>
                    <button type="button" id="cropCloseBtn" class="text-slate-400 hover:text-slate-700 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-5">
                    <p class="text-xs text-slate-500 mb-3">拖拽图片调整位置，使用滑块缩放</p>
                    <div id="cropContainer" class="relative w-full aspect-square rounded-full overflow-hidden bg-slate-200 cursor-grab active:cursor-grabbing select-none">
                        <img id="cropImage" src="" alt="" class="absolute max-w-none" draggable="false">
                        <div class="absolute inset-0 rounded-full border-2 border-white/60 shadow-[0_0_0_9999px_rgba(0,0,0,0.35)] pointer-events-none"></div>
                    </div>
                    <div class="flex items-center gap-3 mt-4">
                        <span class="text-xs text-slate-500 flex-shrink-0">缩放</span>
                        <input type="range" id="cropZoom" min="1" max="3" step="0.01" value="1" class="flex-1 accent-primary-500">
                        <button type="button" id="cropResetBtn" class="text-xs text-primary-600 hover:text-primary-700 px-2 py-1 rounded hover:bg-primary-50 flex-shrink-0">重置</button>
                    </div>
                </div>
                <div class="flex gap-3 px-5 py-4 border-t border-slate-200 bg-slate-50">
                    <button type="button" id="cropCancelBtn" class="btn-secondary flex-1 py-2 text-sm">取消</button>
                    <button type="button" id="cropConfirmBtn" class="btn-primary flex-1 py-2 text-sm">确认裁剪</button>
                </div>
            </div>
        </div>

        
        {{-- 聊天背景图区域 --}}
        <div class="flex items-center space-x-4 mb-6 pb-6 border-b border-slate-100">
            @php $chatBgUrl = auth()->user()->getChatBgUrl(); @endphp
            <div class="w-40 h-24 rounded-lg bg-cover bg-center shadow-inner ring-1 ring-slate-200 overflow-hidden flex-shrink-0" style="@if($chatBgUrl)background-image: url('{{ $chatBgUrl }}')@else background: linear-gradient(135deg, #e2e8f0, #cbd5e1) @endif"></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-700 mb-1">@include('layouts.partials.icons', ['name' => 'palette', 'class' => 'w-4 h-4']) 游戏聊天背景图</p>
                <p class="text-xs text-slate-500 mb-2">支持 JPG/PNG/WEBP，最大 5MB。不设置则使用默认背景。</p>
                <div class="flex items-center gap-2 flex-wrap">
                    <form id="chatBgForm" action="{{ route('profile.chat-bg') }}" method="POST" enctype="multipart/form-data" class="contents">
                        @csrf
                        <input type="file" id="chatBgInput" name="chat_bg" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="document.getElementById('chatBgForm').submit()">
                        <button type="button" id="chatBgBtn" class="btn-secondary text-sm" onclick="document.getElementById('chatBgInput').click()">
                            @include('layouts.partials.icons', ['name' => 'image', 'class' => 'w-4 h-4'])选择背景图
                        </button>
                    </form>
                    @if(auth()->user()->chat_bg)
                    <form action="{{ route('profile.chat-bg.remove') }}" method="POST" class="contents" onsubmit="return confirm('确定要移除聊天背景图？')">
                        @csrf
                        <button type="submit" class="btn-secondary text-sm text-red-600 border-red-200 hover:bg-red-50">
                            @include('layouts.partials.icons', ['name' => 'x', 'class' => 'w-4 h-4'])移除
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

<form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">用户名</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="input w-full">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">邮箱</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="input w-full">
                </div>
                <div>
                    <label for="bio" class="block text-sm font-medium text-slate-700 mb-1">个人简介</label>
                    <textarea id="bio" name="bio" rows="4"
                        class="input w-full" placeholder="介绍一下自己...">{{ old('bio', $user->bio) }}</textarea>
                    <p class="char-counter" id="bioCounter"></p>
                </div>
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('profile.show', $user) }}" class="btn-secondary">
                        ← 返回
                    </a>
                    <button type="submit" class="btn-primary">
                        保存修改
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var btn = document.getElementById('avatarBtn');
    var input = document.getElementById('avatarInput');
    var form = document.getElementById('avatarForm');
    if (!btn || !input || !form) return;

    btn.addEventListener('click', function(e) {
        e.preventDefault();
        input.click();
    });

    // ==== 裁剪功能 ====
    var cropModal = document.getElementById('cropModal');
    var cropImage = document.getElementById('cropImage');
    var cropContainer = document.getElementById('cropContainer');
    var cropZoom = document.getElementById('cropZoom');
    var cropX = document.getElementById('cropX');
    var cropY = document.getElementById('cropY');
    var cropScale = document.getElementById('cropScale');
    var cropSize = document.getElementById('cropSize');
    var currentAvatar = document.getElementById('currentAvatar');

    var imgNaturalW = 0, imgNaturalH = 0;
    var scale = 1, offsetX = 0, offsetY = 0;
    var isDragging = false, dragStartX = 0, dragStartY = 0, startOffsetX = 0, startOffsetY = 0;
    var containerSize = 0;

    function openCrop(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            cropImage.src = e.target.result;
            cropImage.onload = function() {
                imgNaturalW = cropImage.naturalWidth;
                imgNaturalH = cropImage.naturalHeight;
                containerSize = cropContainer.clientWidth;
                resetCrop();
                cropModal.classList.remove('hidden');
                cropModal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            };
        };
        reader.readAsDataURL(file);
    }

    function resetCrop() {
        scale = 1;
        cropZoom.value = 1;
        // 居中显示
        var minDim = Math.min(imgNaturalW, imgNaturalH);
        var displayW = containerSize;
        var displayH = (imgNaturalH / imgNaturalW) * displayW;
        if (imgNaturalW < imgNaturalH) {
            displayH = containerSize;
            displayW = (imgNaturalW / imgNaturalH) * displayW;
        }
        offsetX = (containerSize - displayW) / 2;
        offsetY = (containerSize - displayH) / 2;
        updateImage();
    }

    function updateImage() {
        var minDim = Math.min(imgNaturalW, imgNaturalH);
        var baseW = imgNaturalW > imgNaturalH ? (containerSize * imgNaturalW / minDim) : (containerSize * scale);
        var baseH = imgNaturalH > imgNaturalW ? (containerSize * imgNaturalH / minDim) : (containerSize * scale);

        if (imgNaturalW > imgNaturalH) {
            baseW = containerSize * scale * (imgNaturalW / minDim);
            baseH = containerSize * scale;
        } else {
            baseW = containerSize * scale;
            baseH = containerSize * scale * (imgNaturalH / minDim);
        }

        // 限制偏移范围
        var maxX = 0;
        var minX = containerSize - baseW;
        var maxY = 0;
        var minY = containerSize - baseH;
        offsetX = Math.max(minX, Math.min(maxX, offsetX));
        offsetY = Math.max(minY, Math.min(maxY, offsetY));

        cropImage.style.width = baseW + 'px';
        cropImage.style.height = baseH + 'px';
        cropImage.style.left = offsetX + 'px';
        cropImage.style.top = offsetY + 'px';

        // 更新裁剪参数
        var cx = -offsetX / baseW;
        var cy = -offsetY / baseH;
        cropX.value = cx.toFixed(4);
        cropY.value = cy.toFixed(4);
        cropScale.value = scale.toFixed(2);
    }

    function closeCrop() {
        cropModal.classList.add('hidden');
        cropModal.classList.remove('flex');
        document.body.style.overflow = '';
        cropImage.src = '';
    }

    input.addEventListener('change', function() {
        if (!this.files || !this.files[0]) return;
        var file = this.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('图片大小不能超过 2MB');
            this.value = '';
            return;
        }
        openCrop(file);
    });

    // 缩放滑块
    cropZoom.addEventListener('input', function() {
        scale = parseFloat(this.value);
        updateImage();
    });

    // 重置
    document.getElementById('cropResetBtn').addEventListener('click', resetCrop);

    // 拖拽
    cropContainer.addEventListener('mousedown', function(e) {
        isDragging = true;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        startOffsetX = offsetX;
        startOffsetY = offsetY;
        e.preventDefault();
    });

    // 触屏拖拽
    cropContainer.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            isDragging = true;
            dragStartX = e.touches[0].clientX;
            dragStartY = e.touches[0].clientY;
            startOffsetX = offsetX;
            startOffsetY = offsetY;
        }
    });

    window.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        offsetX = startOffsetX + (e.clientX - dragStartX);
        offsetY = startOffsetY + (e.clientY - dragStartY);
        updateImage();
    });

    window.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        offsetX = startOffsetX + (e.touches[0].clientX - dragStartX);
        offsetY = startOffsetY + (e.touches[0].clientY - dragStartY);
        updateImage();
    });

    window.addEventListener('mouseup', function() { isDragging = false; });
    window.addEventListener('touchend', function() { isDragging = false; });

    // 确认裁剪
    document.getElementById('cropConfirmBtn').addEventListener('click', function() {
        // 更新预览
        if (currentAvatar) {
            // 创建一个临时canvas来预览
            var canvas = document.createElement('canvas');
            canvas.width = 200;
            canvas.height = 200;
            var ctx = canvas.getContext('2d');
            var size = Math.min(cropImage.naturalWidth, cropImage.naturalHeight);
            var sx = parseFloat(cropX.value) * cropImage.naturalWidth;
            var sy = parseFloat(cropY.value) * cropImage.naturalHeight;
            ctx.drawImage(cropImage, sx, sy, size / scale, size / scale, 0, 0, 200, 200);
            currentAvatar.src = canvas.toDataURL();
        }
        closeCrop();
        form.submit();
    });

    // 取消
    document.getElementById('cropCancelBtn').addEventListener('click', function() {
        input.value = '';
        closeCrop();
    });
    document.getElementById('cropCloseBtn').addEventListener('click', function() {
        input.value = '';
        closeCrop();
    });

    // 简介字数统计
    var bio = document.getElementById('bio');
    var counter = document.getElementById('bioCounter');
    if (bio && counter) {
        function update() {
            var len = bio.value.length;
            counter.textContent = len + ' / 500';
            counter.className = 'char-counter' + (len > 500 ? ' error' : len > 400 ? ' warning' : '');
        }
        bio.addEventListener('input', update);
        update();
    }
})();
</script>
@endsection
