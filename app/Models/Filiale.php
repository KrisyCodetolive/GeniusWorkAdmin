<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\FilialeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Traits\BelongsToEntreprise;

class Filiale extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'code',
        'adresse',
        'ville',
        'pays',
        'telephone',
        'email',
        'site_web',
        'logo',
        'description',
        'configuration',
        'statut',
        'site_id'
    ];

    protected $casts = [
        'configuration' => 'json',
    ];

    // Constantes pour les statuts
    const STATUT_ACTIF = 'actif';
    const STATUT_INACTIF = 'inactif';

    // Constantes pour les rôles des responsables
    const ROLE_RESPONSABLE_PRINCIPAL = 'responsable_principal';
    const ROLE_RESPONSABLE_ADJOINT = 'responsable_adjoint';

    /**
     * Get the entreprise that owns the filiale.
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Get the responsables for the filiale.
     */
    public function responsables()
    {
        return $this->belongsToMany(Employeur::class, 'filiale_responsables')
                    ->withPivot('role', 'date_debut', 'date_fin')
                    ->withTimestamps();
    }

    /**
     * Get the departments associated with the filiale.
     */
    public function departements()
    {
        return $this->hasMany(Departement::class);
    }

    /**
     * Get the employeurs associated with the filiale through departements.
     */
    public function employeursThroughDepartements()
    {
        return $this->hasManyThrough(
            Employeur::class,
            Departement::class,
            'filiale_id', // Foreign key on departements table
            'departement_id', // Foreign key on employeurs table
            'id', // Local key on filiales table
            'id' // Local key on departements table
        );
    }

    /**
     * Relation avec les employeurs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employeurs()
    {
        return $this->hasMany(Employeur::class);
    }

    /**
     * Get the site associated with the filiale.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Scope a query to only include active filiales.
     */
    public function scopeActif($query): Builder
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }

    /**
     * Scope a query to only include filiales from a specific country.
     */
    public function scopeParPays($query, string $pays): Builder
    {
        return $query->where('pays', $pays);
    }

    /**
     * Scope a query to only include filiales from a specific city.
     */
    public function scopeParVille($query, string $ville): Builder
    {
        return $query->where('ville', $ville);
    }

    /**
     * Get the current responsable principal of the filiale.
     */
    public function getResponsablePrincipalActuel()
    {
        return $this->responsables()
            ->wherePivot('role', self::ROLE_RESPONSABLE_PRINCIPAL)
            ->wherePivot('date_fin', null)
            ->first();
    }

    /**
     * Get all active responsables of the filiale.
     */
    public function getResponsablesActifs()
    {
        return $this->responsables()
            ->wherePivot('date_fin', null)
            ->get();
    }

    /**
     * Assign a new responsable to the filiale.
     */
    public function assignerResponsable(Employeur $employeur, string $role, ?Carbon $dateDebut = null): void
    {
        // Vérifier si l'employeur n'est pas déjà responsable actif
        $responsableExistant = $this->responsables()
            ->where('employeur_id', $employeur->id)
            ->wherePivot('date_fin', null)
            ->first();

        if ($responsableExistant) {
            throw new FilialeException("Cet employeur est déjà responsable de cette filiale.");
        }

        // Si c'est un responsable principal, terminer le mandat du responsable principal actuel
        if ($role === self::ROLE_RESPONSABLE_PRINCIPAL) {
            $this->terminerMandatResponsablePrincipal();
        }

        // Assigner le nouveau responsable
        $this->responsables()->attach($employeur->id, [
            'role' => $role,
            'date_debut' => $dateDebut ?? now(),
        ]);
    }

    /**
     * Terminate the mandate of the current principal responsable.
     */
    protected function terminerMandatResponsablePrincipal(): void
    {
        $responsablePrincipal = $this->getResponsablePrincipalActuel();
        if ($responsablePrincipal) {
            $this->responsables()->updateExistingPivot($responsablePrincipal->id, [
                'date_fin' => now()
            ]);
        }
    }

    /**
     * Terminate the mandate of a responsable.
     */
    public function terminerMandat(Employeur $employeur, ?Carbon $dateFin = null): void
    {
        $this->responsables()->updateExistingPivot($employeur->id, [
            'date_fin' => $dateFin ?? now()
        ]);
    }

    /**
     * Get the number of active employees in the filiale through departements.
     */
    public function getNombreEmployesActifs(): int
    {
        // Utiliser la relation hasManyThrough pour obtenir les employés actifs
        return $this->employeursThroughDepartements()
            ->where('employeurs.statut', 'actif')
            ->count();
    }

    /**
     * Génère un code unique pour la filiale
     * 
     * @param string $nom Nom de la filiale
     * @param int $entrepriseId ID de l'entreprise
     * @return string Code unique généré
     */
    public static function genererCode($nom, $entrepriseId)
    {
        // Prendre les 3 premières lettres du nom en majuscules
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $nom), 0, 3));
        
        // Trouver le dernier code utilisé avec ce préfixe dans cette entreprise
        $dernierCode = self::where('entreprise_id', $entrepriseId)
            ->where('code', 'LIKE', $prefix . '%')
            ->orderByRaw('LENGTH(code) DESC, code DESC')
            ->value('code');
        
        if ($dernierCode) {
            // Extraire le numéro et l'incrémenter
            $numero = (int) substr($dernierCode, strlen($prefix));
            $nouveauCode = $prefix . str_pad($numero + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nouveauCode = $prefix . '001';
        }
        
        return $nouveauCode;
    }

    /**
     * Check if the filiale has reached its employee limit based on configuration.
     */
    public function aAtteintLimiteEmployes(): bool
    {
        $config = $this->configuration;
        if (isset($config['limite_employes'])) {
            return $this->getNombreEmployesActifs() >= $config['limite_employes'];
        }
        return false;
    }

    /**
     * Get the history of responsables for this filiale.
     */
    public function getHistoriqueResponsables()
    {
        return $this->responsables()
            ->withPivot('role', 'date_debut', 'date_fin')
            ->orderBy('filiale_responsables.date_debut', 'desc')
            ->get();
    }

    /**
     * Check if the filiale is the headquarters.
     */
    public function estSiegeSocial(): bool
    {
        return isset($this->configuration['type']) && 
               $this->configuration['type'] === 'siege_social';
    }

    /**
     * Get all departments with their active employees count.
     */
    public function getDepartementsAvecEmployes()
    {
        return $this->departements()
            ->withCount(['employeurs' => function ($query) {
                $query->where('statut', 'actif');
            }])
            ->get();
    }

    /**
     * Update the filiale status.
     */
    public function updateStatut(string $statut): void
    {
        if (!in_array($statut, [self::STATUT_ACTIF, self::STATUT_INACTIF])) {
            throw new FilialeException("Statut invalide.");
        }

        $this->update(['statut' => $statut]);
    }

    /**
     * Get the complete address of the filiale.
     */
    public function getAdresseComplete(): string
    {
        $adresse = [];
        if ($this->adresse) $adresse[] = $this->adresse;
        if ($this->ville) $adresse[] = $this->ville;
        if ($this->pays) $adresse[] = $this->pays;

        return implode(', ', $adresse);
    }
    
    // Nouvelles fonctionnalités
    
    /**
     * Obtient les statistiques de la filiale
     */
    public function getStatistiques(): array
    {
        $employesActifs = $this->getNombreEmployesActifs();
        $totalEmployes = $this->employeursThroughDepartements()->count();
        
        return [
            'nombre_employes_actifs' => $employesActifs,
            'nombre_employes_total' => $totalEmployes,
            'nombre_departements' => $this->departements()->count(),
            'taux_activite' => $totalEmployes > 0 ? round(($employesActifs / $totalEmployes) * 100, 2) : 0,
            'limite_employes' => $this->configuration['limite_employes'] ?? 'Non définie',
            'taux_occupation' => $this->calculerTauxOccupation(),
        ];
    }
    
    /**
     * Calcule le taux d'occupation (employés / limite)
     */
    private function calculerTauxOccupation(): float
    {
        $limite = $this->configuration['limite_employes'] ?? 0;
        
        if ($limite <= 0) {
            return 0;
        }
        
        $nombreEmployes = $this->employeursThroughDepartements()->count();
        return min(100, round(($nombreEmployes / $limite) * 100, 2));
    }
    
    /**
     * Obtient la structure hiérarchique des départements de la filiale
     */
    public function getStructureDepartements(): array
    {
        $departements = $this->departements()
            ->whereNull('departement_parent_id')
            ->with('sousDepartements')
            ->get();
            
        $structure = [];
        
        foreach ($departements as $departement) {
            $structure[] = $departement->getArborescence();
        }
        
        return $structure;
    }
    
    /**
     * Vérifie si la filiale peut être fusionnée avec une autre
     */
    public function peutEtreFusionneeAvec(Filiale $autreFiliale): bool
    {
        // Vérifier que les filiales sont dans la même entreprise
        if ($this->entreprise_id !== $autreFiliale->entreprise_id) {
            return false;
        }
        
        // Autres vérifications possibles
        
        return true;
    }
    
    /**
     * Fusionne cette filiale avec une autre filiale
     */
    public function fusionnerAvec(Filiale $autreFiliale): void
    {
        if (!$this->peutEtreFusionneeAvec($autreFiliale)) {
            throw new FilialeException("Ces filiales ne peuvent pas être fusionnées.");
        }
        
        DB::transaction(function () use ($autreFiliale) {
            // Récupérer les départements de l'autre filiale
            $departements = $autreFiliale->departements;
            
            // Transférer les départements
            $autreFiliale->departements()->update(['filiale_id' => $this->id]);
            
            // Supprimer l'autre filiale
            $autreFiliale->delete();
        });
    }
    
    /**
     * Vérifie si la filiale peut être supprimée en toute sécurité
     */
    public function peutEtreSupprimee(): bool
    {
        // Vérifier si c'est le siège social
        if ($this->estSiegeSocial()) {
            return false;
        }
        
        // Vérifier s'il y a des départements
        if ($this->departements()->exists()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtient les filiales similaires basées sur divers critères
     */
    public function getFilialesSimilaires($limit = 5): Collection
    {
        return Filiale::where('entreprise_id', $this->entreprise_id)
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->where('pays', $this->pays)
                      ->orWhere('ville', $this->ville);
            })
            ->limit($limit)
            ->get();
    }
}
