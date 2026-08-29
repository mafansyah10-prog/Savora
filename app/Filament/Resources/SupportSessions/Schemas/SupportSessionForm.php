<?php

namespace App\Filament\Resources\SupportSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupportSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('session_token')
                    ->label('ID Sesi / Token')
                    ->disabled()
                    ->dehydrated(false),
                
                TextInput::make('customer_info')
                    ->label('Pelanggan')
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if (!$record) return;
                        
                        $user = $record->user;
                        if ($user) {
                            $component->state("{$user->name} ({$user->email})");
                        } else {
                            $component->state("Tamu / Guest (" . substr($record->session_token, 0, 12) . "...)");
                        }
                    }),

                Select::make('status')
                    ->label('Status Sesi')
                    ->options([
                        'pending' => 'Menunggu Balasan (Pending)',
                        'active' => 'Aktif (Active)',
                        'resolved' => 'Selesai (Resolved)',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('duration_minutes')
                    ->label('Durasi Sesi Chat (Menit)')
                    ->numeric()
                    ->default(15)
                    ->required()
                    ->dehydrated(false)
                    ->visible(fn ($record) => $record && $record->status !== 'resolved'),

                DateTimePicker::make('expires_at')
                    ->label('Batas Waktu Sesi (Expires At)')
                    ->disabled()
                    ->timezone('Asia/Jakarta'),

                Placeholder::make('chat_history')
                    ->label('Riwayat Percakapan')
                    ->view('filament.components.chat-history')
                    ->columnSpanFull(),

                Textarea::make('reply_message')
                    ->label('Tulis Balasan Admin')
                    ->placeholder('Ketik pesan balasan ke customer di sini, lalu klik Simpan...')
                    ->rows(3)
                    ->columnSpanFull()
                    ->dehydrated(false),
            ]);
    }
}
