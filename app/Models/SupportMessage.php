<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_session_id',
        'sender',
        'message',
        'is_read',
    ];

    public function supportSession()
    {
        return $this->belongsTo(SupportSession::class);
    }
}
