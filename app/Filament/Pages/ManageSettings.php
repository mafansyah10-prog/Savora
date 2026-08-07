<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

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

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Dasar')
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


                \Filament\Schemas\Components\Section::make('Integrasi Pakasir Payment Gateway')
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

                \Filament\Schemas\Components\Section::make('Voucher Pendaftaran Pengguna Baru')
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

                \Filament\Schemas\Components\Section::make('Tampilan Beranda')
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
