@extends('layouts.app')

@section('title', $user->name . ' 的资料')

@section('content')
<div class="space-y-5">
    {{-- 个人信息卡片 --}}
    <div class="card overflow-hidden">
        {{-- 顶部横幅 --}}
        <div style="height: 96px; background: linear-gradient(135deg, #34d399 0%, #059669 100%);"></div>

        <div class="px-5 sm:px-8 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:space-x-6 -mt-12 sm:-mt-14">
                {{-- 头像区域 --}}
                <div class="flex flex-col items-center sm:items-start">
                    <div class="relative">
                        <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}"
                            class="w-24 h-24 sm:w-28 sm:h-28 rounded-full ring-4 ring-white shadow-lg bg-white object-cover">
                        @auth
                            @if(auth()->id() === $user->id)
                                <button type="button" id="avatarBtn"
                                    class="absolute bottom-1 right-1 w-8 h-8 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-md transition"
                                    title="更换头像">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                                <form id="avatarForm" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="hidden">
                                    @csrf
                                    <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                                    <input type="hidden" name="crop_x" id="cropX" value="0">
                                    <input type="hidden" name="crop_y" id="cropY" value="0">
                                    <input type="hidden" name="crop_scale" id="cropScale" value="1">
                                    <input type="hidden" name="crop_size" id="cropSize" value="300">
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>

                {{-- 用户信息 --}}
                <div class="flex-1 mt-3 sm:mt-0 sm:pb-2 text-center sm:text-left">
                    <div class="flex items-center justify-center sm:justify-start space-x-2 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
                        @if($user->isAdmin())
                            <span class="badge bg-amber-100 text-amber-800">管理员</span>
                        @endif
                        @if($user->mc_verified)
                            <span class="badge bg-primary-100 text-primary-700 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                MC已验证
                            </span>
                        @endif
                    </div>
                    @if($user->mc_username)
                        <p class="text-primary-600 text-sm mt-1">MC ID: {{ $user->mc_username }}</p>
                    @endif
                </div>
            </div>

            {{-- 个人简介 --}}
            <div class="mt-4">
                @if($user->bio)
                    <p class="text-slate-700 leading-relaxed">{{ $user->bio }}</p>
                @else
                    <p class="text-slate-400 italic">这个人很懒，什么都没留下...</p>
                @endif
            </div>

            {{-- 统计数据 --}}
            <div class="grid grid-cols-3 gap-3 mt-5">
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <div class="text-xl font-bold text-primary-600">{{ $user->threads()->count() }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">帖子</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <div class="text-xl font-bold text-primary-600">{{ $user->replies()->count() }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">回复</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <div class="text-xl font-bold text-primary-600">{{ $user->created_at->format('Y-m-d') }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">注册于</div>
                </div>
            </div>

            {{-- 操作按钮 --}}
            @auth
                @if(auth()->id() === $user->id)
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn-secondary text-sm">
                            @include('layouts.partials.icons', ['name' => 'pencil', 'class' => 'w-4 h-4']) 编辑资料
                        </a>
                        <a href="{{ route('profile.mc-bind') }}" class="btn-primary text-sm">
                            @include('layouts.partials.icons', ['name' => 'link', 'class' => 'w-4 h-4']) 绑定 MC 账号
                        </a>
                    </div>
                @endif
            @endauth
        </div>
    </div>

    {{-- 最近帖子 --}}
    <div>
        <h2 class="text-base font-bold text-slate-900 mb-3 flex items-center">
            @include('layouts.partials.icons', ['name' => 'document', 'class' => 'w-4 h-4 mr-2'])最近发布的帖子
        </h2>
        <div class="space-y-3">
            @foreach($threads as $thread)
                @include('partials.thread-card', ['thread' => $thread])
            @endforeach
            @if($threads->isEmpty())
                <div class="card p-6 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm">暂无帖子</p>
                </div>
            @endif
        </div>
    </div>

</div>

@auth
    @if(auth()->id() === $user->id)
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
                var baseW, baseH;
                if (imgNaturalW > imgNaturalH) {
                    baseW = containerSize * scale * (imgNaturalW / minDim);
                    baseH = containerSize * scale;
                } else {
                    baseW = containerSize * scale;
                    baseH = containerSize * scale * (imgNaturalH / minDim);
                }
                var maxX = 0, minX = containerSize - baseW;
                var maxY = 0, minY = containerSize - baseH;
                offsetX = Math.max(minX, Math.min(maxX, offsetX));
                offsetY = Math.max(minY, Math.min(maxY, offsetY));

                cropImage.style.width = baseW + 'px';
                cropImage.style.height = baseH + 'px';
                cropImage.style.left = offsetX + 'px';
                cropImage.style.top = offsetY + 'px';

                cropX.value = (-offsetX / baseW).toFixed(4);
                cropY.value = (-offsetY / baseH).toFixed(4);
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

            cropZoom.addEventListener('input', function() {
                scale = parseFloat(this.value);
                updateImage();
            });

            document.getElementById('cropResetBtn').addEventListener('click', resetCrop);

            cropContainer.addEventListener('mousedown', function(e) {
                isDragging = true;
                dragStartX = e.clientX; dragStartY = e.clientY;
                startOffsetX = offsetX; startOffsetY = offsetY;
                e.preventDefault();
            });
            cropContainer.addEventListener('touchstart', function(e) {
                if (e.touches.length === 1) {
                    isDragging = true;
                    dragStartX = e.touches[0].clientX; dragStartY = e.touches[0].clientY;
                    startOffsetX = offsetX; startOffsetY = offsetY;
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

            document.getElementById('cropConfirmBtn').addEventListener('click', function() {
                closeCrop();
                form.submit();
            });
            document.getElementById('cropCancelBtn').addEventListener('click', function() {
                input.value = '';
                closeCrop();
            });
            document.getElementById('cropCloseBtn').addEventListener('click', function() {
                input.value = '';
                closeCrop();
            });
        })();
        </script>
    @endif
@endauth
@endsection
