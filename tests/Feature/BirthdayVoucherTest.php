<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BirthdayVoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_birth_date()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'birth_date' => '1995-05-15',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertEquals('1995-05-15', $user->birth_date->format('Y-m-d'));
    }

    public function test_birthday_voucher_issued_automatically_on_birthday()
    {
        $setting = Setting::first() ?? Setting::create([
            'store_name' => 'Savora',
            'whatsapp_number' => '6289601905406',
            'instagram_url' => 'https://instagram.com/m.afansyah_',
            'hero_title' => 'Title',
            'hero_subtitle' => 'Subtitle',
        ]);
        $setting->update([
            'birthday_voucher_is_active' => true,
            'birthday_voucher_value' => 20000.00,
        ]);

        $today = now()->timezone('Asia/Jakarta');
        $user = User::factory()->create([
            'birth_date' => $today->subYears(20)->format('Y-m-d'),
        ]);

        $this->actingAs($user)->get(route('home'));

        $this->assertTrue(Voucher::where('user_id', $user->id)->where('code', 'like', 'HBD-%')->exists());
        $voucher = Voucher::where('user_id', $user->id)->first();
        $this->assertEquals(20000.00, $voucher->value);
    }

    public function test_birthday_voucher_not_issued_if_not_birthday()
    {
        $setting = Setting::first() ?? Setting::create([
            'store_name' => 'Savora',
            'whatsapp_number' => '6289601905406',
            'instagram_url' => 'https://instagram.com/m.afansyah_',
            'hero_title' => 'Title',
            'hero_subtitle' => 'Subtitle',
        ]);
        $setting->update([
            'birthday_voucher_is_active' => true,
        ]);

        $today = now()->timezone('Asia/Jakarta');
        $user = User::factory()->create([
            // Set birthday to tomorrow
            'birth_date' => $today->addDay()->subYears(20)->format('Y-m-d'),
        ]);

        $this->actingAs($user)->get(route('home'));

        $this->assertFalse(Voucher::where('user_id', $user->id)->where('code', 'like', 'HBD-%')->exists());
    }

    public function test_birthday_voucher_issued_only_once_per_year()
    {
        $setting = Setting::first() ?? Setting::create([
            'store_name' => 'Savora',
            'whatsapp_number' => '6289601905406',
            'instagram_url' => 'https://instagram.com/m.afansyah_',
            'hero_title' => 'Title',
            'hero_subtitle' => 'Subtitle',
        ]);
        $setting->update([
            'birthday_voucher_is_active' => true,
        ]);

        $today = now()->timezone('Asia/Jakarta');
        $user = User::factory()->create([
            'birth_date' => $today->subYears(20)->format('Y-m-d'),
        ]);

        // Visit once
        $this->actingAs($user)->get(route('home'));
        $this->assertEquals(1, Voucher::where('user_id', $user->id)->where('code', 'like', 'HBD-%')->count());

        // Visit again
        $this->actingAs($user)->get(route('home'));
        $this->assertEquals(1, Voucher::where('user_id', $user->id)->where('code', 'like', 'HBD-%')->count());
    }

    public function test_birthday_voucher_console_command()
    {
        $setting = Setting::first() ?? Setting::create([
            'store_name' => 'Savora',
            'whatsapp_number' => '6289601905406',
            'instagram_url' => 'https://instagram.com/m.afansyah_',
            'hero_title' => 'Title',
            'hero_subtitle' => 'Subtitle',
        ]);
        $setting->update([
            'birthday_voucher_is_active' => true,
        ]);

        $today = now()->timezone('Asia/Jakarta');
        $user = User::factory()->create([
            'birth_date' => $today->subYears(25)->format('Y-m-d'),
        ]);

        $this->artisan('savora:send-birthday-vouchers')
            ->assertExitCode(0);

        $this->assertTrue(Voucher::where('user_id', $user->id)->where('code', 'like', 'HBD-%')->exists());
    }
}
