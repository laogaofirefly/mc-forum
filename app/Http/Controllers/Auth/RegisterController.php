<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:30', 'unique:users,name', 'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
            'mc_username' => ['nullable', 'string', 'max:16', 'regex:/^[A-Za-z0-9_]+$/'],
            'agree' => ['required', 'accepted'],
        ], [
            'name.required' => '请填写用户名',
            'name.min' => '用户名至少2个字符',
            'name.max' => '用户名不能超过30个字符',
            'name.unique' => '该用户名已被注册',
            'name.regex' => '用户名只能包含中文、字母、数字和下划线',
            'email.required' => '请填写邮箱',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '该邮箱已被注册',
            'password.required' => '请填写密码',
            'password.min' => '密码至少8位',
            'password.confirmed' => '两次输入的密码不一致',
            'mc_username.max' => 'MC游戏名不能超过16个字符',
            'mc_username.regex' => 'MC游戏名只能包含字母、数字和下划线',
            'agree.accepted' => '请阅读并同意用户协议',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'mc_username' => $validated['mc_username'] ?? null,
        ]);

        Auth::login($user, true);

        return redirect()->route('home')->with('success', '注册成功！欢迎加入 MC 论坛，' . $user->name);
    }
}
