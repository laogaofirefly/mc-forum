@extends('layouts.app')
@section('title', '在线地图')
@section('content')
<div class="space-y-5">
 <section class="card border-0 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 p-5 text-white sm:p-7"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm font-semibold tracking-wider text-emerald-100">DYNMAP · LIVE</p><h1 class="mt-1 text-2xl font-bold sm:text-3xl">服务器在线地图</h1><p class="mt-2 text-emerald-50">实时浏览世界、玩家和地标。</p></div><a href="{{ $dynmapUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-white px-4 py-2.5 text-center font-semibold text-emerald-700 shadow-sm">新窗口打开 ↗</a></div></section>
 <section class="card overflow-hidden"><div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5"><div><h2 class="font-bold text-slate-800">Dynmap 实时地图</h2><p class="mt-0.5 text-sm text-slate-500">地图功能由 Dynmap 原版前端提供</p></div><a href="{{ $dynmapUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary-600">全屏打开</a></div><iframe src="{{ $dynmapUrl }}" title="Minecraft Dynmap" class="block h-[75vh] min-h-[560px] w-full border-0 bg-slate-100" loading="eager" referrerpolicy="strict-origin-when-cross-origin">你的浏览器不支持内嵌地图。</iframe></section>
</div>
@endsection