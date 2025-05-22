<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class Site extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entreprise_id',
        'nom',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'latitude',
        'longitude',
        'rayon_geofencing',
        'has_geofencing',
        'statut',
        'description',
        'horaires',
        'contact_nom',
        'contact_email',
        'contact_telephone',
        'qr_token',
        'qr_generated_at'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'has_geofencing' => 'boolean',
        'horaires' => 'json',
        'qr_generated_at' => 'datetime'
    ];

    /**
     * Obtenir l'entreprise associée au site.
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Obtenir les pointages associés au site.
     */
    public function pointages()
    {
        return $this->hasMany(Presence::class);
    }

    /**
     * Obtenir les employés associés au site.
     * Récupère les utilisateurs qui appartiennent à la même entreprise que le site.
     */
    public function employes()
    {
        return $this->hasMany(User::class, 'entreprise_id', 'entreprise_id');
    }
    
    /**
     * Obtenir les visites associées au site.
     */
    public function visites()
    {
        return $this->hasMany(Visite::class);
    }

    /**
     * Vérifier si le site est actif.
     */
    public function estActif()
    {
        return $this->statut === 'actif';
    }

    /**
     * Vérifier si un point GPS est dans le rayon du site.
     */
    public function estDansRayon($latitude, $longitude)
    {
        if (!$this->has_geofencing) {
            return true;
        }

        // Calcul de la distance entre le point et le site
        $distance = $this->calculerDistance(
            $latitude,
            $longitude,
            $this->latitude,
            $this->longitude
        );

        return $distance <= $this->rayon_geofencing;
    }

    /**
     * Calculer la distance entre deux points GPS en mètres.
     */
    protected function calculerDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Formule de Haversine pour calculer la distance entre deux points GPS
        $earthRadius = 6371000; // Rayon de la terre en mètres

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Scope pour filtrer les sites actifs.
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope pour filtrer les sites par entreprise.
     */
    public function scopeParEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Scope pour filtrer les sites avec geofencing.
     */
    public function scopeAvecGeofencing($query)
    {
        return $query->where('has_geofencing', true);
    }

    /**
     * Vérifie si le QR code du site est valide
     *
     * @param string $token
     * @return bool
     */
    public function validateQRCode($token)
    {
        // Vérifier si le token correspond et n'est pas expiré (24h par défaut)
        if ($this->qr_token !== $token || 
            !$this->qr_generated_at || 
            $this->qr_generated_at->diffInHours(now()) > 24) {
            return false;
        }

        return true;
    }

    /**
     * Génère un nouveau QR code pour le site
     *
     * @return string Le token du QR code
     */
    public function generateQRCode()
    {
        // Générer un token unique
        $this->qr_token = \Illuminate\Support\Str::random(32);
        $this->qr_generated_at = now();
        $this->save();
        
        return $this->qr_token;
    }
}
