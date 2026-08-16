<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        $voucher = Voucher::where('user_id', $user->id)->first();
        $this->assertNotNull($voucher);
        $this->assertStringStartsWith('BARU-', $voucher->code);
        $this->assertEquals('fixed', $voucher->type);
        $this->assertEquals(10000, (int) $voucher->value);
    }
}
