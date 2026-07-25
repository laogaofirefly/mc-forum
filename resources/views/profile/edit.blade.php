@extends('layouts.app')

@section('title', '编辑资料')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mc-card rounded-lg p-6">
        <h2 class="text-xl font-bold text-white mb-6">编辑个人资料</h2>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">用户名</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">邮箱</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="mc-input w-full px-4 py-2 rounded-lg">
                </div>
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-300 mb-1">个人简介</label>
                    <textarea id="bio" name="bio" rows="4"
                        class="mc-input w-full px-4 py-2 rounded-lg">{{ old('bio', $user->bio) }}</textarea>
                </div>
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('profile.show', $user) }}" class="text-gray-400 hover:text-gray-300 transition">
                        ← 返回
                    </a>
                    <button type="submit" class="mc-button text-white px-6 py-2 rounded-lg font-bold">
                        保存修改
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
