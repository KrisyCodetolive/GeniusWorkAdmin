<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;

trait HasEntrepriseScope
{
    /**
     * Filtre une requête pour limiter les résultats à l'entreprise de l'utilisateur connecté
     * sauf pour les SuperAdmin et Support qui peuvent voir toutes les entreprises.
     *
     * @param Builder $query La requête à filtrer
     * @param string $enterpriseColumn Le nom de la colonne contenant l'ID de l'entreprise (par défaut: 'entreprise_id')
     * @return Builder La requête filtrée
     */
    public static function scopeForCurrentEntreprise(Builder $query, string $enterpriseColumn = 'entreprise_id'): Builder
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est ni SuperAdmin ni Support, filtrer par entreprise
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where($enterpriseColumn, $user->entreprise_id);
        }
        
        return $query;
    }
    
    /**
     * Filtre une relation dans un Select pour limiter les options à l'entreprise de l'utilisateur connecté
     * sauf pour les SuperAdmin et Support qui peuvent voir toutes les entreprises.
     *
     * @param string $relationship Le nom de la relation
     * @param string $titleColumn Le nom de la colonne à afficher dans le select
     * @param string $enterpriseColumn Le nom de la colonne contenant l'ID de l'entreprise (par défaut: 'entreprise_id')
     * @return Select Le composant Select configuré
     */
    public static function getEntrepriseFilteredSelect(string $relationship, string $titleColumn, string $enterpriseColumn = 'entreprise_id'): Select
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return Select::make($relationship)
            ->relationship($relationship, $titleColumn, function (Builder $query) use ($user, $isSuperAdminOrSupport, $enterpriseColumn) {
                // Si l'utilisateur n'est pas SuperAdmin ou Support, filtrer par entreprise
                if (!$isSuperAdminOrSupport) {
                    return $query->where($enterpriseColumn, $user->entreprise_id);
                }
                return $query;
            })
            ->searchable()
            ->preload();
    }
    
    /**
     * Modifie les données du formulaire avant la création pour définir automatiquement l'entreprise_id
     * pour les utilisateurs qui ne sont pas SuperAdmin ou Support.
     *
     * @param array $data Les données du formulaire
     * @param string $enterpriseColumn Le nom de la colonne contenant l'ID de l'entreprise (par défaut: 'entreprise_id')
     * @return array Les données modifiées
     */
    public static function mutateFormDataWithEntreprise(array $data, string $enterpriseColumn = 'entreprise_id'): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on utilise son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport() && !isset($data[$enterpriseColumn])) {
            $data[$enterpriseColumn] = $user->entreprise_id;
        }
        
        return $data;
    }
}
