<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#10b981">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MC论坛') - MC服务器社区</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            -webkit-tap-highlight-color: transparent;
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* 按钮 */
        .btn-primary {
            background-color: #10b981;
            color: #fff;
            transition: all 0.15s ease;
            min-height: 44px;
            font-weight: 600;
        }
        .btn-primary:hover { background-color: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
        .btn-primary:active { transform: translateY(0); }

        .btn-secondary {
            background-color: #fff;
            color: #334155;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease;
            min-height: 44px;
            font-weight: 500;
        }
        .btn-secondary:hover { background-color: #f8fafc; border-color: #cbd5e1; }

        .btn-danger {
            background-color: #ef4444;
            color: #fff;
            transition: all 0.15s ease;
            min-height: 44px;
            font-weight: 600;
        }
        .btn-danger:hover { background-color: #dc2626; }

        /* 卡片 */
        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: box-shadow 0.2s ease;
        }
        .card-hover:hover {
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border-color: #cbd5e1;
        }

        /* 输入框 */
        .input {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            color: #1e293b;
            min-height: 44px;
            font-size: 16px;
            border-radius: 10px;
            transition: all 0.15s ease;
            -webkit-appearance: none;
            appearance: none;
        }
        .input:focus {
            border-color: #10b981;
            outline: none;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        .input::placeholder { color: #94a3b8; }
        textarea.input { line-height: 1.6; resize: vertical; }
        select.input {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.25em 1.25em;
            padding-right: 2.5rem;
        }

        .form-error { color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; }
        .input-error { border-color: #ef4444 !important; }
        .input-error:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.1) !important; }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .mobile-nav { display: none; }
        .mobile-nav.active { display: block; }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 12px 20px;
            border-radius: 10px;
            color: #fff;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }
        .toast.show { transform: translateX(0); }
        @media (max-width: 768px) {
            .toast { top: auto; bottom: 80px; right: 16px; left: 16px; }
            .toast.show { transform: translateY(0); }
        }

        .char-counter { font-size: 0.75rem; color: #94a3b8; text-align: right; margin-top: 0.25rem; }
        .char-counter.warning { color: #f59e0b; }
        .char-counter.error { color: #ef4444; }

        /* 滚动条 */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* 链接 */
        .link-primary { color: #059669; }
        .link-primary:hover { color: #047857; text-decoration: underline; }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased">
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 backdrop-blur-lg bg-white/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center font-bold text-white text-sm shadow-sm">
                            MC
                        </div>
                        <span class="text-lg font-bold text-slate-900 hidden sm:inline">MC论坛</span>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('home') }}" class="text-slate-600 hover:text-primary-600 hover:bg-primary-50 px-3 py-2 rounded-lg text-sm font-medium transition">首页</a>
                    <a href="{{ route('threads.index') }}" class="text-slate-600 hover:text-primary-600 hover:bg-primary-50 px-3 py-2 rounded-lg text-sm font-medium transition">全部帖子</a>
                    <a href="{{ route('players.index') }}" class="text-slate-600 hover:text-primary-600 hover:bg-primary-50 px-3 py-2 rounded-lg text-sm font-medium transition">服务器成员</a>
                    <a href="{{ route('game-chat') }}" class="text-slate-600 hover:text-primary-600 hover:bg-primary-50 px-3 py-2 rounded-lg text-sm font-medium transition">游戏聊天</a>
                    @auth
                        <a href="{{ route('threads.create') }}" class="text-slate-600 hover:text-primary-600 hover:bg-primary-50 px-3 py-2 rounded-lg text-sm font-medium transition">发帖</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.monitor') }}" class="text-amber-600 hover:text-amber-700 hover:bg-amber-50 px-3 py-2 rounded-lg text-sm font-medium transition">监控</a>
                        @endif
                    @endauth
                </div>

                <div class="hidden md:flex items-center space-x-3">
                    @guest
                        <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900 px-3 py-2 rounded-lg text-sm font-medium transition">登录</a>
                        <a href="{{ route('register') }}" class="btn-primary px-4 py-2 rounded-lg text-sm">注册</a>
                    @else
                        <a href="{{ route('notifications.index') }}" class="relative text-slate-600 hover:text-primary-600 px-2 py-2 rounded-lg transition" title="消息通知" id="navNotifyBtn">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span id="navNotifyDot" class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center hidden">0</span>
                        </a>
                        <a href="{{ route('profile.show', auth()->user()) }}" class="flex items-center space-x-2 text-slate-700 hover:text-primary-600 transition">
                            <img src="{{ auth()->user()->getAvatarUrl() }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full ring-2 ring-slate-200">
                            <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-slate-400 hover:text-red-500 px-2 py-2 rounded-lg text-sm transition" title="退出登录">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    @endguest
                </div>

                <button type="button" class="md:hidden flex items-center justify-center w-10 h-10 rounded-lg text-slate-600 hover:bg-slate-100 transition" id="mobileMenuBtn" aria-label="菜单">
                    <svg id="menuIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg id="closeIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div class="mobile-nav md:hidden border-t border-slate-100 bg-white" id="mobileNav">
            <div class="px-3 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition">🏠 首页</a>
                <a href="{{ route('threads.index') }}" class="block text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition">📋 全部帖子</a>
                <a href="{{ route('players.index') }}" class="block text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition">👥 服务器成员</a>
                <a href="{{ route('game-chat') }}" class="block text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition">💬 游戏聊天</a>
                @auth
                    <a href="{{ route('notifications.index') }}" class="block text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition">🔔 消息通知 <span id="mobileNotifyCount" class="hidden ml-1 inline-block px-1.5 py-0.5 bg-red-500 text-white text-xs rounded-full">0</span></a>
                    <a href="{{ route('threads.create') }}" class="block text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition">✏️ 发帖</a>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('admin.monitor') }}" class="block text-amber-600 hover:bg-amber-50 px-3 py-2.5 rounded-lg font-medium transition">📊 服务器监控</a>
                    @endif
                    <a href="{{ route('profile.show', auth()->user()) }}" class="block text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition">👤 我的主页</a>
                    <a href="{{ route('profile.edit') }}" class="block text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition">⚙️ 设置</a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left text-red-500 hover:bg-red-50 px-3 py-2.5 rounded-lg font-medium transition">🚪 退出登录</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition">🔑 登录</a>
                    <a href="{{ route('register') }}" class="block btn-primary text-center px-3 py-2.5 rounded-lg mt-2">📝 立即注册</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-primary-50 border border-primary-200 rounded-xl text-primary-800 flex items-center">
                <svg class="w-5 h-5 mr-2.5 flex-shrink-0 text-primary-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 flex items-center">
                <svg class="w-5 h-5 mr-2.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 mr-2.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="font-medium">请修正以下问题：</span>
                </div>
                <ul class="list-disc list-inside ml-7 space-y-1 text-sm">
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
                <div class="w-full lg:w-80 space-y-6 flex-shrink-0">
                    @include('partials.server-status')
                </div>
            </div>
        @endif
    </main>

    <footer class="bg-white border-t border-slate-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-slate-500">
                <p class="font-bold text-base sm:text-lg mb-1 text-slate-700">MC论坛 · 我的世界玩家社区</p>
                <p class="text-xs sm:text-sm">&copy; {{ date('Y') }} MC论坛. 保留所有权利。</p>
            </div>
        </div>
    </footer>

    <div id="toast" class="toast"></div>

    <script>
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
            mobileNav.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    mobileNav.classList.remove('active');
                    menuIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                });
            });
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show';
            if (type === 'success') toast.style.background = '#10b981';
            else if (type === 'error') toast.style.background = '#ef4444';
            else if (type === 'warning') toast.style.background = '#f59e0b';
            setTimeout(() => { toast.className = 'toast'; }, 3000);
        }

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

        document.querySelectorAll('form').forEach(function(form) {
            if (form.hasAttribute('data-no-autodisable')) return;
            form.addEventListener('submit', function() {
                const btns = form.querySelectorAll('button[type="submit"]');
                btns.forEach(function(btn) {
                    if (!btn.disabled && !btn.classList.contains('no-disable')) {
                        btn.disabled = true;
                        const original = btn.innerHTML;
                        btn.dataset.originalHtml = original;
                        btn.innerHTML = '<span class="inline-flex items-center justify-center"><svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>处理中...</span>';
                        setTimeout(function() {
                            btn.disabled = false;
                            btn.innerHTML = original;
                        }, 10000);
                    }
                });
            });
        });

        // 通知红点：定时拉取未读数
        @auth
        function updateNotifyDot() {
            fetch('{{ route("notifications.unread") }}', { credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    var count = d.count || 0;
                    var dot = document.getElementById('navNotifyDot');
                    var mob = document.getElementById('mobileNotifyCount');
                    if (dot) {
                        if (count > 0) {
                            dot.textContent = count > 99 ? '99+' : count;
                            dot.classList.remove('hidden');
                        } else {
                            dot.classList.add('hidden');
                        }
                    }
                    if (mob) {
                        if (count > 0) {
                            mob.textContent = count > 99 ? '99+' : count;
                            mob.classList.remove('hidden');
                        } else {
                            mob.classList.add('hidden');
                        }
                    }
                })
                .catch(function() {});
        }
        updateNotifyDot();
        setInterval(updateNotifyDot, 30000);  // 30秒检查一次
        @endauth
    </script>
</body>
</html>
