<?php

use App\Http\Middleware\CheckBlockedUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhook/pakasir',
            'webhook/midtrans',
        ]);
        $middleware->appendToGroup('web', CheckBlockedUser::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckBirthdayVoucher::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403 && $request->is('admin*')) {
                if (auth()->guard('admin')->check() && ! auth()->guard('admin')->user()->can_access_admin_panel) {
                    auth()->guard('admin')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect('/admin/login')->with('error', 'Akun Anda tidak memiliki akses ke panel admin.');
                }
            }
        });
    })->create();
