<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class AppareilBiometrique extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'site_id',
        'nom',
        'modele',
        'fabricant',
        'numero_serie',
        'adresse_ip',
        'port',
        'protocole', // TCP, UDP, HTTP, etc.
        'identifiant_connexion',
        'mot_de_passe',
        'cle_api',
        'configuration',
        'dernier_sync',
        'derniere_sync_auto',
        'statut', // actif, inactif, maintenance, erreur
        'sync_auto_enabled',
        'sync_logs_interval',
        'sync_users_interval',
        'sync_time_interval',
        'sync_options',
        'version_firmware',
        'capacite_empreintes',
        'capacite_visages',
        'capacite_cartes',
        'capacite_logs',
        'type_authentification', // empreinte, visage, carte, code, multiple
        'options_disponibles', // json avec les options disponibles
        'parametres_avances', // json avec les paramètres avancés
        'notes'
    ];

    protected $casts = [
        'dernier_sync' => 'datetime',
        'derniere_sync_auto' => 'datetime',
        'sync_auto_enabled' => 'boolean',
        'sync_logs_interval' => 'integer',
        'sync_users_interval' => 'integer',
        'sync_time_interval' => 'integer',
        'configuration' => 'json',
        'options_disponibles' => 'json',
        'parametres_avances' => 'json',
        'sync_options' => 'json',
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function logs()
    {
        return $this->hasMany(LogAppareilBiometrique::class);
    }

    public function pointages()
    {
        return $this->hasMany(Presence::class, 'appareil_id')->where('source', 'biometrique');
    }

    public function utilisateursEnregistres()
    {
        return $this->belongsToMany(User::class, 'appareil_biometrique_user')
            ->withPivot('identifiant_biometrique', 'type_donnee', 'date_enregistrement', 'statut')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    public function scopeEnMaintenance($query)
    {
        return $query->where('statut', 'maintenance');
    }

    public function scopeEnErreur($query)
    {
        return $query->where('statut', 'erreur');
    }

    public function scopeParSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeParModele($query, $modele)
    {
        return $query->where('modele', $modele);
    }

    public function scopeParFabricant($query, $fabricant)
    {
        return $query->where('fabricant', $fabricant);
    }

    // Scope pour les appareils configurés pour la synchronisation automatique
    public function scopeSyncAuto($query)
    {
        return $query->where('sync_auto_enabled', true);
    }

    // Helpers
    public function estActif()
    {
        return $this->statut === 'actif';
    }

    public function estConnecte()
    {
        return $this->statut === 'actif' && $this->dernier_sync && $this->dernier_sync->diffInMinutes(now()) < 15;
    }

    public function supporteEmpreintes()
    {
        $options = $this->options_disponibles ?? [];
        return isset($options['empreinte']) && $options['empreinte'] === true;
    }

    public function supporteVisage()
    {
        $options = $this->options_disponibles ?? [];
        return isset($options['visage']) && $options['visage'] === true;
    }

    public function supporteCarte()
    {
        $options = $this->options_disponibles ?? [];
        return isset($options['carte']) && $options['carte'] === true;
    }

    public function supporteCode()
    {
        $options = $this->options_disponibles ?? [];
        return isset($options['code']) && $options['code'] === true;
    }

    public function getParametre($cle, $defaut = null)
    {
        $params = $this->parametres_avances ?? [];
        return $params[$cle] ?? $defaut;
    }

    public function setParametre($cle, $valeur)
    {
        $params = $this->parametres_avances ?? [];
        $params[$cle] = $valeur;
        $this->parametres_avances = $params;
        $this->save();
    }

    public function getAdresseComplete()
    {
        return $this->adresse_ip . ':' . $this->port;
    }

    public function getUrlApi()
    {
        if ($this->protocole === 'HTTP' || $this->protocole === 'HTTPS') {
            $protocol = strtolower($this->protocole);
            return "{$protocol}://{$this->adresse_ip}:{$this->port}/api";
        }
        
        return null;
    }

    /**
     * Vérifie si l'appareil doit être synchronisé pour un type donné
     * 
     * @param string $type Type de synchronisation (logs, users, time)
     * @return bool
     */
    public function doitEtreSynchronise(string $type): bool
    {
        if (!$this->sync_auto_enabled || $this->statut !== 'actif') {
            return false;
        }
        
        if (!$this->derniere_sync_auto) {
            return true;
        }
        
        $now = now();
        $lastSync = $this->derniere_sync_auto;
        
        switch ($type) {
            case 'logs':
                return $now->diffInMinutes($lastSync) >= $this->sync_logs_interval;
                
            case 'users':
                return $now->diffInMinutes($lastSync) >= $this->sync_users_interval;
                
            case 'time':
                return $now->diffInMinutes($lastSync) >= $this->sync_time_interval;
                
            default:
                return true;
        }
    }
}
