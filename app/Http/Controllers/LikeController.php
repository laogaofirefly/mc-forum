<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Thread;
use App\Models\Reply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function toggle(Request $request, string $type, int $id): RedirectResponse
    {
        $user = Auth::user();

        $likeable = match ($type) {
            'thread' => Thread::findOrFail($id),
            'reply' => Reply::findOrFail($id),
            default => abort(404),
        };

        $like = Like::where('user_id', $user->id)
            ->where('likeable_type', get_class($likeable))
            ->where('likeable_id', $id)
            ->first();

        if ($like) {
            $like->delete();
            $message = '已取消点赞。';
        } else {
            Like::create([
                'user_id' => $user->id,
                'likeable_type' => get_class($likeable),
                'likeable_id' => $id,
            ]);
            $message = '点赞成功！';
        }

        return back()->with('success', $message);
    }
}
