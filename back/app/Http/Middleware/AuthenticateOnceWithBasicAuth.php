<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateOnceWithBasicAuth
{
    /**
     * Браузерное окно пароля без сессии. Гейт включён всегда.
     * Его снимает только BASIC_AUTH_DISABLED=true.
     * Логин и пароль сравниваются с env, без bcrypt на каждый запрос.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('auth.basic_disabled') === true) {
            return $next($request);
        }

        $login = (string) config('auth.basic_email');
        $password = (string) config('auth.basic_password');
        $givenLogin = (string) $request->getUser();
        $givenPassword = (string) $request->getPassword();
        $valid = $login !== ''
            && $password !== ''
            && hash_equals($login, $givenLogin)
            && hash_equals($password, $givenPassword);

        if (! $valid) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Schedule"',
            ]);
        }

        return $next($request);
    }
}
