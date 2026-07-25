<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ServerStatus;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $serverHost = config('services.minecraft.host', 'localhost');
        $serverPort = config('services.minecraft.port', 25565);

        try {
            $serverStatus = ServerStatus::getStatus($serverHost, $serverPort);
        } catch (\Exception $e) {
            $serverStatus = null;
        }

        $categories = Category::active()->withCount('threads')->get();

        $latestThreads = Thread::with(['user', 'category', 'latestReply.user'])
            ->orderByRaw('COALESCE(last_reply_at, created_at) DESC')
            ->take(10)
            ->get();

        $popularThreads = Thread::with(['user', 'category'])
            ->withCount('replies')
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        return view('home', compact('serverStatus', 'categories', 'latestThreads', 'popularThreads'));
    }
}
