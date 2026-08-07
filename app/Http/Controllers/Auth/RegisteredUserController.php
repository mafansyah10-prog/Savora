<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if (\App\Models\Setting::getGlobal()->new_user_voucher_is_active) {
            $setting = \App\Models\Setting::getGlobal();
            \App\Models\Voucher::create([
                'code' => 'BARU-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'type' => $setting->new_user_voucher_type ?? 'fixed',
                'value' => $setting->new_user_voucher_value ?? 10000,
                'min_order_amount' => $setting->new_user_voucher_min_order_amount ?? 0,
                'is_active' => true,
                'is_hidden' => false,
                'expires_at' => now()->addDays((int)($setting->new_user_voucher_expires_in_days ?? 30)),
                'user_id' => $user->id,
                'usage_limit' => 1,
                'limit_per_user' => $setting->new_user_voucher_limit_per_user ?? 1,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
