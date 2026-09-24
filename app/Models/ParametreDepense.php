<?php

namespace App\Models;

use App\Traits\BelongsToEntreprise;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ParametreDepense extends Model
{
    use BelongsToEntreprise, HasUuids;

    public const SEUIL_PAR_DEFAUT = 500000;

    protected $table = 'parametres_depense';

    protected $fillable = [
        'entreprise_id',
        'seuil_validation_ceo',
        'ceo_user_id',
        'prefixe_reference',
        'devise',
    ];

    protected $casts = [
        'seuil_validation_ceo' => 'decimal:2',
    ];

    public function ceo()
    {
        return $this->belongsTo(User::class, 'ceo_user_id');
    }

    public const CATEGORIES_PAR_DEFAUT = [
        'Fournitures de bureau' => '6041',
        'Transport et déplacements' => '6181',
        'Frais de mission' => '6183',
        'Carburant' => '6042',
        'Entretien et réparations' => '624',
        'Services extérieurs' => '632',
        'Avance sur salaire' => '421',
        'Autres dépenses' => '658',
    ];

    /**
     * Paramètres d'une entreprise, créés avec les valeurs par défaut au premier accès
     * (avec un jeu de catégories de départ, pour qu'une demande puisse être saisie tout de suite).
     */
    public static function pour(string $entrepriseId): self
    {
        $parametres = static::withoutGlobalScopes()->firstOrCreate(
            ['entreprise_id' => $entrepriseId],
            [
                'seuil_validation_ceo' => self::SEUIL_PAR_DEFAUT,
                'prefixe_reference' => 'DEP',
                'devise' => 'FCFA',
            ]
        );

        if ($parametres->wasRecentlyCreated
            && ! CategorieDepense::withoutGlobalScopes()->where('entreprise_id', $entrepriseId)->exists()) {
            foreach (self::CATEGORIES_PAR_DEFAUT as $nom => $code) {
                CategorieDepense::create([
                    'entreprise_id' => $entrepriseId,
                    'nom' => $nom,
                    'code_comptable' => $code,
                ]);
            }
        }

        return $parametres;
    }

    /**
     * Le CEO est celui désigné dans les paramètres ; à défaut, l'administrateur de l'entreprise.
     */
    public function estCeo(User $user): bool
    {
        if ($this->ceo_user_id) {
            return $user->id === $this->ceo_user_id;
        }

        return $user->isAdmin() && $user->entreprise_id === $this->entreprise_id;
    }

    /**
     * Utilisateurs à prévenir quand une demande attend la validation du CEO.
     */
    public function ceos()
    {
        if ($this->ceo_user_id) {
            return User::whereKey($this->ceo_user_id)->get();
        }

        return User::where('entreprise_id', $this->entreprise_id)->where('role', 'admin')->get();
    }
}
