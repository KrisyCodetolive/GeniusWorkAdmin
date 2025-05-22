<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Entreprise;
use App\Traits\BelongsToEntreprise;

class MethodePointage extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'code',
        'description',
        'necessite_photo',
        'necessite_geolocalisation',
        'necessite_signature',
        'necessite_validation',
        'autoriser_hors_site',
        'rayon_geofencing',
        'configuration',
        'validation_regles',
        'statut'
    ];

    protected $casts = [
        'necessite_photo' => 'boolean',
        'necessite_geolocalisation' => 'boolean',
        'necessite_signature' => 'boolean',
        'necessite_validation' => 'boolean',
        'autoriser_hors_site' => 'boolean',
        'configuration' => 'json',
        'validation_regles' => 'json'
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function trackings()
    {
        return $this->hasMany(Tracking::class, 'methode_pointage', 'code');
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

    public function scopeAvecPhoto($query)
    {
        return $query->where('necessite_photo', true);
    }

    public function scopeAvecGeolocalisation($query)
    {
        return $query->where('necessite_geolocalisation', true);
    }

    public function scopeAvecSignature($query)
    {
        return $query->where('necessite_signature', true);
    }

    public function scopeAvecValidation($query)
    {
        return $query->where('necessite_validation', true);
    }

    // Helpers
    public function estActif()
    {
        return $this->statut === 'actif';
    }

    public function necessitePhoto()
    {
        return $this->necessite_photo;
    }

    public function necessiteGeolocalisation()
    {
        return $this->necessite_geolocalisation;
    }

    public function necessiteSignature()
    {
        return $this->necessite_signature;
    }

    public function necessiteValidation()
    {
        return $this->necessite_validation;
    }

    public function autoriseHorsSite()
    {
        return $this->autoriser_hors_site;
    }

    public function getRayonGeofencing()
    {
        return $this->rayon_geofencing;
    }

    public function verifierValidationRegles($donnees)
    {
        $regles = $this->validation_regles ?? [];
        
        foreach ($regles as $regle => $configuration) {
            switch ($regle) {
                case 'photo':
                    if ($this->necessite_photo && empty($donnees['photo'])) {
                        return false;
                    }
                    break;
                case 'geolocalisation':
                    if ($this->necessite_geolocalisation && 
                        (empty($donnees['latitude']) || empty($donnees['longitude']))) {
                        return false;
                    }
                    break;
                case 'signature':
                    if ($this->necessite_signature && empty($donnees['signature'])) {
                        return false;
                    }
                    break;
                // Autres règles de validation personnalisées
            }
        }

        return true;
    }

    public function getConfiguration($cle, $defaut = null)
    {
        $config = $this->configuration ?? [];
        return $config[$cle] ?? $defaut;
    }

    public function setConfiguration($cle, $valeur)
    {
        $config = $this->configuration ?? [];
        $config[$cle] = $valeur;
        $this->configuration = $config;
        $this->save();
    }

    public function verifierGeofencing($latitude, $longitude, $site)
    {
        if (!$this->necessite_geolocalisation) {
            return true;
        }

        if ($this->autoriser_hors_site) {
            return true;
        }

        // Calcul de la distance entre le point de pointage et le site
        $distance = $this->calculerDistance(
            $latitude,
            $longitude,
            $site->latitude,
            $site->longitude
        );

        return $distance <= $this->rayon_geofencing;
    }

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
}
