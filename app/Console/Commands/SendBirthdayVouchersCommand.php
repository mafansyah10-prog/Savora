<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BirthdayVoucherService;
use Illuminate\Console\Command;

class SendBirthdayVouchersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'savora:send-birthday-vouchers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemberian voucher ulang tahun otomatis kepada customer yang berulang tahun hari ini';

    /**
     * Execute the console command.
     */
    public function handle(BirthdayVoucherService $service)
    {
        $this->info('Memulai pengecekan ulang tahun customer...');

        $today = now()->timezone('Asia/Jakarta');
        $monthDay = $today->format('m-d');

        // Query users with birthday today
        $users = User::whereNotNull('birth_date')
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $voucher = $service->issueVoucherIfEligible($user);
            if ($voucher) {
                $this->line("Voucher {$voucher->code} berhasil diterbitkan untuk {$user->name} (ID: {$user->id})");
                $count++;
            }
        }

        $this->info("Pengecekan selesai. {$count} voucher ulang tahun berhasil diterbitkan.");
    }
}
