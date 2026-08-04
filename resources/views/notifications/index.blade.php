@extends('layouts.app')
@section('title', '消息通知')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="page-title text-slate-900 flex items-center">
                @include('layouts.partials.icons', ['name' => 'bell', 'class' => 'w-6 h-6 mr-2 flex-shrink-0'])消息通知
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">所有人对你的帖子/回复的操作记录</p>
        </div>
        @if($unreadCount > 0)
            <span class="badge bg-red-100 text-red-700">
                {{ $unreadCount }} 条未读
            </span>
        @else
            <span class="badge bg-slate-100 text-slate-600">已全部读完</span>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notif)
            <div class="card p-4 {{ $notif->is_read ? '' : 'border-l-4 border-l-primary-500 bg-primary-50/30' }}">
                <div class="flex items-start gap-3">
                    <a href="{{ route('profile.show', $notif->fromUser) }}" class="flex-shrink-0">
                        <img src="{{ $notif->fromUser->getAvatarUrl() }}" alt="{{ $notif->fromUser->name }}"
                            class="w-10 h-10 rounded-full ring-2 ring-slate-100 bg-white object-cover">
                    </a>
                    <div class="flex-1 min-w-0">
                        {{-- 通知标题行 --}}
                        <div class="text-sm text-slate-600 mb-1">
                            <a href="{{ route('profile.show', $notif->fromUser) }}" class="text-primary-600 hover:text-primary-700 hover:underline font-medium">
                                {{ $notif->fromUser->name }}
                            </a>

                            @if($notif->type === 'reply')
                                回复了你的帖子
                            @elseif($notif->type === 'like')
                                赞了你的{{ $notif->notifiable_type === 'App\\Models\\Thread' ? '帖子' : '回复' }}
                            @elseif($notif->type === 'mention')
                                在回复中提到了你
                            @else
                                与你产生了互动
                            @endif

                            {{-- 链接到帖子/回复 --}}
                            @php
                                $link = '#';
                                $title = '';
                                if ($notif->type === 'reply' || $notif->type === 'mention') {
                                    $data = $notif->data ?? [];
                                    $slug = $data['thread_slug'] ?? '';
                                    $rid = $data['reply_id'] ?? '';
                                    if ($slug) {
                                        $link = route('threads.show', $slug) . ($rid ? '#reply-' . $rid : '');
                                    }
                                    $title = $data['thread_title'] ?? '';
                                } elseif ($notif->type === 'like') {
                                    $data = $notif->data ?? [];
                                    $slug = $data['thread_slug'] ?? '';
                                    if ($slug) {
                                        $link = route('threads.show', $slug);
                                    }
                                    $title = $data['thread_title'] ?? '';
                                }
                            @endphp
                            @if($link !== '#')
                                <a href="{{ $link }}" class="text-slate-900 hover:text-primary-600 hover:underline font-medium">
                                    {{ $title ?: '查看详情' }}
                                </a>
                            @endif
                        </div>

                        {{-- 内容预览（仅 reply 和 mention 显示） --}}
                        @if(($notif->type === 'reply' || $notif->type === 'mention') && !empty($notif->data['body_excerpt'] ?? ''))
                            <div class="bg-slate-50 rounded-lg p-3 prose prose-slate prose-sm max-w-none border border-slate-100">
                                {{ $notif->data['body_excerpt'] }}
                            </div>
                        @endif

                        <div class="text-xs text-slate-400 mt-1.5">{{ $notif->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-8 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="font-medium text-slate-500">暂无通知</p>
                <p class="mt-1 text-xs">当有人回复你的帖子或与你互动时，会在这里显示</p>
            </div>
        @endforelse
    </div>

    @if($notifications->count() >= 50)
        <div class="text-center text-xs text-slate-400 py-2">仅显示最近 50 条通知</div>
    @endif
</div>
@endsection
