<?php
namespace App\Http\Controllers;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use App\Services\MentionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReplyController extends Controller
{
    public function store(Request $request, Thread $thread): RedirectResponse
    {
        if ($thread->is_locked) {
            return back()->with('error', '该帖子已锁定，无法回复。');
        }
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:5000'],
        ], [
            'body.required' => '请填写回复内容',
            'body.min' => '回复内容不能为空',
            'body.max' => '回复内容不能超过5000个字符',
        ]);
        $reply = Reply::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);
        $thread->update(['last_reply_at' => now()]);

        // 通知帖子作者
        if ($thread->user_id !== Auth::id()) {
            NotificationController::createNotification(
                $thread->user_id,
                'reply',
                $reply->id,
                Reply::class,
                Auth::id(),
                ['thread_title' => $thread->title, 'thread_slug' => $thread->slug]
            );
        }

        // @提及检测与通知
        MentionService::processMentions($validated['body'], $thread, $reply->id);

        return redirect()->route('threads.show', $thread)
            ->with('success', '回复成功！')
            ->withFragment('reply-' . $reply->id);
    }

    public function edit(Reply $reply): View
    {
        if (Auth::id() !== $reply->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }
        return view('replies.edit', compact('reply'));
    }

    public function update(Request $request, Reply $reply): RedirectResponse
    {
        if (Auth::id() !== $reply->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:5000'],
        ], [
            'body.required' => '请填写回复内容',
            'body.min' => '回复内容不能为空',
            'body.max' => '回复内容不能超过5000个字符',
        ]);
        $reply->update($validated);

        // 编辑后也重新检测 @提及
        MentionService::processMentions($validated['body'], $reply->thread, $reply->id);

        return redirect()->route('threads.show', $reply->thread)
            ->with('success', '回复已更新。')
            ->withFragment('reply-' . $reply->id);
    }

    public function destroy(Reply $reply): RedirectResponse
    {
        if (Auth::id() !== $reply->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }
        $thread = $reply->thread;
        $reply->delete();
        return redirect()->route('threads.show', $thread)->with('success', '回复已删除。');
    }
}