<?php

namespace App\Http\Middleware;

use App\Services\BirthdayVoucherService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBirthdayVoucher
{
    protected BirthdayVoucherService $service;

    public function __construct(BirthdayVoucherService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $this->service->issueVoucherIfEligible(auth()->user());
        }

        return $next($request);
    }
}
