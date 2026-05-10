<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();
        $email = $githubUser->getEmail() ?: $githubUser->getId().'@users.noreply.github.com';

        $user = User::query()
            ->where('github_id', $githubUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user === null) {
            $user = new User([
                'password' => Hash::make(Str::random(32)),
            ]);
        }

        $user->name = $githubUser->getName() ?: $githubUser->getNickname() ?: 'GitHub User';
        $user->email = $email;
        $user->github_id = $githubUser->getId();
        $user->save();

        Auth::login($user, remember: true);

        return redirect()->route('posts.index')->with('success', 'Вы вошли через GitHub.');
    }
}
