<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->can_access_admin_panel && ! $this->isBlockedNow();
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'can_access_admin_panel',
        'rank',
        'total_spent',
        'rank_upgraded_at',
        'rank_notified_at',
        'is_blocked',
        'blocked_until',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_access_admin_panel' => 'boolean',
            'rank_upgraded_at' => 'datetime',
            'rank_notified_at' => 'datetime',
            'is_blocked' => 'boolean',
            'blocked_until' => 'datetime',
        ];
    }

    public function isBlockedNow(): bool
    {
        if (! $this->is_blocked) {
            return false;
        }

        if ($this->blocked_until && $this->blocked_until->isPast()) {
            return false;
        }

        return true;
    }

    public function getBlockedMessage(): string
    {
        if ($this->blocked_until) {
            return 'Akun Anda sedang terblokir sampai '.$this->blocked_until->translatedFormat('d F Y H:i').'.';
        }

        return 'Akun Anda telah diblokir secara permanen.';
    }

    // ─── Ranks Definition ─────────────────────────────────────────────────────
    public static array $ranks = [
        'reguler' => ['label' => 'Reguler',  'min' => 0,          'icon' => '⚪', 'color' => 'gray',   'hex' => '#6b7280'],
        'perunggu' => ['label' => 'Perunggu', 'min' => 200000,     'icon' => '🥉', 'color' => 'bronze', 'hex' => '#cd7f32'],
        'perak' => ['label' => 'Perak',    'min' => 1000000,    'icon' => '🥈', 'color' => 'silver', 'hex' => '#9ca3af'],
        'emas' => ['label' => 'Emas',     'min' => 3000000,    'icon' => '🥇', 'color' => 'gold',   'hex' => '#e2c86e'],
        'platinum' => ['label' => 'Platinum', 'min' => 10000000,   'icon' => '💎', 'color' => 'cyan',   'hex' => '#22d3ee'],
        'diamond' => ['label' => 'VIP Diamond', 'min' => 25000000, 'icon' => '👑', 'color' => 'purple', 'hex' => '#a855f7'],
    ];

    // ─── Rank Calculation ─────────────────────────────────────────────────────
    public static function calculateRank(int $totalSpent): string
    {
        $rank = 'reguler';
        foreach (self::$ranks as $key => $info) {
            if ($totalSpent >= $info['min']) {
                $rank = $key;
            }
        }

        return $rank;
    }

    public function updateTotalSpent(): void
    {
        $total = $this->orders()
            ->whereIn('status', ['paid', 'shipped', 'completed'])
            ->sum('total_amount');

        $oldRank = $this->rank;
        $newRank = self::calculateRank((int) $total);
        $rankKeys = array_keys(self::$ranks);
        $rankedUp = array_search($newRank, $rankKeys) > array_search($oldRank, $rankKeys);

        $this->update([
            'total_spent' => $total,
            'rank' => $newRank,
            'rank_upgraded_at' => $rankedUp ? now() : $this->rank_upgraded_at,
            'rank_notified_at' => $rankedUp ? null : $this->rank_notified_at,
        ]);
    }

    // ─── Rank Info Helpers ────────────────────────────────────────────────────
    public function getRankInfoAttribute(): array
    {
        return self::$ranks[$this->rank] ?? self::$ranks['reguler'];
    }

    public function getRankLabelAttribute(): string
    {
        return $this->rank_info['label'];
    }

    public function getRankIconAttribute(): string
    {
        return $this->rank_info['icon'];
    }

    public function getRankHexAttribute(): string
    {
        return $this->rank_info['hex'];
    }

    public function getNextRankInfoAttribute(): ?array
    {
        $keys = array_keys(self::$ranks);
        $current = array_search($this->rank, $keys);
        if ($current === false || $current >= count($keys) - 1) {
            return null;
        }

        return self::$ranks[$keys[$current + 1]];
    }

    public function getRankProgressAttribute(): int
    {
        $currentMin = self::$ranks[$this->rank]['min'];
        $next = $this->next_rank_info;
        if (! $next) {
            return 100;
        }
        $nextMin = $next['min'];
        if ($nextMin === $currentMin) {
            return 100;
        }
        $progress = ($this->total_spent - $currentMin) / ($nextMin - $currentMin) * 100;

        return (int) min(max($progress, 0), 100);
    }

    public function getRemainingForNextRankAttribute(): int
    {
        $next = $this->next_rank_info;
        if (! $next) {
            return 0;
        }

        return max(0, $next['min'] - $this->total_spent);
    }

    // ─── Relationships ────────────────────────────────────────────────────────
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
