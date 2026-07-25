<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        $threads = $user->threads()->with('category')->latest()->take(5)->get();
        $replies = $user->replies()->with('thread')->latest()->take(5)->get();

        return view('profile.show', compact('user', 'threads', 'replies'));
    }

    public function edit(): View
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,name,' . $user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($validated);

        return redirect()->route('profile.show', $user)->with('success', '个人资料已更新。');
    }

    public function mcBind(): View
    {
        $user = Auth::user();
        return view('profile.mc-bind', compact('user'));
    }

    public function mcBindUpdate(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'mc_username' => ['required', 'string', 'max:16'],
        ]);

        $uuid = $this->getMinecraftUuid($validated['mc_username']);

        if ($uuid) {
            $user->mc_username = $validated['mc_username'];
            $user->mc_uuid = $uuid;
            $user->mc_verified = true;
            $user->save();

            return redirect()->route('profile.show', $user)->with('success', 'MC 账号绑定成功！');
        }

        return back()->withErrors(['mc_username' => '无法验证该 MC 用户名，请检查后重试。'])->withInput();
    }

    private function getMinecraftUuid(string $username): ?string
    {
        $url = 'https://api.mojang.com/users/profiles/minecraft/' . urlencode($username);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['id'])) {
                $uuid = $data['id'];
                return substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20);
            }
        }

        return null;
    }
}
