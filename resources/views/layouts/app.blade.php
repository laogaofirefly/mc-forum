<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#14532d">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MC论坛') - MC服务器论坛</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #1a1a2e;
            background-image: url("data:image/svg+xml,%3Csvg width='64' height='64' viewBox='0 0 64 64' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8 16c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0 2c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0 30c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0 2c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm48-32c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0 2c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0 30c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0 2c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z' fill='%2322c55e' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
            -webkit-tap-highlight-color: transparent;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .mc-button {
            background: linear-gradient(180deg, #4ade80 0%, #22c55e 50%, #16a34a 100%);
            border: 2px solid #15803d;
            box-shadow: 0 4px 0 #14532d, 0 6px 10px rgba(0,0,0,0.3);
            transition: all 0.1s;
            -webkit-appearance: none;
            appearance: none;
            min-height: 44px;
        }
        .mc-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #14532d, 0 8px 12px rgba(0,0,0,0.3);
        }
        .mc-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #14532d, 0 4px 8px rgba(0,0,0,0.3);
        }
        .mc-card {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border: 2px solid #334155;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .mc-input {
            background-color: #1e293b;
            border: 2px solid #334155;
            color: #e2e8f0;
            min-height: 44px;
            font-size: 16px;
            -webkit-appearance: none;
            appearance: none;
            border-radius: 8px;
        }
        .mc-input:focus {
            border-color: #22c55e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }
        textarea.mc-input {
            line-height: 1.6;
            resize: vertical;
        }
        select.mc-input {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .mc-input::placeholder {
            color: #64748b;
        }
        @media (max-width: 768px) {
            .mc-input {
                font-size: 16px;
            }
        }
        .form-error {
            color: #fca5a5;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .input-error {
            border-color: #ef4444 !important;
            background-color: rgba(239, 68, 68, 0.05);
        }
        .input-error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }
        .mobile-nav {
            display: none;
        }
        .mobile-nav.active {
            display: block;
        }
        .mobile-menu-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 8px;
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            border: none;
            color: #fff;
        }
        .mobile-menu-btn:active {
            background: rgba(255,255,255,0.1);
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 12px 20px;
            border-radius: 8px;
            color: #fff;
            font-weight: 500;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }
        .toast.show {
            transform: translateX(0);
        }
        @media (max-width: 768px) {
            .toast {
                top: auto;
                bottom: 80px;
                right: 16px;
                left: 16px;
            }
            .toast.show {
                transform: translateY(0);
            }
        }
        .char-counter {
            font-size: 0.75rem;
            color: #64748b;
            text-align: right;
            margin-top: 0.25rem;
        }
        .char-counter.warning {
            color: #f59e0b;
        }
        .char-counter.error {
            color: #ef4444;
        }
    </style>
