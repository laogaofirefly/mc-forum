<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $login = trim($validated['login']);
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        // 先找到用户，检查是否被封禁
        $user = \App\Models\User::where($field, $login)->first();
        if ($user && $user->isBlocked()) {
            $reason = $user->block_reason ? '（原因：' . $user->block_reason . '）' : '';
            return back()->withErrors([
                'login' => '该账号已被封禁' . $reason . '，请联系管理员。',
            ])->onlyInput('login');
        }

        if (Auth::attempt([$field => $login, 'password' => $validated['password']], !empty($validated['remember']))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'))->with('success', '登录成功！欢迎回来，' . Auth::user()->name);
        }

        return back()->withErrors([
            'login' => '账号或密码错误。',
        ])->onlyInput('login');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', '已成功退出登录。');
    }
}
