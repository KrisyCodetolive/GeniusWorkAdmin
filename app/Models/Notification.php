<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\User;
use App\Models\Entreprise;
use App\Traits\BelongsToEntreprise;

class Notification extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'user_id',
        'entreprise_id',
        'notifiable_type',
        'notifiable_id',
        'type',
        'message',
        'data',
        'date_envoi',
        'date_lecture',
        'read_at',
        'canal',
        'priorite',
        'statut',
        'status',
        'erreur',
        'tentatives',
        'prochaine_tentative',
        'email_envoye',
        'sms_envoye',
        'lu',
        'canaux_envoyes'
    ];

    protected $casts = [
        'data' => 'json',
        'date_envoi' => 'datetime',
        'date_lecture' => 'datetime',
        'read_at' => 'datetime',
        'prochaine_tentative' => 'datetime',
        'email_envoye' => 'boolean',
        'sms_envoye' => 'boolean',
        'lu' => 'boolean',
        'canaux_envoyes' => 'json'
    ];

    // Relations
    public function notifiable()
    {
        return $this->morphTo();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Scopes
    public function scopeNonLues($query)
    {
        return $query->whereNull('date_lecture')->where('lu', false);
    }

    public function scopeLues($query)
    {
        return $query->whereNotNull('date_lecture')->orWhere('lu', true);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeEnvoyees($query)
    {
        return $query->where('statut', 'envoye');
    }

    public function scopeEchouees($query)
    {
        return $query->where('statut', 'echec');
    }

    public function scopeParCanal($query, $canal)
    {
        return $query->where('canal', $canal);
    }

    public function scopeParPriorite($query, $priorite)
    {
        return $query->where('priorite', $priorite);
    }
    
    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    public function scopeParUtilisateur($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    public function scopeParEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }
    
    public function scopeEmailEnvoye($query)
    {
        return $query->where('email_envoye', true);
    }
    
    public function scopeSmsEnvoye($query)
    {
        return $query->where('sms_envoye', true);
    }

    // Helpers
    public function marquerCommeLue()
    {
        $this->update([
            'date_lecture' => now(),
            'read_at' => now(),
            'statut' => 'lu',
            'status' => 'read',
            'lu' => true
        ]);
    }

    public function marquerCommeEnvoyee()
    {
        $this->update([
            'date_envoi' => now(),
            'statut' => 'envoye',
            'status' => 'sent',
            'erreur' => null
        ]);
    }

    public function marquerCommeEchouee($erreur)
    {
        $this->increment('tentatives');
        $this->update([
            'statut' => 'echec',
            'status' => 'failed',
            'erreur' => $erreur,
            'prochaine_tentative' => $this->calculerProchaineRetentative()
        ]);
    }
    
    public function marquerEmailEnvoye()
    {
        $this->update([
            'email_envoye' => true
        ]);
    }
    
    public function marquerSmsEnvoye()
    {
        $this->update([
            'sms_envoye' => true
        ]);
    }

    public function estLue()
    {
        return !is_null($this->date_lecture) || $this->lu;
    }

    public function estEnvoyee()
    {
        return $this->statut === 'envoye';
    }

    public function estEchouee()
    {
        return $this->statut === 'echec';
    }
    
    public function emailEstEnvoye()
    {
        return $this->email_envoye;
    }
    
    public function smsEstEnvoye()
    {
        return $this->sms_envoye;
    }

    protected function calculerProchaineRetentative()
    {
        // Logique exponentielle de backoff
        $delai = min(pow(2, $this->tentatives) * 5, 60); // 5min, 10min, 20min, 40min, max 60min
        return now()->addMinutes($delai);
    }

    /**
     * Obtient la couleur du badge pour le type de notification
     *
     * @return string
     */
    public function getBadgeColor()
    {
        $colors = [
            'retard' => 'warning',
            'absence' => 'danger',
            'sortie_manquante' => 'info',
            'conge_approuve' => 'success',
            'conge_refuse' => 'danger',
            'rappel_presence' => 'primary',
        ];
        
        return $colors[$this->type] ?? 'secondary';
    }
    
    /**
     * Obtient la couleur du badge pour le statut de la notification
     *
     * @return string
     */
    public function getStatusColor()
    {
        $colors = [
            'created' => 'secondary',
            'sent' => 'primary',
            'delivered' => 'info',
            'read' => 'success',
            'failed' => 'danger',
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }
    
    /**
     * Vérifie si la notification a été envoyée via un canal spécifique
     *
     * @param string $canal Nom du canal (email, sms, app)
     * @return bool
     */
    public function estEnvoyeeViaCanal($canal)
    {
        $canaux = json_decode($this->canaux_envoyes, true) ?: [];
        return in_array($canal, $canaux);
    }
}
