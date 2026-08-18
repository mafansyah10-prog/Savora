<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ManageSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan';
    }

    public function getTitle(): string
    {
        return 'Pengaturan Toko';
    }

    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = Setting::first();
        if ($setting) {
            $this->form->fill($setting->toArray());
        } else {
            $this->form->fill();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Pengaturan nama dan kontak toko.')
                    ->components([
                        Forms\Components\TextInput::make('store_name')
                            ->label('Nama Toko')
                            ->required(),
                        Forms\Components\TextInput::make('whatsapp_number')
                            ->label('Nomor Telepon')
                            ->helperText('Gunakan format internasional tanpa tanda +, contoh: 6281234567890')
                            ->required(),
                        Forms\Components\TextInput::make('instagram_url')
                            ->label('Tautan Instagram')
                            ->url()
                            ->required(),
                        Forms\Components\Textarea::make('store_address')
                            ->label('Alamat Toko')
                            ->rows(3)
                            ->required(),
                    ])->columns(2),

                Section::make('Status & Jam Operasional Toko')
                    ->description('Atur status buka/tutup toko serta jam operasional buka dan tutup secara otomatis.')
                    ->components([
                        Forms\Components\Toggle::make('is_store_open')
                            ->label('Buka Toko')
                            ->helperText('Jika dinonaktifkan, toko akan ditutup secara manual dan pelanggan tidak bisa memesan.')
                            ->default(true)
                            ->columnSpanFull(),

                        Tabs::make('OperationalSchedules')
                            ->tabs([
                                Tab::make('Jadwal Mingguan')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                // Monday
                                                Forms\Components\Toggle::make('weekly_schedule.monday.is_open')
                                                    ->label('Senin - Buka Toko')
                                                    ->default(true),
                                                Forms\Components\TimePicker::make('weekly_schedule.monday.open_time')
                                                    ->label('Jam Buka')
                                                    ->seconds(false)
                                                    ->nullable(),
                                                Forms\Components\TimePicker::make('weekly_schedule.monday.close_time')
                                                    ->label('Jam Tutup')
                                                    ->seconds(false)
                                                    ->nullable(),

                                                // Tuesday
                                                Forms\Components\Toggle::make('weekly_schedule.tuesday.is_open')
                                                    ->label('Selasa - Buka Toko')
                                                    ->default(true),
                                                Forms\Components\TimePicker::make('weekly_schedule.tuesday.open_time')
                                                    ->label('Jam Buka')
                                                    ->seconds(false)
                                                    ->nullable(),
                                                Forms\Components\TimePicker::make('weekly_schedule.tuesday.close_time')
                                                    ->label('Jam Tutup')
                                                    ->seconds(false)
                                                    ->nullable(),

                                                // Wednesday
                                                Forms\Components\Toggle::make('weekly_schedule.wednesday.is_open')
                                                    ->label('Rabu - Buka Toko')
                                                    ->default(true),
                                                Forms\Components\TimePicker::make('weekly_schedule.wednesday.open_time')
                                                    ->label('Jam Buka')
                                                    ->seconds(false)
                                                    ->nullable(),
                                                Forms\Components\TimePicker::make('weekly_schedule.wednesday.close_time')
                                                    ->label('Jam Tutup')
                                                    ->seconds(false)
                                                    ->nullable(),

                                                // Thursday
                                                Forms\Components\Toggle::make('weekly_schedule.thursday.is_open')
                                                    ->label('Kamis - Buka Toko')
                                                    ->default(true),
                                                Forms\Components\TimePicker::make('weekly_schedule.thursday.open_time')
                                                    ->label('Jam Buka')
                                                    ->seconds(false)
                                                    ->nullable(),
                                                Forms\Components\TimePicker::make('weekly_schedule.thursday.close_time')
                                                    ->label('Jam Tutup')
                                                    ->seconds(false)
                                                    ->nullable(),

                                                // Friday
                                                Forms\Components\Toggle::make('weekly_schedule.friday.is_open')
                                                    ->label('Jumat - Buka Toko')
                                                    ->default(true),
                                                Forms\Components\TimePicker::make('weekly_schedule.friday.open_time')
                                                    ->label('Jam Buka')
                                                    ->seconds(false)
                                                    ->nullable(),
                                                Forms\Components\TimePicker::make('weekly_schedule.friday.close_time')
                                                    ->label('Jam Tutup')
                                                    ->seconds(false)
                                                    ->nullable(),

                                                // Saturday
                                                Forms\Components\Toggle::make('weekly_schedule.saturday.is_open')
                                                    ->label('Sabtu - Buka Toko')
                                                    ->default(true),
                                                Forms\Components\TimePicker::make('weekly_schedule.saturday.open_time')
                                                    ->label('Jam Buka')
                                                    ->seconds(false)
                                                    ->nullable(),
                                                Forms\Components\TimePicker::make('weekly_schedule.saturday.close_time')
                                                    ->label('Jam Tutup')
                                                    ->seconds(false)
                                                    ->nullable(),

                                                // Sunday
                                                Forms\Components\Toggle::make('weekly_schedule.sunday.is_open')
                                                    ->label('Minggu - Buka Toko')
                                                    ->default(true),
                                                Forms\Components\TimePicker::make('weekly_schedule.sunday.open_time')
                                                    ->label('Jam Buka')
                                                    ->seconds(false)
                                                    ->nullable(),
                                                Forms\Components\TimePicker::make('weekly_schedule.sunday.close_time')
                                                    ->label('Jam Tutup')
                                                    ->seconds(false)
                                                    ->nullable(),
                                            ]),
                                    ]),

                                Tab::make('Jadwal Tanggal Khusus / Hari Libur')
                                    ->schema([
                                        Forms\Components\Repeater::make('special_schedules')
                                            ->label('Daftar Tanggal Khusus')
                                            ->schema([
                                                Forms\Components\DatePicker::make('date')
                                                    ->label('Tanggal')
                                                    ->required(),
                                                Forms\Components\Toggle::make('is_open')
                                                    ->label('Buka Toko')
                                                    ->default(false),
                                                Forms\Components\TimePicker::make('open_time')
                                                    ->label('Jam Buka')
                                                    ->seconds(false)
                                                    ->nullable(),
                                                Forms\Components\TimePicker::make('close_time')
                                                    ->label('Jam Tutup')
                                                    ->seconds(false)
                                                    ->nullable(),
                                                Forms\Components\TextInput::make('note')
                                                    ->label('Keterangan / Alasan')
                                                    ->placeholder('Contoh: Libur Lebaran, Tutup Cepat, dll.')
                                                    ->required(),
                                            ])
                                            ->columns(5)
                                            ->default([])
                                            ->columnSpanFull(),
                                    ]),

                                Tab::make('Global (Sederhana)')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TimePicker::make('store_open_time')
                                                    ->label('Jam Buka Global')
                                                    ->seconds(false)
                                                    ->nullable()
                                                    ->helperText('Hanya digunakan jika jadwal mingguan kosong'),
                                                Forms\Components\TimePicker::make('store_close_time')
                                                    ->label('Jam Tutup Global')
                                                    ->seconds(false)
                                                    ->nullable()
                                                    ->helperText('Hanya digunakan jika jadwal mingguan kosong'),
                                            ])
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make('Integrasi Pakasir Payment Gateway')
                    ->description('Pengaturan untuk pembayaran otomatis via Pakasir.')
                    ->components([
                        Forms\Components\Toggle::make('pakasir_is_active')
                            ->label('Aktifkan Pakasir Payment Gateway')
                            ->default(false),
                        Forms\Components\TextInput::make('pakasir_project')
                            ->label('Pakasir Project Slug')
                            ->placeholder('Contoh: nama-proyek-anda')
                            ->required(fn ($get) => $get('pakasir_is_active')),
                        Forms\Components\TextInput::make('pakasir_api_key')
                            ->label('Pakasir API Key')
                            ->password()
                            ->revealable()
                            ->required(fn ($get) => $get('pakasir_is_active')),
                    ])->columns(3),

                Section::make('Integrasi Midtrans Payment Gateway')
                    ->description('Pengaturan untuk pembayaran otomatis via Midtrans (termasuk DANA, QRIS, dll).')
                    ->components([
                        Forms\Components\Toggle::make('midtrans_is_active')
                            ->label('Aktifkan Midtrans')
                            ->default(false)
                            ->live(),
                        Forms\Components\TextInput::make('midtrans_client_key')
                            ->label('Midtrans Client Key')
                            ->required(fn ($get) => $get('midtrans_is_active')),
                        Forms\Components\TextInput::make('midtrans_server_key')
                            ->label('Midtrans Server Key')
                            ->password()
                            ->revealable()
                            ->required(fn ($get) => $get('midtrans_is_active')),
                        Forms\Components\Toggle::make('midtrans_is_production')
                            ->label('Mode Production (Live)')
                            ->default(false),
                    ])->columns(2),

                Section::make('Pembayaran Manual (Transfer Bank / QRIS)')
                    ->description('Pengaturan untuk pembayaran manual ke rekening bank atau QRIS toko. Anda dapat menambahkan beberapa metode pembayaran manual.')
                    ->components([
                        Forms\Components\Toggle::make('manual_payment_is_active')
                            ->label('Aktifkan Pembayaran Manual')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('manual_payment_methods')
                            ->label('Metode Pembayaran Manual')
                            ->visible(fn ($get) => $get('manual_payment_is_active'))
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Metode Pembayaran')
                                    ->placeholder('Contoh: Transfer Bank BCA, DANA, OVO')
                                    ->required(),
                                Forms\Components\TextInput::make('account_number')
                                    ->label('Nomor Rekening / HP')
                                    ->required(),
                                Forms\Components\TextInput::make('account_name')
                                    ->label('Atas Nama')
                                    ->required(),
                                Forms\Components\FileUpload::make('qris_image')
                                    ->label('QRIS Pembayaran (Opsional)')
                                    ->image()
                                    ->directory('qris')
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->default([]),
                    ])->columns(2),

                Section::make('Voucher Pendaftaran Pengguna Baru')
                    ->description('Pengaturan voucher otomatis yang didapatkan ketika customer baru mendaftar.')
                    ->components([
                        Forms\Components\Toggle::make('new_user_voucher_is_active')
                            ->label('Aktifkan Voucher Pengguna Baru')
                            ->helperText('Jika diaktifkan, pengguna baru yang mendaftar akan otomatis mendapatkan voucher promo.')
                            ->default(true)
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('new_user_voucher_type')
                            ->label('Tipe Potongan')
                            ->options([
                                'fixed' => 'Nominal Tetap (Rupiah)',
                                'percent' => 'Persentase (%)',
                            ])
                            ->required()
                            ->default('fixed')
                            ->visible(fn ($get) => $get('new_user_voucher_is_active'))
                            ->live(),

                        Forms\Components\TextInput::make('new_user_voucher_value')
                            ->label('Nilai Potongan')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->visible(fn ($get) => $get('new_user_voucher_is_active'))
                            ->helperText(fn ($get) => $get('new_user_voucher_type') === 'percent' ? 'Masukkan angka persentase (1 - 100)' : 'Masukkan nominal rupiah potongan'),

                        Forms\Components\TextInput::make('new_user_voucher_min_order_amount')
                            ->label('Minimal Pembelian (Rupiah)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->visible(fn ($get) => $get('new_user_voucher_is_active'))
                            ->helperText('Batas minimum total belanja agar voucher ini dapat digunakan'),

                        Forms\Components\TextInput::make('new_user_voucher_expires_in_days')
                            ->label('Masa Berlaku Voucher (Hari)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->visible(fn ($get) => $get('new_user_voucher_is_active'))
                            ->helperText('Jumlah hari voucher tetap aktif setelah customer mendaftar'),

                        Forms\Components\TextInput::make('new_user_voucher_limit_per_user')
                            ->label('Batas Pemakaian Per Pengguna')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->visible(fn ($get) => $get('new_user_voucher_is_active'))
                            ->helperText('Jumlah maksimal voucher pendaftaran ini dapat digunakan oleh setiap pengguna baru (biasanya diisi 1)'),
                    ])->columns(2),

                Section::make('Pengaturan Batas Minimal Pangkat Loyalitas')
                    ->description('Tentukan nominal belanja minimum untuk naik ke setiap peringkat loyalty.')
                    ->components([
                        Forms\Components\TextInput::make('rank_bronze_min')
                            ->label('Batas Minimum Perunggu (🥉)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('rank_silver_min')
                            ->label('Batas Minimum Perak (🥈)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('rank_gold_min')
                            ->label('Batas Minimum Emas (🥇)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('rank_platinum_min')
                            ->label('Batas Minimum Platinum (💎)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('rank_diamond_min')
                            ->label('Batas Minimum VIP Diamond (👑)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('Rp'),
                    ])->columns(2),

                Section::make('Tampilan Beranda')
                    ->description('Teks yang muncul di halaman utama (Hero Banner & Tentang Kami).')
                    ->components([
                        Forms\Components\TextInput::make('hero_title')
                            ->label('Judul Banner Utama')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('hero_subtitle')
                            ->label('Sub-judul Banner Utama')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('about_text')
                            ->label('Teks Tentang Kami')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = Setting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            Setting::create($data);
        }

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }
}
