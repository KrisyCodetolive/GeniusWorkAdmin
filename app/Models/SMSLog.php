<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToEntreprise;
use Carbon\Carbon;

class SMSLog extends Model
{
    use BelongsToEntreprise;
    
    protected $table = 'sms_logs';
    protected $fillable = [
        'phone_number',
        'message',
        'status',
        'error_message',
        'attempts',
        'sent_at',
        'notification_type',
        'entreprise_id',
        'notifiable_id',
        'notifiable_type',
        'last_attempt'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'last_attempt' => 'datetime',
        'attempts' => 'integer',
    ];

    // Statuts possibles
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_PENDING = 'pending';

    // Relations
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Scopes pour le filtrage
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    // Méthodes utilitaires
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => Carbon::now(),
        ]);
    }

    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function canBeRetried(): bool
    {
        return $this->status === self::STATUS_FAILED && $this->attempts < config('sms.max_attempts', 3);
    }

    public function retry(): void
    {
        if ($this->canBeRetried()) {
            $this->update([
                'status' => self::STATUS_PENDING,
                'error_message' => null,
            ]);
        }
    }

    // Statistiques
    public static function getStats(int $days = 30): array
    {
        $query = self::where('created_at', '>=', Carbon::now()->subDays($days));
        
        return [
            'total' => $query->count(),
            'sent' => $query->sent()->count(),
            'failed' => $query->failed()->count(),
            'pending' => $query->pending()->count(),
            'success_rate' => $query->count() > 0 
                ? round(($query->sent()->count() / $query->count()) * 100, 2)
                : 0,
            'average_attempts' => round($query->average('attempts') ?? 0, 2),
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check() && !$model->entreprise_id) {
                $model->entreprise_id = auth()->user()->entreprise_id;
            }
        });
    }
}
