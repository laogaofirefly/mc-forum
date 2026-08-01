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
    public function create(Request $request): View
    {
        $categories = Category::active()->get();
        $selectedCategory = $request->get('category');

        return view('threads.create', compact('categories', 'selectedCategory'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'min:5', 'max:100'],
            'body' => ['required', 'string', 'min:2', 'max:10000'],
        ], [
            'category_id.required' => '请选择帖子板块',
            'category_id.exists' => '所选板块不存在',
            'title.required' => '请填写帖子标题',
            'title.min' => '标题至少5个字符',
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

        $thread = Thread::create([
            'category_id' => $validated['category_id'],
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

        $thread->load(['user', 'category', 'replies.user', 'likes']);
        $replyCount = $thread->replies()->count();

        return view('threads.show', compact('thread', 'replyCount'));
    }

    public function edit(Thread $thread): View
    {
        if (!Auth::check() || (Auth::id() !== $thread->user_id && !Auth::user()->isAdmin())) {
            abort(403);
        }

        $categories = Category::active()->get();

        return view('threads.edit', compact('thread', 'categories'));
    }

    public function update(Request $request, Thread $thread): RedirectResponse
    {
        if (Auth::id() !== $thread->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'body' => ['required', 'string', 'min:10'],
        ]);

        $thread->update($validated);

        return redirect()->route('threads.show', $thread)->with('success', '帖子已更新。');
    }

    public function destroy(Thread $thread): RedirectResponse
    {
        if (Auth::id() !== $thread->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $category = $thread->category;
        $thread->delete();

        return redirect()->route('categories.show', $category)->with('success', '帖子已删除。');
    }
}
