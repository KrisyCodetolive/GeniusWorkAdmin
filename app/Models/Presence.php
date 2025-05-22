<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Presence extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'employeur_id',
        'site_id',
        'methode_pointage_id',
        'raison_sortie_id',
        'appareil_id',
        'date_heure_entree',
        'date_heure_sortie',
        'type',
        'date_heure',
        'latitude',
        'longitude',
        'precision_geo',
        'adresse_ip',
        'appareil',
        'navigateur',
        'photo_url',
        'signature_url',
        'qr_code',
        'nfc_tag',
        'source',
        'statut',
        'statut_validation',
        'commentaire',
        'validateur_id',
        'date_validation',
        'distance_site',
        'verification_data',
        'webauthn_credential_id',
        'minutes_travaillees',
        'minutes_retard',
        'minutes_supplementaires',
        'minutes_pause',
        'date_heure_pause_debut',
        'date_heure_pause_fin',
        'supplementaire_id'
    ];

    protected $casts = [
        'date_heure' => 'datetime',
        'date_heure_entree' => 'datetime',
        'date_heure_sortie' => 'datetime',
        'date_heure_pause_debut' => 'datetime',
        'date_heure_pause_fin' => 'datetime',
        'date_validation' => 'datetime',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'precision_geo' => 'decimal:2',
        'distance_site' => 'decimal:2',
        'verification_data' => 'array',
        'minutes_supplementaires' => 'integer',
        'minutes_pause' => 'integer'
    ];

    // Relations
    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
    }


    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function methodePointage()
    {
        return $this->belongsTo(MethodePointage::class);
    }

    public function raisonSortie()
    {
        return $this->belongsTo(RaisonSortie::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }

    public function jour()
    {
        return $this->belongsTo(Jour::class);
    }

    public function webAuthnCredential()
    {
        return $this->belongsTo(WebAuthnCredential::class, 'webauthn_credential_id');
    }

    /**
     * Get the biometric device that recorded this presence.
     */
    public function appareilBiometrique()
    {
        return $this->belongsTo(\App\Models\AppareilBiometrique::class, 'appareil_id');
    }

    public function supplementaire()
    {
        return $this->belongsTo(Supplementaire::class);
    }
    
    /**
     * Get the entreprise associated with the presence through employeur.
     */
    public function entreprise()
    {
        return $this->hasOneThrough(
            Entreprise::class,
            Employeur::class,
            'id', // Clé étrangère sur Employeur qui pointe vers Presence
            'id', // Clé étrangère sur Entreprise qui pointe vers Employeur
            'employeur_id', // Clé locale sur Presence
            'entreprise_id' // Clé locale sur Employeur
        );
    }

    // Scopes
    public function scopeEntree($query)
    {
        return $query->where('type', 'entree');
    }

    public function scopeSortie($query)
    {
        return $query->where('type', 'sortie');
    }

    public function scopePause($query)
    {
        return $query->whereIn('type', ['pause_debut', 'pause_fin']);
    }

    public function scopePauseDebut($query)
    {
        return $query->where('type', 'pause_debut');
    }

    public function scopePauseFin($query)
    {
        return $query->where('type', 'pause_fin');
    }

    public function scopeDate($query, $date)
    {
        return $query->whereDate('date_heure', $date);
    }

    public function scopePeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_heure', [$debut, $fin]);
    }

    public function scopeValide($query)
    {
        return $query->whereNotNull('validateur_id');
    }

    public function scopeNonValide($query)
    {
        return $query->whereNull('validateur_id');
    }

    public function scopeParSource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeParMethode($query, $methodeId)
    {
        return $query->where('methode_pointage_id', $methodeId);
    }

    public function scopeParSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeWebPointage($query)
    {
        return $query->whereHas('methodePointage', function($q) {
            $q->where('code', 'like', 'WEB-%');
        });
    }

    // Helpers
    public function valider(User $validateur)
    {
        $this->update([
            'validateur_id' => $validateur->id,
            'date_validation' => now(),
            'statut_validation' => 'approuve'
        ]);
    }

    public function annuler($commentaire = null)
    {
        $this->update([
            'statut' => 'annule',
            'commentaire' => $commentaire
        ]);
    }

    public function getTempsPresence()
    {
        if ($this->type !== 'sortie') return null;

        $entree = self::where('user_id', $this->user_id)
            ->where('type', 'entree')
            ->where('date_heure', '<', $this->date_heure)
            ->orderBy('date_heure', 'desc')
            ->first();

        if (!$entree) return null;

        return $this->date_heure->diffInMinutes($entree->date_heure);
    }

    public function getTempsPause()
    {
        if ($this->type !== 'pause_fin') return null;

        $pauseDebut = self::where('user_id', $this->user_id)
            ->where('type', 'pause_debut')
            ->where('date_heure', '<', $this->date_heure)
            ->orderBy('date_heure', 'desc')
            ->first();

        if (!$pauseDebut) return null;

        return $this->date_heure->diffInMinutes($pauseDebut->date_heure);
    }

    public function isValide()
    {
        return !is_null($this->validateur_id);
    }

    public function hasLocation()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function isHorsSite()
    {
        return $this->distance_site > $this->site->rayon_geofencing;
    }

    public function verifierGeolocalisation($latitude, $longitude, $precision = null)
    {
        if (!$this->site || !$this->site->hasGeofencing()) {
            return true;
        }

        $distance = $this->calculerDistance($latitude, $longitude, $this->site->latitude, $this->site->longitude);
        $rayonMax = $this->site->rayon_geofencing;

        return $distance <= $rayonMax;
    }

    public function calculerDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // mètres

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat/2) * sin($dLat/2) + cos($lat1) * cos($lat2) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance;
    }
}
