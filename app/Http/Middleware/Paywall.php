<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Paywall
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return abort(403, 'Usuário não tem permissão para acesso.');
        }

        if (!$user->premium) {
            return abort(402, 'Você não possui uma assinatura ativa.');
        }

        return $next($request);
    }
}
