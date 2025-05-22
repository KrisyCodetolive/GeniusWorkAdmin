<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Entreprise;
use App\Traits\BelongsToEntreprise;

class Politique extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'annuler_horaires_si_sortie_manquee',
        'nombre_pointages_par_jour',
        'activer_pauses',
        'activer_heures_supplementaires',
        'notifier_utilisateurs',
        'tolerance_retard',
        'tolerance_depart_anticipe',
        'autoriser_permutations',
        'autoriser_recuperations',
        'autoriser_travail_weekend',
        'regles_presence',
        'regles_conges',
        'regles_supplementaires',
        'configuration'
    ];

    protected $casts = [
        'annuler_horaires_si_sortie_manquee' => 'boolean',
        'activer_pauses' => 'boolean',
        'activer_heures_supplementaires' => 'boolean',
        'notifier_utilisateurs' => 'boolean',
        'autoriser_permutations' => 'boolean',
        'autoriser_recuperations' => 'boolean',
        'autoriser_travail_weekend' => 'boolean',
        'regles_presence' => 'json',
        'regles_conges' => 'json',
        'regles_supplementaires' => 'json',
        'configuration' => 'json'
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Helpers
    public function estRetardTolere($minutes)
    {
        return $minutes <= $this->tolerance_retard;
    }

    public function estDepartAnticipeTolere($minutes)
    {
        return $minutes <= $this->tolerance_depart_anticipe;
    }

    public function doitAnnulerHoraires()
    {
        return $this->annuler_horaires_si_sortie_manquee;
    }

    public function autorisePermutations()
    {
        return $this->autoriser_permutations;
    }

    public function autoriseRecuperations()
    {
        return $this->autoriser_recuperations;
    }

    public function autoriseTravailWeekend()
    {
        return $this->autoriser_travail_weekend;
    }

    public function verifierReglePresence($type, $donnees)
    {
        $regles = $this->regles_presence ?? [];
        if (!isset($regles[$type])) return true;

        // Logique de vérification des règles de présence
        return true;
    }

    public function verifierRegleConge($type, $donnees)
    {
        $regles = $this->regles_conges ?? [];
        if (!isset($regles[$type])) return true;

        // Logique de vérification des règles de congés
        return true;
    }

    public function verifierRegleSupplementaire($type, $donnees)
    {
        $regles = $this->regles_supplementaires ?? [];
        if (!isset($regles[$type])) return true;

        // Logique de vérification des règles d'heures supplémentaires
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
}
