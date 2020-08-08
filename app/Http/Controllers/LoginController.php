<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Tobias Maxham <git2020@maxham.de>
 */
class LoginController
{
    public function login()
    {
        return $this->redirectToProvider();
    }

    public function redirectToProvider(): Response
    {
        return Socialite::driver('bitbucket')->redirect();
    }

    public function callback()
    {
        $data = $this->handleProviderCallback();

        $user = new User();
        $user->name = $data['user']->name;
        $user->email = $data['user']->email;
        session(['user' => $user]);
        cache()->put('user', $user);

        return redirect()->route('index')->withInput($data);
    }

    public function handleProviderCallback(): array
    {
        $user = Socialite::driver('bitbucket')->user();
        if (null == $user || ! isset($user->token)) {
            throw new Exception('Invalid Auth Token.');
        }

        return ['user' => $user,];
    }
}
