<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Entreprise;
use App\Models\Employeur;
use App\Models\Responsable;
use App\Models\Filiale;
use App\Exceptions\DepartementException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Traits\BelongsToEntreprise;

class Departement extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'filiale_id',
        'nom',
        'code',
        'description',
        'responsable_id',
        'parent_id',
        'niveau',
        'statut',
        'configuration'
    ];

    protected $casts = [
        'configuration' => 'json',
    ];

    // Constantes pour les statuts
    const STATUT_ACTIF = 'actif';
    const STATUT_INACTIF = 'inactif';

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function filiale()
    {
        return $this->belongsTo(Filiale::class);
    }

    public function departementParent()
    {
        return $this->belongsTo(Departement::class, 'parent_id');
    }

    public function sousDepartements()
    {
        return $this->hasMany(Departement::class, 'parent_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Employeur::class, 'responsable_id');
    }

    public function employeurs()
    {
        return $this->hasMany(Employeur::class, 'departement_id');
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }

    public function scopeInactif($query)
    {
        return $query->where('statut', self::STATUT_INACTIF);
    }

    public function scopeNiveau($query, $niveau)
    {
        return $query->where('niveau', $niveau);
    }

    public function scopeDepartementsRacine($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeParFiliale($query, $filialeId)
    {
        return $query->where('filiale_id', $filialeId);
    }

    // Fonctionnalités avancées
    
    /**
     * Génère un code unique pour le département si non spécifié
     */
    public static function genererCode($nom, $filialeId)
    {
        // Prendre les 3 premières lettres du nom en majuscules
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $nom), 0, 3));
        
        // Récupérer l'entreprise_id associée à la filiale
        $entrepriseId = Filiale::find($filialeId)->entreprise_id;
        
        // Trouver le dernier code utilisé avec ce préfixe dans cette filiale
        $dernierCode = self::where('filiale_id', $filialeId)
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
        
        // Vérifier si le code est unique dans l'entreprise
        $codeExiste = self::where('entreprise_id', $entrepriseId)
            ->where('code', $nouveauCode)
            ->exists();
            
        if ($codeExiste) {
            // Générer un nouveau code avec un suffixe supplémentaire
            $suffixe = substr(strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', Filiale::find($filialeId)->nom)), 0, 2);
            $nouveauCode = $prefix . $suffixe . str_pad($numero + 1, 3, '0', STR_PAD_LEFT);
        }
        
        return $nouveauCode;
    }
    
    /**
     * Calcule et met à jour le niveau hiérarchique du département
     */
    public function calculerEtMettreAJourNiveau()
    {
        $niveau = 1; // Niveau par défaut pour les départements racines
        
        if ($this->parent_id) {
            $parent = $this->departementParent;
            if ($parent) {
                $niveau = $parent->niveau + 1;
            }
        }
        
        $this->niveau = $niveau;
        $this->save();
        
        // Mettre à jour récursivement les niveaux des sous-départements
        foreach ($this->sousDepartements as $sousDept) {
            $sousDept->calculerEtMettreAJourNiveau();
        }
        
        return $niveau;
    }
    
    /**
     * Vérifie si le département peut être déplacé sous un nouveau parent
     */
    public function peutEtreDeplaceVers(Departement $nouveauParent): bool
    {
        // Vérifier que le nouveau parent n'est pas un descendant du département actuel
        if ($this->estAncetre($nouveauParent)) {
            return false;
        }
        
        // Vérifier que le nouveau parent est dans la même filiale
        if ($this->filiale_id !== $nouveauParent->filiale_id) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Vérifie si ce département est un ancêtre du département donné
     */
    public function estAncetre(Departement $autreDepartement): bool
    {
        if ($autreDepartement->parent_id === $this->id) {
            return true;
        }
        
        foreach ($this->sousDepartements as $sousDept) {
            if ($sousDept->estAncetre($autreDepartement)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Déplace le département sous un nouveau parent
     */
    public function deplacerVers(?Departement $nouveauParent = null): void
    {
        if ($nouveauParent && !$this->peutEtreDeplaceVers($nouveauParent)) {
            throw new DepartementException("Ce département ne peut pas être déplacé vers le parent spécifié.");
        }
        
        $this->parent_id = $nouveauParent ? $nouveauParent->id : null;
        $this->save();
        
        // Recalculer les niveaux
        $this->calculerEtMettreAJourNiveau();
    }
    
    /**
     * Transfère tous les employés vers un autre département
     */
    public function transfererEmployesVers(Departement $departementCible): int
    {
        $nombreEmployes = $this->employeurs()->count();
        
        if ($nombreEmployes > 0) {
            $this->employeurs()->update(['departement_id' => $departementCible->id]);
        }
        
        return $nombreEmployes;
    }
    
    /**
     * Obtient tous les départements descendants (récursivement)
     */
    public function getTousLesSousDepartements(): Collection
    {
        $sousDepartements = collect();
        
        foreach ($this->sousDepartements as $sousDept) {
            $sousDepartements->push($sousDept);
            $sousDepartements = $sousDepartements->merge($sousDept->getTousLesSousDepartements());
        }
        
        return $sousDepartements;
    }
    
    /**
     * Obtient le chemin complet du département (hiérarchie)
     */
    public function getCheminComplet($separator = ' > '): string
    {
        $chemin = [$this->nom];
        $parent = $this->departementParent;
        
        while ($parent) {
            array_unshift($chemin, $parent->nom);
            $parent = $parent->departementParent;
        }
        
        return implode($separator, $chemin);
    }
    
    /**
     * Vérifie si le département a atteint sa limite d'employés
     */
    public function aAtteintLimiteEmployes(): bool
    {
        if (isset($this->configuration['limite_employes'])) {
            return $this->employeurs()->count() >= $this->configuration['limite_employes'];
        }
        
        return false;
    }
    
    /**
     * Calcule le budget total consommé par le département et ses sous-départements
     */
    public function calculerBudgetConsomme(): float
    {
        // Logique fictive pour calculer le budget consommé
        // Dans une implémentation réelle, cela pourrait être lié à une table de dépenses
        $budgetConsomme = 0;
        
        // Ajouter la logique de calcul du budget consommé ici
        
        return $budgetConsomme;
    }
    
    /**
     * Obtient le budget disponible restant
     */
    public function getBudgetDisponible(): float
    {
        $budgetTotal = $this->configuration['budget'] ?? 0;
        $budgetConsomme = $this->calculerBudgetConsomme();
        
        return max(0, $budgetTotal - $budgetConsomme);
    }
    
    /**
     * Obtient les statistiques du département
     */
    public function getStatistiques(): array
    {
        return [
            'nombre_employes' => $this->employeurs()->count(),
            'nombre_employes_actifs' => $this->employeurs()->where('statut', 'actif')->count(),
            'nombre_sous_departements' => $this->sousDepartements()->count(),
            'budget_total' => $this->configuration['budget'] ?? 0,
            'budget_disponible' => $this->getBudgetDisponible(),
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
        
        $nombreEmployes = $this->employeurs()->count();
        return min(100, round(($nombreEmployes / $limite) * 100, 2));
    }
    
    /**
     * Obtient l'arborescence complète du département (pour affichage)
     */
    public function getArborescence(): array
    {
        $result = [
            'id' => $this->id,
            'nom' => $this->nom,
            'code' => $this->code,
            'niveau' => $this->niveau,
            'nombre_employes' => $this->employeurs()->count(),
            'sous_departements' => []
        ];
        
        foreach ($this->sousDepartements as $sousDept) {
            $result['sous_departements'][] = $sousDept->getArborescence();
        }
        
        return $result;
    }
    
    /**
     * Obtient les départements de même niveau (siblings)
     */
    public function getDepartementsMemeSibling(): Collection
    {
        return Departement::where('parent_id', $this->parent_id)
            ->where('id', '!=', $this->id)
            ->get();
    }
    
    /**
     * Fusionne ce département avec un autre département
     */
    public function fusionnerAvec(Departement $autreDepartement): void
    {
        // Vérifier que les départements sont dans la même filiale
        if ($this->filiale_id !== $autreDepartement->filiale_id) {
            throw new DepartementException("Impossible de fusionner des départements de filiales différentes.");
        }
        
        DB::transaction(function () use ($autreDepartement) {
            // Transférer les employés
            $autreDepartement->employeurs()->update(['departement_id' => $this->id]);
            
            // Réaffecter les sous-départements
            $autreDepartement->sousDepartements()->update(['parent_id' => $this->id]);
            
            // Supprimer l'autre département
            $autreDepartement->delete();
        });
        
        // Recalculer les niveaux
        $this->calculerEtMettreAJourNiveau();
    }
    
    /**
     * Vérifie si le département peut être supprimé en toute sécurité
     */
    public function peutEtreSupprime(): bool
    {
        // Vérifier s'il y a des employés dans ce département
        if ($this->employeurs()->exists()) {
            return false;
        }
        
        // Vérifier s'il y a des sous-départements
        if ($this->sousDepartements()->exists()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtient les départements similaires basés sur le nom ou d'autres critères
     */
    public function getDepartementsSimilaires($limit = 5): Collection
    {
        return Departement::where('filiale_id', $this->filiale_id)
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->where('nom', 'LIKE', '%' . substr($this->nom, 0, 5) . '%')
                      ->orWhere('niveau', $this->niveau);
            })
            ->limit($limit)
            ->get();
    }
}
