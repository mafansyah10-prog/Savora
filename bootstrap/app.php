<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhook/pakasir',
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckBlockedUser::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 403 && $request->is('admin*')) {
                if (auth()->guard('admin')->check() && !auth()->guard('admin')->user()->can_access_admin_panel) {
                    auth()->guard('admin')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect('/admin/login')->with('error', 'Akun Anda tidak memiliki akses ke panel admin.');
                }
            }
        });
    })->create();
