<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LogAppareilBiometrique extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'appareil_biometrique_id',
        'user_id',
        'type_evenement', // connexion, deconnexion, erreur, synchronisation, pointage, configuration, etc.
        'details',
        'statut', // success, error, warning, info
        'date_evenement',
        'donnees_brutes'
    ];

    protected $casts = [
        'date_evenement' => 'datetime',
        'details' => 'json',
        'donnees_brutes' => 'json'
    ];

    // Relations
    public function appareilBiometrique()
    {
        return $this->belongsTo(AppareilBiometrique::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeParType($query, $type)
    {
        return $query->where('type_evenement', $type);
    }

    public function scopeParStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeSucces($query)
    {
        return $query->where('statut', 'success');
    }

    public function scopeErreur($query)
    {
        return $query->where('statut', 'error');
    }

    public function scopeAvertissement($query)
    {
        return $query->where('statut', 'warning');
    }

    public function scopeInfo($query)
    {
        return $query->where('statut', 'info');
    }

    public function scopeRecent($query, $heures = 24)
    {
        return $query->where('date_evenement', '>=', now()->subHours($heures));
    }

    // Helpers
    public function estSucces()
    {
        return $this->statut === 'success';
    }

    public function estErreur()
    {
        return $this->statut === 'error';
    }

    public function getDetail($cle, $defaut = null)
    {
        $details = $this->details ?? [];
        return $details[$cle] ?? $defaut;
    }
}
