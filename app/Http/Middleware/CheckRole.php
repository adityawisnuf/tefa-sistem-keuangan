<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ... $roles)
    {
        $user = Auth::user();

        foreach($roles as $role) {
            if ($user && $user->role === $role) {
                return $next($request);
            }
        }

        abort(403);
    }
}
