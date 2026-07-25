<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::active()->withCount('threads')->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Request $request, Category $category): View
    {
        if (!$category->is_active) {
            abort(404);
        }

        $threads = Thread::where('category_id', $category->id)
            ->with(['user', 'latestReply.user'])
            ->withCount('replies')
            ->pinned()
            ->latest()
            ->paginate(20);

        $pinnedThreads = Thread::where('category_id', $category->id)
            ->with(['user', 'latestReply.user'])
            ->withCount('replies')
            ->pinned()
            ->latest()
            ->get();

        $normalThreads = Thread::where('category_id', $category->id)
            ->with(['user', 'latestReply.user'])
            ->withCount('replies')
            ->notPinned()
            ->latest('last_reply_at')
            ->orWhereNull('last_reply_at')
            ->latest('created_at')
            ->paginate(20);

        return view('categories.show', compact('category', 'pinnedThreads', 'normalThreads'));
    }
}
