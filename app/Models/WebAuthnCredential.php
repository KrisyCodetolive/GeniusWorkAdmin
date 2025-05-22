<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WebAuthnCredential extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'credential_id',
        'public_key',
        'counter',
        'name',
        'type',
        'device_type',
        'is_active',
        'last_used_at'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'counter' => 'integer',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Obtenir l'utilisateur associé à cette information d'identification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marquer cette information d'identification comme utilisée.
     *
     * @param int $counter Nouvelle valeur du compteur
     * @return bool
     */
    public function markAsUsed($counter)
    {
        return $this->update([
            'counter' => $counter,
            'last_used_at' => now()
        ]);
    }

    /**
     * Désactiver cette information d'identification.
     *
     * @return bool
     */
    public function disable()
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Activer cette information d'identification.
     *
     * @return bool
     */
    public function enable()
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Vérifier si cette information d'identification est active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Scope pour filtrer les informations d'identification actives.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour filtrer par type d'appareil.
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope pour filtrer par type d'authentification.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
