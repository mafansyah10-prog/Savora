<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBlockedUser
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isBlockedNow()) {
            $message = Auth::user()->getBlockedMessage();
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => $message,
            ]);
        }

        return $next($request);
    }
}
