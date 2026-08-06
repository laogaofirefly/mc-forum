@extends('layouts.app')

@section('title', '在线地图')

@section('content')
<div class="space-y-5 sm:space-y-6">
    <div class="card p-5 sm:p-6 bg-gradient-to-br from-emerald-500 to-teal-700 border-0 text-white">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-2">
                    <span aria-hidden="true">🗺️</span> Minecraft 在线地图
                </h1>
                <p class="mt-1 text-emerald-50">实时浏览服务器世界、玩家位置和地标。</p>
            </div>
            <a href="{{ $dynmapUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                在新窗口打开地图 ↗
            </a>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5">
            <h2 class="font-bold text-slate-800">Dynmap</h2>
            <a href="{{ $dynmapUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary-600 hover:text-primary-700">无法加载？新窗口打开</a>
        </div>
        <iframe
            src="{{ $dynmapUrl }}"
            title="Minecraft Dynmap 在线地图"
            class="block h-[72vh] min-h-[560px] w-full border-0 bg-slate-100"
            loading="eager"
            referrerpolicy="strict-origin-when-cross-origin"
        >
            你的浏览器不支持内嵌地图。请使用上方“在新窗口打开地图”。
        </iframe>
    </div>
</div>
@endsection
