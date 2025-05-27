<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Abonnement;
use App\Models\Facturation;
use App\Models\FraisUsage;
use App\Models\Adresse;
use App\Models\Departement;
use App\Models\Employeur;
use App\Models\User;
use App\Models\Notification;
use App\Models\Visiteur;
use App\Models\Visite;
use Illuminate\Support\Facades\Log;

class Entreprise extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entreprise) {
            if (empty($entreprise->code)) {
                $nom = preg_replace('/[^A-Za-z0-9]/', '', $entreprise->nom);
                $prefixe = strtoupper(substr($nom, 0, 3));
                $entreprise->code = $prefixe;
                
                Log::info('Code entreprise généré', [
                    'nom' => $entreprise->nom,
                    'nom_nettoye' => $nom,
                    'code' => $prefixe
                ]);
            }
        });
    }

    protected $fillable = [
        'nom',
        'code',
        'description',
        'email',
        'telephone',
        'logo',
        'site_web',
        'secteur_activite',
        'nif',
        'rccm',
        'raison_sociale',
        'statut',
        'devise',
        'fuseau_horaire',
        'langue',
        'configuration',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'latitude',
        'longitude',
        'nombre_employes'
    ];

    protected $casts = [
        'configuration' => 'json',
        'email_verified_at' => 'datetime',
    ];

    // Relations
    
    public function filiales()
    {
        return $this->hasMany(Filiale::class);
    }
    public function sites()
    {
        return $this->hasMany(Site::class);
    }
    
    public function adresses()
    {
        return $this->hasMany(Adresse::class);
    }

    public function departements()
    {
        return $this->hasMany(Departement::class);
    }

    public function employeurs()
    {
        return $this->hasMany(Employeur::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }

    public function abonnementActif()
    {
        return $this->hasOne(Abonnement::class)->where('statut', 'actif');
    }

    public function facturations()
    {
        return $this->hasMany(Facturation::class);
    }

    public function fraisUsages()
    {
        return $this->hasMany(FraisUsage::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    
    public function visiteurs()
    {
        return $this->hasMany(Visiteur::class);
    }
    
    public function visites()
    {
        return $this->hasMany(Visite::class);
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

    public function scopeSecteur($query, $secteur)
    {
        return $query->where('secteur_activite', $secteur);
    }

    // Helpers
    public function hasValidSubscription()
    {
        return $this->abonnementActif()->exists();
    }

    public function getEmployeCount()
    {
        return $this->employeurs()->count();
    }

    public function isOverEmployeeLimit()
    {
        $limit = $this->abonnementActif->planAbonnement->nombre_employes_max;
        return $this->getEmployeCount() >= $limit;
    }
}