</head>
<body class="min-h-screen text-gray-200">
    <nav class="bg-gradient-to-r from-primary-900 via-primary-800 to-primary-900 border-b-4 border-primary-600 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-primary-500 rounded flex items-center justify-center font-bold text-white text-xl">
                            MC
                        </div>
                        <span class="text-xl font-bold text-white hidden sm:inline">MC论坛</span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">首页</a>
                    <a href="{{ route('threads.index') }}" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">全部帖子</a>
                    <a href="{{ route('game-chat') }}" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">游戏聊天</a>
                    @auth
                        <a href="{{ route('threads.create') }}" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">发帖</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.monitor') }}" class="text-yellow-300 hover:text-yellow-100 px-3 py-2 rounded-md text-sm font-medium transition">📊 监控</a>
                        @endif
                    @endauth
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    @guest
                        <a href="{{ route('login') }}" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">登录</a>
                        <a href="{{ route('register') }}" class="mc-button text-white px-4 py-2 rounded-md text-sm font-bold">注册</a>
                    @else
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('profile.show', auth()->user()) }}" class="flex items-center space-x-2 text-primary-100 hover:text-white transition">
                                <img src="{{ auth()->user()->getAvatarUrl() }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded border-2 border-primary-500">
                                <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">退出</button>
                            </form>
                        </div>
                    @endguest
                </div>
                <button type="button" class="mobile-menu-btn md:hidden" id="mobileMenuBtn" aria-label="菜单">
                    <svg id="menuIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="closeIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="mobile-nav md:hidden border-t border-primary-700/50 bg-primary-900/95 backdrop-blur" id="mobileNav">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block text-primary-100 hover:bg-primary-800/50 px-3 py-3 rounded-md font-medium transition">🏠 首页</a>
                <a href="{{ route('threads.index') }}" class="block text-primary-100 hover:bg-primary-800/50 px-3 py-3 rounded-md font-medium transition">📋 全部帖子</a>
                <a href="{{ route('game-chat') }}" class="block text-primary-100 hover:bg-primary-800/50 px-3 py-3 rounded-md font-medium transition">💬 游戏聊天</a>
                @auth
                    <a href="{{ route('threads.create') }}" class="block text-primary-100 hover:bg-primary-800/50 px-3 py-3 rounded-md font-medium transition">✏️ 发帖</a>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('admin.monitor') }}" class="block text-yellow-300 hover:bg-yellow-900/30 px-3 py-3 rounded-md font-medium transition">📊 服务器监控</a>
                    @endif
                    <a href="{{ route('profile.show', auth()->user()) }}" class="block text-primary-100 hover:bg-primary-800/50 px-3 py-3 rounded-md font-medium transition">👤 我的主页</a>
                    <a href="{{ route('profile.edit') }}" class="block text-primary-100 hover:bg-primary-800/50 px-3 py-3 rounded-md font-medium transition">⚙️ 设置</a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left text-red-300 hover:bg-red-900/30 px-3 py-3 rounded-md font-medium transition">🚪 退出登录</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block text-primary-100 hover:bg-primary-800/50 px-3 py-3 rounded-md font-medium transition">🔑 登录</a>
                    <a href="{{ route('register') }}" class="block mc-button text-center text-white my-2 px-3 py-3 rounded-md font-bold">📝 立即注册</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-primary-900/50 border-2 border-primary-500 rounded-lg text-primary-200 flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-900/50 border-2 border-red-500 rounded-lg text-red-200 flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-900/50 border-2 border-red-500 rounded-lg text-red-200">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="font-medium">请修正以下问题：</span>
                </div>
                <ul class="list-disc list-inside ml-7 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(request()->routeIs('home'))
            <div class="max-w-4xl mx-auto">
                @yield('content')
            </div>
        @else
            <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
                <div class="flex-1 min-w-0">
                    @yield('content')
                </div>
                <div class="w-full lg:w-80 space-y-6">
                    @include('partials.server-status')
                </div>
            </div>
        @endif
    </main>

    <footer class="bg-gradient-to-r from-primary-900 via-primary-800 to-primary-900 border-t-4 border-primary-600 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="text-center text-primary-200">
                <p class="font-bold text-base sm:text-lg mb-2">MC论坛 - 我的世界玩家社区</p>
                <p class="text-xs sm:text-sm text-primary-300">&copy; {{ date('Y') }} MC论坛. 保留所有权利。</p>
            </div>
        </div>
    </footer>

    <div id="toast" class="toast"></div>

    <script>
        // 移动端导航菜单
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileNav = document.getElementById('mobileNav');
        const menuIcon = document.getElementById('menuIcon');
        const closeIcon = document.getElementById('closeIcon');

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileNav.classList.toggle('active');
                menuIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            });

            // 点击链接后自动关闭菜单
            const mobileLinks = mobileNav.querySelectorAll('a');
            mobileLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileNav.classList.remove('active');
                    menuIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                });
            });
        }

        // Toast 提示
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show';
            if (type === 'success') {
                toast.style.background = 'linear-gradient(135deg, #16a34a, #15803d)';
            } else if (type === 'error') {
                toast.style.background = 'linear-gradient(135deg, #dc2626, #991b1b)';
            } else if (type === 'warning') {
                toast.style.background = 'linear-gradient(135deg, #d97706, #92400e)';
            }
            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
        }

        // 自动字符计数
        document.querySelectorAll('[data-maxlength]').forEach(function(el) {
            const max = parseInt(el.getAttribute('data-maxlength'));
            if (!max) return;
            el.setAttribute('maxlength', max);
            const counterId = el.getAttribute('data-counter-id') || (el.id || 'input') + '-counter';
            let counter = document.getElementById(counterId);
            if (!counter) {
                counter = document.createElement('div');
                counter.id = counterId;
                counter.className = 'char-counter';
                el.parentNode.insertBefore(counter, el.nextSibling);
            }
            function update() {
                const len = el.value.length;
                counter.textContent = len + ' / ' + max;
                counter.classList.remove('warning', 'error');
                if (len >= max * 0.9) counter.classList.add('error');
                else if (len >= max * 0.75) counter.classList.add('warning');
            }
            el.addEventListener('input', update);
            update();
        });

        // 防止表单重复提交
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                const btns = form.querySelectorAll('button[type="submit"]');
                btns.forEach(function(btn) {
                    if (!btn.disabled) {
                        btn.disabled = true;
                        const original = btn.textContent;
                        btn.dataset.originalText = original;
                        btn.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>处理中...</span>';
                        setTimeout(function() {
                            btn.disabled = false;
                            btn.textContent = original;
                        }, 10000);
                    }
                });
            });
        });
    </script>
</body>
</html>
