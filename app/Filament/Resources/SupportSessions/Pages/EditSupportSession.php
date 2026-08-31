<?php

namespace App\Filament\Resources\SupportSessions\Pages;

use App\Filament\Resources\SupportSessionResource;
use App\Models\SupportMessage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupportSession extends EditRecord
{
    protected static string $resource = SupportSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $reply = $this->data['reply_message'] ?? null;
        
        if ($reply && !empty(trim($reply))) {
            // Save admin reply message
            SupportMessage::create([
                'support_session_id' => $this->record->id,
                'sender' => 'admin',
                'message' => trim($reply),
                'is_read' => false,
            ]);

            // Calculate expiration based on duration input
            $duration = $this->data['duration_minutes'] ?? null;
            $newExpiry = (!is_null($duration) && $duration !== '') ? now()->addMinutes((int) $duration) : null;

            // Set session status to active and apply expiration timestamp
            $this->record->update([
                'status' => 'active',
                'expires_at' => $newExpiry,
                'updated_at' => now(),
            ]);

            // Reload form to clear the reply textarea and update chat visualizer
            $this->fillForm();
        }
    }
}
