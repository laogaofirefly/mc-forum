@extends('layouts.app')

@section('title', '编辑资料')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card p-6">
        <h2 class="text-xl font-bold text-slate-900 mb-6">编辑个人资料</h2>
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
                        class="input w-full">{{ old('bio', $user->bio) }}</textarea>
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
@endsection
