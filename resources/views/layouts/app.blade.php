<!DOCTYPE html>
<html lang="zh-CN" class="">
<head>
    <meta charset="UTF-8">
    <!-- 阻止夜间模式闪烁：必须在任何内容渲染前执行 -->
    <script>
        (function() {
            var stored = localStorage.getItem('mc-forum-dark');
            if (stored === 'true' || (stored === null && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#10b981" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1e293b" media="(prefers-color-scheme: dark)">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MC论坛') - MC服务器社区</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
                            400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
                            800: '#065f46', 900: '#064e3b',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        heading: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        mono: ['SF Mono', 'Monaco', 'Consolas', 'ui-monospace', 'monospace'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-main: #1e293b;
            --text-sub: #64748b;
            --nav-bg: #ffffff;
            --nav-border: #e2e8f0;
            --footer-bg: #ffffff;
            --footer-border: #e2e8f0;
            --input-bg: #ffffff;
            --input-border: #e2e8f0;
            --input-focus-ring: rgba(16,185,129,0.1);
            --toast-success: #10b981;
            --toast-error: #ef4444;
            --toast-warning: #f59e0b;
        }
        .dark {
            --bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-main: #f1f5f9;
            --text-sub: #94a3b8;
            --nav-bg: #1e293b;
            --nav-border: #334155;
            --footer-bg: #1e293b;
            --footer-border: #334155;
            --input-bg: #1e293b;
            --input-border: #475569;
            --input-focus-ring: rgba(16,185,129,0.15);
        }
        body {
            background-color: var(--bg);
            -webkit-tap-highlight-color: transparent;
            padding-bottom: env(safe-area-inset-bottom);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        button, a, [role="button"] { touch-action: manipulation; }
        /* 按钮 */
        .btn-primary {
            background-color: #10b981;
            color: #fff;
            transition: all 0.15s ease;
            min-height: 44px;
            font-weight: 600;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
            vertical-align: middle;
            text-align: center;
        }
        .btn-primary:hover { background-color: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.25); }
        .btn-primary:active { transform: translateY(0); }
        .btn-secondary {
            background-color: var(--card-bg);
            color: var(--text-main);
            border: 1px solid var(--card-border);
            transition: all 0.15s ease;
            min-height: 44px;
            font-weight: 500;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
            vertical-align: middle;
            text-align: center;
        }
        .btn-secondary:hover { background-color: var(--bg); border-color: #cbd5e1; }
        .btn-danger {
            background-color: #ef4444;
            color: #fff;
            transition: all 0.15s ease;
            min-height: 44px;
            font-weight: 600;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
            vertical-align: middle;
            text-align: center;
        }
        .btn-danger:hover { background-color: #dc2626; }
        .btn-primary.w-full, .btn-secondary.w-full, .btn-danger.w-full { display: flex; width: 100%; }
        /* 卡片 */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            transition: box-shadow 0.2s ease, background-color 0.3s ease, border-color 0.3s ease;
        }
        .card-hover:hover {
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border-color: #cbd5e1;
        }
        .dark .card-hover:hover {
            box-shadow: 0 4px 24px rgba(0,0,0,0.3);
            border-color: #475569;
        }
        /* 输入框 */
        .input {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-main);
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
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }

        .link-primary { color: #059669; }
        .link-primary:hover { color: #047857; text-decoration: underline; }
        .dark .link-primary { color: #34d399; }
        .dark .link-primary:hover { color: #10b981; }

        /* ====== 字体层级区分 ====== */
        h1, h2, h3, h4, h5, h6, .font-heading { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; letter-spacing: -0.01em; }
        h1 { font-weight: 800; }
        h2 { font-weight: 700; }
        h3 { font-weight: 600; }
        .font-mono, code, pre { font-family: 'SF Mono', 'Monaco', 'Consolas', 'ui-monospace', monospace; }
        .text-body { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif; }
        .page-title { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
        @media (min-width: 640px) { .page-title { font-size: 1.75rem; } }

        /* ====== Markdown 渲染样式（prose） ====== */
        .prose { color: var(--text-main); line-height: 1.7; font-size: 0.95rem; word-wrap: break-word; }
        .prose-base { font-size: 1rem; }
        .prose-sm { font-size: 0.875rem; }
        .prose.max-w-none { max-width: none; }
        .prose > *:first-child { margin-top: 0; }
        .prose > *:last-child { margin-bottom: 0; }
        .prose h1 { font-size: 1.5em; font-weight: 700; color: #0f172a; margin: 0.8em 0 0.4em; line-height: 1.3; }
        .prose h2 { font-size: 1.3em; font-weight: 700; color: #0f172a; margin: 0.8em 0 0.4em; line-height: 1.3; }
        .prose h3 { font-size: 1.15em; font-weight: 700; color: #0f172a; margin: 0.7em 0 0.3em; line-height: 1.3; }
        .prose h4 { font-size: 1em; font-weight: 700; color: #0f172a; margin: 0.6em 0 0.3em; }
        .prose p { margin: 0.6em 0; }
        .prose a { color: #059669; text-decoration: none; }
        .prose a:hover { text-decoration: underline; color: #047857; }
        .prose strong { font-weight: 700; color: #0f172a; }
        .prose em { font-style: italic; }
        .prose ul { margin: 0.6em 0; padding-left: 1.5em; list-style: disc; }
        .prose ol { margin: 0.6em 0; padding-left: 1.5em; list-style: decimal; }
        .prose li { margin: 0.25em 0; }
        .prose blockquote { border-left: 3px solid #6ee7b7; padding: 0.2em 0 0.2em 1em; margin: 0.8em 0; color: #64748b; background: #f8fafc; border-radius: 0 6px 6px 0; }
        .prose blockquote p { margin: 0.3em 0; }
        .prose code { background: #f1f5f9; color: #db2777; padding: 0.15em 0.4em; border-radius: 4px; font-size: 0.875em; font-family: 'SF Mono', 'Monaco', 'Consolas', monospace; }
        .prose pre { background: #1e293b; color: #f1f5f9; padding: 0.9em 1em; border-radius: 8px; margin: 0.8em 0; overflow-x: auto; font-size: 0.85em; line-height: 1.5; }
        .prose pre code { background: transparent; color: inherit; padding: 0; border-radius: 0; font-size: inherit; }
        .prose img { max-width: 100%; height: auto; border-radius: 8px; margin: 0.8em 0; box-shadow: 0 1px 3px rgba(0,0,0,0.08); display: block; }
        .prose hr { border: none; border-top: 1px solid var(--card-border); margin: 1.5em 0; }
        .prose table { width: 100%; border-collapse: collapse; margin: 0.8em 0; font-size: 0.9em; }
        .prose th, .prose td { border: 1px solid var(--card-border); padding: 0.5em 0.75em; text-align: left; }
        .prose th { background: var(--bg); font-weight: 600; color: var(--text-main); }

        /* 暗色模式 prose */
        .dark .prose h1, .dark .prose h2, .dark .prose h3, .dark .prose h4, .dark .prose strong { color: #f1f5f9; }
        .dark .prose a { color: #34d399; }
        .dark .prose a:hover { color: #10b981; }
        .dark .prose code { background: #334155; color: #fbbf24; }
        .dark .prose blockquote { background: #1e293b; color: #94a3b8; border-left-color: #34d399; }
        .dark .prose pre { background: #0f172a; color: #e2e8f0; }
        .dark .prose pre code { background: transparent; }

        /* 暗色模式特定覆盖 */
        .dark .text-slate-900 { color: #f1f5f9 !important; }
        .dark .text-slate-800 { color: #e2e8f0 !important; }
        .dark .text-slate-700 { color: #cbd5e1 !important; }
        .dark .text-slate-600 { color: #94a3b8 !important; }
        .dark .text-slate-500 { color: #94a3b8 !important; }
        .dark .text-slate-400 { color: #64748b !important; }
        .dark .bg-white { background-color: #1e293b !important; }
        .dark .bg-slate-50 { background-color: #0f172a !important; }
        .dark .border-slate-200 { border-color: #334155 !important; }
        .dark .border-slate-100 { border-color: #334155 !important; }
        .dark .bg-primary-50 { background-color: rgba(16,185,129,0.1) !important; }
        .dark .text-primary-800 { color: #34d399 !important; }
        .dark .text-primary-700 { color: #10b981 !important; }
        .dark .text-primary-600 { color: #34d399 !important; }
        .dark .bg-red-50 { background-color: rgba(239,68,68,0.1) !important; }
        .dark .text-red-800 { color: #fca5a5 !important; }
        .dark .text-amber-600 { color: #fbbf24 !important; }
        .dark .hover\:text-primary-600:hover { color: #34d399 !important; }
        .dark .hover\:text-primary-700:hover { color: #10b981 !important; }
        .dark .hover\:bg-primary-50:hover { background-color: rgba(16,185,129,0.1) !important; }
        .dark .hover\:text-slate-900:hover { color: #f1f5f9 !important; }
        .dark .hover\:bg-slate-100:hover { background-color: #334155 !important; }
        .dark .ring-slate-100 { --tw-ring-color: #334155 !important; }
        .dark .ring-slate-200 { --tw-ring-color: #475569 !important; }
        .dark .toast { color: #fff; }
        .dark .text-slate-900 { color: #f1f5f9 !important; }
        .dark .text-slate-300 { color: #cbd5e1 !important; }
        .dark .text-slate-200 { color: #e2e8f0 !important; }
        .dark .bg-slate-100 { background-color: #1e293b !important; }

        /* ====== 全局动画 ====== */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.92); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
        @keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        @keyframes bounce-in {
            0% { opacity: 0; transform: scale(0.3); }
            50% { opacity: 1; transform: scale(1.05); }
            70% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
        @keyframes countUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        .animate-fade-in { animation: fadeIn 0.5s ease both; }
        .animate-fade-in-up { animation: fadeInUp 0.5s ease both; }
        .animate-fade-in-down { animation: fadeInDown 0.4s ease both; }
        .animate-fade-in-left { animation: fadeInLeft 0.5s ease both; }
        .animate-fade-in-right { animation: fadeInRight 0.5s ease both; }
        .animate-scale-in { animation: scaleIn 0.4s ease both; }
        .animate-bounce-in { animation: bounce-in 0.5s ease both; }
        .animate-pulse-soft { animation: pulse-soft 2s ease-in-out infinite; }
        .animate-shimmer {
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
            background-size: 200% 100%;
            animation: shimmer 1.5s ease-in-out infinite;
        }

        /* 交错动画（列表项逐条出现） */
        .stagger-children > * { opacity: 0; animation: fadeInUp 0.4s ease forwards; }
        .stagger-children > *:nth-child(1) { animation-delay: 0.05s; }
        .stagger-children > *:nth-child(2) { animation-delay: 0.1s; }
        .stagger-children > *:nth-child(3) { animation-delay: 0.15s; }
        .stagger-children > *:nth-child(4) { animation-delay: 0.2s; }
        .stagger-children > *:nth-child(5) { animation-delay: 0.25s; }
        .stagger-children > *:nth-child(6) { animation-delay: 0.3s; }
        .stagger-children > *:nth-child(7) { animation-delay: 0.35s; }
        .stagger-children > *:nth-child(8) { animation-delay: 0.4s; }
        .stagger-children > *:nth-child(9) { animation-delay: 0.45s; }
        .stagger-children > *:nth-child(10) { animation-delay: 0.5s; }

        /* 滚动触发动画（默认隐藏，由 JS 触发） */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .reveal-left { opacity: 0; transform: translateX(-24px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal-left.visible { opacity: 1; transform: translateX(0); }
        .reveal-right { opacity: 0; transform: translateX(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal-right.visible { opacity: 1; transform: translateX(0); }
        .reveal-scale { opacity: 0; transform: scale(0.94); transition: opacity 0.5s ease, transform 0.5s ease; }
        .reveal-scale.visible { opacity: 1; transform: scale(1); }

        /* 导航链接下划线动画 */
        .nav-link-underline { position: relative; }
        .nav-link-underline::after {
            content: ''; position: absolute; bottom: 4px; left: 50%; width: 0; height: 2px;
            background: #10b981; border-radius: 1px; transition: all 0.25s ease; transform: translateX(-50%);
        }
        .nav-link-underline:hover::after, .nav-link-underline.active::after { width: 60%; }

        /* 按钮波纹效果 */
        .btn-ripple { position: relative; overflow: hidden; }
        .btn-ripple::after {
            content: ''; position: absolute; inset: 0; background: radial-gradient(circle, rgba(255,255,255,0.3) 10%, transparent 10%);
            background-position: center; background-repeat: no-repeat; opacity: 0; transition: opacity 0.4s, background-size 0.4s;
        }
        .btn-ripple:active::after { background-size: 400%; opacity: 1; transition: 0s; }

        /* 图片加载渐入 */
        img[loading="lazy"] { opacity: 0; transition: opacity 0.4s ease; }
        img[loading="lazy"].loaded { opacity: 1; }

        /* 聊天消息进入动画 */
        @keyframes msgIn {
            from { opacity: 0; transform: translateY(12px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .chat-msg-enter { animation: msgIn 0.35s ease both; }

        /* 统计数字跳动 */
        .stat-number { display: inline-block; animation: countUp 0.5s ease both; }

        /* 页面切换过渡 */
        .page-transition { animation: fadeIn 0.35s ease both; }
        .content-wrapper { animation: fadeInUp 0.45s ease both; }

        /* 终端光标闪烁 */
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
        .cursor-blink { animation: blink 1s step-end infinite; }

        /* 悬停浮起效果 */
        .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .dark .hover-lift:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.3); }

        /* 悬停缩放 */
        .hover-scale { transition: transform 0.2s ease; }
        .hover-scale:hover { transform: scale(1.03); }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased flex flex-col">
    <nav class="sticky top-0 z-50 border-b backdrop-blur-lg" style="background-color: var(--nav-bg); border-color: var(--nav-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2.5">
                        {{-- Logo --}}
                        <svg class="w-9 h-9" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="36" height="36" rx="8" fill="url(#logo-grad)"/>
                            <defs><linearGradient id="logo-grad" x1="0" y1="0" x2="36" y2="36"><stop stop-color="#34d399"/><stop offset="1" stop-color="#059669"/></linearGradient></defs>
                            <text x="18" y="25" text-anchor="middle" fill="white" font-size="17" font-weight="800" font-family="Inter, sans-serif">MC</text>
                        </svg>
                        <span class="text-lg font-bold hidden sm:inline" style="color: var(--text-main);">MC论坛</span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('home') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-600 hover:text-primary-600 hover:bg-primary-50' }}">首页</a>
                    <a href="{{ route('threads.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('threads.index') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-600 hover:text-primary-600 hover:bg-primary-50' }}">全部帖子</a>
                    <a href="{{ route('players.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('players.index') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-600 hover:text-primary-600 hover:bg-primary-50' }}">服务器成员</a>
                    <a href="{{ route('dynmap.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('dynmap.index') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-600 hover:text-primary-600 hover:bg-primary-50' }}">在线地图</a>
                    <a href="{{ route('game-chat') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('game-chat') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-600 hover:text-primary-600 hover:bg-primary-50' }}">游戏聊天</a>
                    <a href="{{ route('private-chat') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('private-chat') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-600 hover:text-primary-600 hover:bg-primary-50' }}">私聊</a>
                    @auth
                        <a href="{{ route('threads.create') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('threads.create') ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-600 hover:text-primary-600 hover:bg-primary-50' }}">发帖</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.console') ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-amber-600 hover:text-amber-700 hover:bg-amber-50' }}">用户管理</a>
                            <a href="{{ route('admin.console') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.console') ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-amber-600 hover:text-amber-700 hover:bg-amber-50' }}">控制台</a>
                        @endif
                    @endauth
                </div>
                <div class="hidden md:flex items-center space-x-3">
                    {{-- 夜间模式切换 --}}
                    <button type="button" id="darkModeToggle" class="w-9 h-9 flex items-center justify-center rounded-lg transition hover:bg-slate-100" style="color: var(--text-main);" title="切换夜间模式" aria-label="切换夜间模式">
                        <svg id="darkIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg id="lightIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
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
        <div class="mobile-nav md:hidden border-t border-slate-100 bg-white dark:bg-slate-800 dark:border-slate-700" id="mobileNav">
            <div class="px-3 py-3 space-y-1">
                {{-- 夜间模式切换 --}}
                <button type="button" id="mobileDarkToggle" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg font-medium transition text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700" aria-label="切换夜间模式">
                    <span class="flex items-center gap-2">
                        <span id="mobileDarkIcon">@include('layouts.partials.icons', ['name' => 'moon', 'class' => 'w-5 h-5'])</span> 夜间模式
                    </span>
                    <span class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors bg-slate-300 dark:bg-primary-500" id="mobileDarkSwitch">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-1 dark:translate-x-6"></span>
                    </span>
                </button>
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('home') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                    @include('layouts.partials.icons', ['name' => 'home', 'class' => 'w-5 h-5 flex-shrink-0']) 首页
                </a>
                <a href="{{ route('threads.index') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('threads.index') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                    @include('layouts.partials.icons', ['name' => 'posts', 'class' => 'w-5 h-5 flex-shrink-0']) 全部帖子
                </a>
                <a href="{{ route('players.index') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('players.index') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                    @include('layouts.partials.icons', ['name' => 'users', 'class' => 'w-5 h-5 flex-shrink-0']) 服务器成员
                </a>
                <a href="{{ route('dynmap.index') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('dynmap.index') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                    <span class="w-5 text-center text-base leading-5" aria-hidden="true">🗺️</span> 在线地图
                </a>
                <a href="{{ route('game-chat') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('game-chat') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                    @include('layouts.partials.icons', ['name' => 'chat', 'class' => 'w-5 h-5 flex-shrink-0']) 游戏聊天
                </a>
                <a href="{{ route('private-chat') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('private-chat') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                    @include('layouts.partials.icons', ['name' => 'mail', 'class' => 'w-5 h-5 flex-shrink-0']) 私聊
                </a>
                @auth
                    <a href="{{ route('notifications.index') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('notifications.index') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                        @include('layouts.partials.icons', ['name' => 'bell', 'class' => 'w-5 h-5 flex-shrink-0']) 消息通知 <span id="mobileNotifyCount" class="hidden ml-1 inline-block px-1.5 py-0.5 bg-red-500 text-white text-xs rounded-full">0</span>
                    </a>
                    <a href="{{ route('threads.create') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('threads.create') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                        @include('layouts.partials.icons', ['name' => 'pencil', 'class' => 'w-5 h-5 flex-shrink-0']) 发帖
                    </a>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 text-amber-600 hover:bg-amber-50 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.console') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                            @include('layouts.partials.icons', ['name' => 'users', 'class' => 'w-5 h-5 flex-shrink-0']) 用户管理
                        </a>
                        <a href="{{ route('admin.console') }}" class="flex items-center gap-2.5 text-amber-600 hover:bg-amber-50 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('admin.console') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                            @include('layouts.partials.icons', ['name' => 'terminal', 'class' => 'w-5 h-5 flex-shrink-0']) 控制台
                        </a>
                    @endif
                    <a href="{{ route('profile.show', auth()->user()) }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('profile.show') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                        @include('layouts.partials.icons', ['name' => 'user', 'class' => 'w-5 h-5 flex-shrink-0']) 我的主页
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('profile.edit') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                        @include('layouts.partials.icons', ['name' => 'cog', 'class' => 'w-5 h-5 flex-shrink-0']) 设置
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center gap-2.5 text-red-500 hover:bg-red-50 px-3 py-2.5 rounded-lg font-medium transition">
                            @include('layouts.partials.icons', ['name' => 'logout', 'class' => 'w-5 h-5 flex-shrink-0']) 退出登录
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-2.5 text-slate-700 hover:bg-primary-50 hover:text-primary-700 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('login') ? 'bg-primary-50 text-primary-700 font-semibold' : '' }}">
                        @include('layouts.partials.icons', ['name' => 'login', 'class' => 'w-5 h-5 flex-shrink-0']) 登录
                    </a>
                    <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 btn-primary text-center px-3 py-2.5 rounded-lg mt-2">
                        @include('layouts.partials.icons', ['name' => 'plus', 'class' => 'w-5 h-5 flex-shrink-0']) 立即注册
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="content-wrapper">
        @if(session('success'))
            <div class="mb-6 p-4 bg-primary-50 border border-primary-200 rounded-xl text-primary-800 flex items-center" style="background-color: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.2);">
                <svg class="w-5 h-5 mr-2.5 flex-shrink-0 text-primary-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 flex items-center" style="background-color: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.2);">
                <svg class="w-5 h-5 mr-2.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800" style="background-color: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.2);">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 mr-2.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="font-medium" style="color: #fca5a5;">请修正以下问题：</span>
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
        </div>
    </main>
    <footer style="background-color: var(--footer-bg); border-color: var(--footer-border);" class="border-t">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-slate-500">
                <p class="font-bold text-base sm:text-lg mb-1" style="color: var(--text-main);">MC论坛 · 我的世界玩家社区</p>
                <p class="text-xs sm:text-sm" style="color: var(--text-sub);">&copy; {{ date('Y') }} MC论坛. 保留所有权利。</p>
            </div>
        </div>
    </footer>
    <div id="toast" class="toast"></div>
    <script>
        // ========== 暗色模式 ==========
        (function() {
            var html = document.documentElement;
            var desktopToggle = document.getElementById('darkModeToggle');
            var darkIcon = document.getElementById('darkIcon');
            var lightIcon = document.getElementById('lightIcon');
            var mobileToggle = document.getElementById('mobileDarkToggle');
            var mobileDarkIcon = document.getElementById('mobileDarkIcon');
            var mobileSwitch = document.getElementById('mobileDarkSwitch');

            function setDark(isDark) {
                if (isDark) {
                    html.classList.add('dark');
                    if (darkIcon) darkIcon.classList.add('hidden');
                    if (lightIcon) lightIcon.classList.remove('hidden');
                    if (mobileDarkIcon) mobileDarkIcon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';
                    if (mobileSwitch) { mobileSwitch.classList.add('dark:bg-primary-500'); mobileSwitch.classList.remove('bg-slate-300'); }
                } else {
                    html.classList.remove('dark');
                    if (lightIcon) lightIcon.classList.add('hidden');
                    if (darkIcon) darkIcon.classList.remove('hidden');
                    if (mobileDarkIcon) mobileDarkIcon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>';
                    if (mobileSwitch) { mobileSwitch.classList.remove('dark:bg-primary-500'); mobileSwitch.classList.add('bg-slate-300'); }
                }
            }

            function toggleDark() {
                var isDark = !html.classList.contains('dark');
                setDark(isDark);
                localStorage.setItem('mc-forum-dark', isDark);
                var metaLight = document.querySelector('meta[name="theme-color"][media="(prefers-color-scheme: light)"]');
                var metaDark = document.querySelector('meta[name="theme-color"][media="(prefers-color-scheme: dark)"]');
                if (metaLight) metaLight.content = isDark ? '#1e293b' : '#10b981';
                if (metaDark) metaDark.content = isDark ? '#1e293b' : '#10b981';
            }

            // 初始化：从阻塞脚本设置的状态读取
            setDark(html.classList.contains('dark'));

            if (desktopToggle) desktopToggle.addEventListener('click', toggleDark);
            if (mobileToggle) mobileToggle.addEventListener('click', toggleDark);

            // 监听系统主题变化
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (localStorage.getItem('mc-forum-dark') === null) {
                    setDark(e.matches);
                }
            });
        })();

        // ========== 移动菜单 ==========
        (function() {
            var mobileMenuBtn = document.getElementById('mobileMenuBtn');
            var mobileNav = document.getElementById('mobileNav');
            var menuIcon = document.getElementById('menuIcon');
            var closeIcon = document.getElementById('closeIcon');
            var backdrop = null;

            function openMenu() {
                mobileNav.classList.add('active');
                menuIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
                // 添加遮罩层
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'fixed inset-0 bg-black/30 z-40 md:hidden';
                    backdrop.style.top = '64px';
                    backdrop.addEventListener('click', closeMenu);
                    backdrop.addEventListener('touchstart', function(e) { e.preventDefault(); closeMenu(); });
                }
                document.body.appendChild(backdrop);
                document.body.style.overflow = 'hidden';
            }

            function closeMenu() {
                mobileNav.classList.remove('active');
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
                if (backdrop && backdrop.parentNode) backdrop.parentNode.removeChild(backdrop);
                document.body.style.overflow = '';
            }

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    if (mobileNav.classList.contains('active')) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });
                // 导航链接点击关闭菜单（暗色模式切换按钮除外）
                mobileNav.querySelectorAll('a, form button[type="submit"]').forEach(function(el) {
                    el.addEventListener('click', function() { closeMenu(); });
                });
            }
        })();

        // ========== Toast ==========
        function showToast(message, type) {
            type = type || 'success';
            var toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show';
            if (type === 'success') toast.style.background = '#10b981';
            else if (type === 'error') toast.style.background = '#ef4444';
            else if (type === 'warning') toast.style.background = '#f59e0b';
            var timer = setTimeout(function() { toast.className = 'toast'; }, 3000);
            toast.addEventListener('click', function() { clearTimeout(timer); toast.className = 'toast'; }, { once: true });
        }

        // ========== 字符计数器 ==========
        (function() {
            document.querySelectorAll('[data-maxlength]').forEach(function(el) {
                var max = parseInt(el.getAttribute('data-maxlength'));
                if (!max) return;
                el.setAttribute('maxlength', max);
                var counterId = el.getAttribute('data-counter-id') || (el.id || 'input') + '-counter';
                var counter = document.getElementById(counterId);
                if (!counter) {
                    counter = document.createElement('div');
                    counter.id = counterId;
                    counter.className = 'char-counter';
                    el.parentNode.insertBefore(counter, el.nextSibling);
                }
                function update() {
                    var len = el.value.length;
                    counter.textContent = len + ' / ' + max;
                    counter.classList.remove('warning', 'error');
                    if (len >= max * 0.9) counter.classList.add('error');
                    else if (len >= max * 0.75) counter.classList.add('warning');
                }
                el.addEventListener('input', update);
                update();
            });
        })();

        // ========== 表单提交防重复 ==========
        (function() {
            document.querySelectorAll('form').forEach(function(form) {
                if (form.hasAttribute('data-no-autodisable')) return;
                form.addEventListener('submit', function() {
                    var btns = form.querySelectorAll('button[type="submit"]');
                    btns.forEach(function(btn) {
                        if (!btn.disabled && !btn.classList.contains('no-disable')) {
                            btn.disabled = true;
                            var original = btn.innerHTML;
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
        })();

        @auth
        // ========== 通知红点 ==========
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
        setInterval(updateNotifyDot, 60000);

        // ========== 点赞 ==========
        window.toggleLike = function(btn) {
            var type = btn.getAttribute('data-like-type');
            var id = btn.getAttribute('data-like-id');
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('{{ route("likes.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ likeable_type: type, likeable_id: id })
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d.ok) return;
                var svg = btn.querySelector('svg');
                var span = btn.querySelector('.like-count');
                if (d.liked) {
                    btn.classList.remove('text-slate-400', 'hover:text-red-400', 'hover:bg-red-50');
                    btn.classList.add('text-red-500', 'bg-red-50', 'hover:bg-red-100');
                    if (svg) svg.setAttribute('fill', 'currentColor');
                } else {
                    btn.classList.remove('text-red-500', 'bg-red-50', 'hover:bg-red-100');
                    btn.classList.add('text-slate-400', 'hover:text-red-400', 'hover:bg-red-50');
                    if (svg) svg.setAttribute('fill', 'none');
                }
                if (span) span.textContent = d.count;
            })
            .catch(function() {});
        };
        @endauth

        // ========== 滚动触发动画 (Intersection Observer) ==========
        (function() {
            if (!('IntersectionObserver' in window)) return;
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        // 不再观察已显示的
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

            document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale').forEach(function(el) {
                observer.observe(el);
            });
        })();

        // ========== 图片懒加载渐入 ==========
        (function() {
            document.querySelectorAll('img[loading="lazy"]').forEach(function(img) {
                if (img.complete) return img.classList.add('loaded');
                img.addEventListener('load', function() { img.classList.add('loaded'); });
                img.addEventListener('error', function() { img.classList.add('loaded'); });
            });
        })();
    </script>
</body>
</html>
