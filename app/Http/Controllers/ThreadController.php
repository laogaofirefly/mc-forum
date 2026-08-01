<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Thread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ThreadController extends Controller
{
    public function create(): View
    {
        return view('threads.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:1', 'max:100'],
            'body' => ['required', 'string', 'min:2', 'max:10000'],
        ], [
            'title.required' => '请填写帖子标题',
            'title.min' => '标题不能为空',
            'title.max' => '标题不能超过100个字符',
            'body.required' => '请填写帖子内容',
            'body.min' => '内容至少2个字符',
            'body.max' => '内容不能超过10000个字符',
        ]);

        $baseSlug = Str::slug($validated['title']);
        if (empty($baseSlug)) {
            $baseSlug = 'thread';
        }
        $slug = $baseSlug;
        $count = Thread::where('slug', 'like', $baseSlug . '%')->count();
        if ($count > 0) {
            $slug = $baseSlug . '-' . time();
        }

        $category = Category::active()->first();
        $categoryId = $category ? $category->id : 1;

        $thread = Thread::create([
            'category_id' => $categoryId,
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'slug' => $slug,
            'body' => $validated['body'],
            'last_reply_at' => now(),
        ]);

        return redirect()->route('threads.show', $thread)->with('success', '帖子发布成功！');
    }

    public function show(Thread $thread): View
    {
        $thread->incrementViews();

        $thread->load(['user', 'category', 'replies.user']);
        $replyCount = $thread->replies()->count();

        return view('threads.show', compact('thread', 'replyCount'));
    }

    public function edit(Thread $thread): View
    {
        if (!Auth::check() || (Auth::id() !== $thread->user_id && !Auth::user()->isAdmin())) {
            abort(403);
        }

        return view('threads.edit', compact('thread'));
    }

    public function update(Request $request, Thread $thread): RedirectResponse
    {
        if (Auth::id() !== $thread->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:1', 'max:100'],
            'body' => ['required', 'string', 'min:2', 'max:10000'],
        ], [
            'title.required' => '请填写帖子标题',
            'title.min' => '标题不能为空',
            'title.max' => '标题不能超过100个字符',
            'body.required' => '请填写帖子内容',
            'body.min' => '内容至少2个字符',
            'body.max' => '内容不能超过10000个字符',
        ]);

        $thread->update($validated);

        return redirect()->route('threads.show', $thread)->with('success', '帖子已更新。');
    }

    public function destroy(Thread $thread): RedirectResponse
    {
        if (Auth::id() !== $thread->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $thread->delete();

        return redirect()->route('home')->with('success', '帖子已删除。');
    }
}
