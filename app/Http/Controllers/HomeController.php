<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::active()->withCount('threads')->get();

        $latestThreads = Thread::with(['user', 'category', 'latestReply.user'])
            ->withCount('replies')
            ->orderByRaw('COALESCE(last_reply_at, created_at) DESC')
            ->take(4)
            ->get();

        return view('home', compact('categories', 'latestThreads'));
    }
}
