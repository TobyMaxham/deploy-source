<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Exception;

class SimpleAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (null == ($user = $this->getUser())) {
            return redirect('login');
        }

        if ('' == trim(config('deployment.user_domain'))) {
            throw new Exception('User is not allowed to access this page');
        }

        $domain = explode('@', $user->email)[1];
        $filtered = collect(explode(';', config('deployment.user_domain')))
            ->filter(fn ($item) => $item == $domain);

        if (0 == $filtered->count()) {
            throw new Exception('Domain of user is not allowed to access this page');
        }

        return $next($request);
    }

    protected function getUser()
    {
        $user = session()->get('user');
        if ($user) {
            return $user;
        }

        return null;
        //return cache()->get('user');
    }
}
