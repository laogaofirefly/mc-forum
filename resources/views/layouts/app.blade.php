<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        }
        .mc-button {
            background: linear-gradient(180deg, #4ade80 0%, #22c55e 50%, #16a34a 100%);
            border: 2px solid #15803d;
            box-shadow: 0 4px 0 #14532d, 0 6px 10px rgba(0,0,0,0.3);
            transition: all 0.1s;
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
        }
        .mc-input:focus {
            border-color: #22c55e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }
    </style>
</head>
<body class="min-h-screen text-gray-200">
    <nav class="bg-gradient-to-r from-primary-900 via-primary-800 to-primary-900 border-b-4 border-primary-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-primary-500 rounded flex items-center justify-center font-bold text-white text-xl">
                            MC
                        </div>
                        <span class="text-xl font-bold text-white">MC论坛</span>
                    </a>
                    <div class="hidden md:flex space-x-4">
                        <a href="{{ route('home') }}" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">首页</a>
                        <a href="{{ route('categories.index') }}" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">板块</a>
                        @auth
                            <a href="{{ route('threads.create') }}" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">发帖</a>
                        @endauth
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @guest
                        <a href="{{ route('login') }}" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">登录</a>
                        <a href="{{ route('register') }}" class="mc-button text-white px-4 py-2 rounded-md text-sm font-bold">注册</a>
                    @else
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('profile.show', auth()->user()) }}" class="flex items-center space-x-2 text-primary-100 hover:text-white transition">
                                <img src="{{ auth()->user()->getAvatarUrl() }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded border-2 border-primary-500">
                                <span class="text-sm font-medium hidden sm:inline">{{ auth()->user()->name }}</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-primary-100 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition">退出</button>
                            </form>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-primary-900/50 border-2 border-primary-500 rounded-lg text-primary-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-900/50 border-2 border-red-500 rounded-lg text-red-200">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-900/50 border-2 border-red-500 rounded-lg text-red-200">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1">
                @yield('content')
            </div>
            <div class="w-full lg:w-80 space-y-6">
                @include('partials.server-status')
                @include('partials.sidebar-categories')
            </div>
        </div>
    </main>

    <footer class="bg-gradient-to-r from-primary-900 via-primary-800 to-primary-900 border-t-4 border-primary-600 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-primary-200">
                <p class="font-bold text-lg mb-2">MC论坛 - 我的世界玩家社区</p>
                <p class="text-sm text-primary-300">&copy; {{ date('Y') }} MC论坛. 保留所有权利。</p>
            </div>
        </div>
    </footer>
</body>
</html>
