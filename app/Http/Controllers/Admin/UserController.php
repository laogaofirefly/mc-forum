<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403, '仅管理员可访问');
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $filter = (string) $request->input('filter', 'all');
        $query = User::query()
            ->withCount(['threads', 'replies']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('mc_username', 'like', '%' . $search . '%');
            });
        }

        switch ($filter) {
            case 'blocked':
                $query->where('is_blocked', true);
                break;
            case 'admin':
                $query->where('is_admin', true);
                break;
            case 'mc_bound':
                $query->whereNotNull('mc_uuid');
                break;
            default:
                break;
        }

        $users = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = [
            'total' => User::count(),
            'blocked' => User::where('is_blocked', true)->count(),
            'admins' => User::where('is_admin', true)->count(),
            'mc_bound' => User::whereNotNull('mc_uuid')->count(),
            'today' => User::where('created_at', '>=', now()->startOfDay())->count(),
        ];

        return view('admin.users.index', compact('users', 'search', 'filter', 'stats'));
    }

    public function block(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', '不能封禁自己');
        }
        if ($user->isAdmin()) {
            return back()->with('error', '不能封禁管理员');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:200'],
        ], [
            'reason.max' => '封禁原因不能超过200字',
        ]);

        $user->update([
            'is_blocked' => true,
            'blocked_at' => now(),
            'block_reason' => $validated['reason'] ?: null,
        ]);

        return back()->with('success', "已封禁用户 {$user->name}");
    }

    public function unblock(User $user): RedirectResponse
    {
        $user->update([
            'is_blocked' => false,
            'blocked_at' => null,
            'block_reason' => null,
        ]);

        return back()->with('success', "已解封用户 {$user->name}");
    }

    public function show(User $user): View
    {
        $user->loadCount(['threads', 'replies', 'likes']);

        $recentThreads = Thread::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'slug', 'created_at']);

        $recentReplies = Reply::with('thread:id,title,slug')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.users.show', compact('user', 'recentThreads', 'recentReplies'));
    }
}
