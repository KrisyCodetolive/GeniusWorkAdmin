<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;
use Illuminate\Support\Str;

class Visiteur extends Model
{
    use HasFactory, SoftDeletes, BelongsToEntreprise, HasUuids;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entreprise_id',
        'nom',
        'prenom',
        'telephone',
        'email',
        'code_visiteur',
        'organisation',
        'fonction',
        'notes',
        'photo',
        'piece_identite',
        'statut'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Les relations qui doivent être chargées automatiquement.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($visiteur) {
            // Générer un code visiteur unique s'il n'est pas déjà défini
            if (empty($visiteur->code_visiteur)) {
                $visiteur->code_visiteur = self::generateUniqueVisitorCode();
            }
        });
    }

    /**
     * Obtenir l'entreprise associée au visiteur.
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id', 'id');
    }

    /**
     * Obtenir les visites associées au visiteur.
     */
    public function visites()
    {
        return $this->hasMany(Visite::class);
    }

    /**
     * Vérifie si le visiteur est actif.
     */
    public function estActif()
    {
        return $this->statut === 'actif';
    }

    /**
     * Génère un code visiteur unique.
     *
     * @return string
     */
    public static function generateUniqueVisitorCode()
    {
        do {
            // Format: VIS-XXXXX (où X est un chiffre ou une lettre)
            $code = 'VIS-' . strtoupper(Str::random(5));
        } while (self::where('code_visiteur', $code)->exists());

        return $code;
    }

    /**
     * Recherche un visiteur par son numéro de téléphone et son entreprise.
     *
     * @param string $telephone
     * @param int $entrepriseId
     * @return Visiteur|null
     */
    public static function findByTelephone($telephone, $entrepriseId)
    {
        return self::where('telephone', $telephone)
                  ->where('entreprise_id', $entrepriseId)
                  ->first();
    }

    /**
     * Recherche un visiteur par son code visiteur.
     *
     * @param string $code
     * @return Visiteur|null
     */
    public static function findByCode($code)
    {
        return self::where('code_visiteur', $code)->first();
    }

    /**
     * Scope pour filtrer les visiteurs actifs.
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope pour filtrer les visiteurs par entreprise.
     */
    public function scopeParEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Retourne le nom complet du visiteur.
     *
     * @return string
     */
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
