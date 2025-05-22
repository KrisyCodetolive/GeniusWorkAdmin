<?php

namespace App\Models\Paie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Support\Str;

class BulletinPaie extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'bulletins_paie';

    protected $fillable = [
        'reference',
        'employeur_id',
        'entreprise_id',
        'periode_debut',
        'periode_fin',
        'date_paiement',
        'salaire_base',
        'total_indemnites',
        'total_primes',
        'salaire_brut',
        'cnps_employe',
        'igr',
        'total_retenues',
        'salaire_net',
        'cnps_employeur',
        'charges_patronales',
        'statut',
        'commentaire',
        'genere_par',
        'valide_par',
        'date_validation',
        'fichier_pdf',
        'meta_donnees'
    ];

    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'date_paiement' => 'date',
        'date_validation' => 'datetime',
        'salaire_base' => 'decimal:2',
        'total_indemnites' => 'decimal:2',
        'total_primes' => 'decimal:2',
        'salaire_brut' => 'decimal:2',
        'cnps_employe' => 'decimal:2',
        'igr' => 'decimal:2',
        'total_retenues' => 'decimal:2',
        'salaire_net' => 'decimal:2',
        'cnps_employeur' => 'decimal:2',
        'charges_patronales' => 'decimal:2',
        'meta_donnees' => 'json',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bulletin) {
            if (!$bulletin->reference) {
                $bulletin->reference = self::genererReference($bulletin->employeur_id, $bulletin->periode_fin);
            }
        });
    }

    /**
     * Génère une référence unique pour le bulletin de paie
     * 
     * @param string $employeurId
     * @param string $periodeFin
     * @return string
     */
    public static function genererReference($employeurId, $periodeFin)
    {
        $employeur = Employeur::find($employeurId);
        $date = date('Ym', strtotime($periodeFin));
        $prefix = 'BP';
        
        if ($employeur) {
            $prefix = Str::upper(substr($employeur->matricule, 0, 2));
        }
        
        $count = self::whereRaw("SUBSTRING(reference, -10, 6) = ?", [$date])->count() + 1;
        
        return $prefix . '-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relation avec l'employeur
     */
    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Relation avec l'utilisateur qui a généré le bulletin
     */
    public function generePar()
    {
        return $this->belongsTo(User::class, 'genere_par');
    }

    /**
     * Relation avec l'utilisateur qui a validé le bulletin
     */
    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    /**
     * Relation avec les éléments de paie
     */
    public function elements()
    {
        return $this->hasMany(ElementPaie::class);
    }

    /**
     * Obtenir les éléments de paie par type
     */
    public function getElementsByType($type)
    {
        return $this->elements()->where('type', $type)->get();
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('periode_debut', [$debut, $fin])
                     ->orWhereBetween('periode_fin', [$debut, $fin]);
    }

    /**
     * Scope pour filtrer par entreprise
     */
    public function scopeParEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Scope pour filtrer par employeur
     */
    public function scopeParEmployeur($query, $employeurId)
    {
        return $query->where('employeur_id', $employeurId);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeParStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Vérifie si le bulletin est validé
     */
    public function estValide()
    {
        return $this->statut === 'validé';
    }

    /**
     * Vérifie si le bulletin est en brouillon
     */
    public function estBrouillon()
    {
        return $this->statut === 'brouillon';
    }

    /**
     * Vérifie si le bulletin est annulé
     */
    public function estAnnule()
    {
        return $this->statut === 'annulé';
    }

    /**
     * Obtenir le chemin du fichier PDF
     */
    public function getCheminPDF()
    {
        return $this->fichier_pdf ? storage_path('app/public/' . $this->fichier_pdf) : null;
    }

    /**
     * Obtenir l'URL du fichier PDF
     */
    public function getUrlPDF()
    {
        return $this->fichier_pdf ? asset('storage/' . $this->fichier_pdf) : null;
    }
}
