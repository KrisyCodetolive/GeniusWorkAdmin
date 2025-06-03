<?php

namespace App\Filament\Widgets;

use App\Models\Employeur;
use App\Models\Entreprise;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EmployeeLimitWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Si l'utilisateur est SuperAdmin ou Support, on n'affiche pas ce widget
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return [];
        }
        
        // Récupérer l'entreprise de l'utilisateur
        $entreprise = $user->entreprise;
        
        if (!$entreprise) {
            return [
                Stat::make('Limite d\'employés', 'Non disponible')
                    ->description('Aucune entreprise associée à votre compte')
                    ->descriptionIcon('heroicon-m-exclamation-circle')
                    ->color('danger'),
            ];
        }
        
        // Récupérer l'abonnement actif
        $abonnementActif = $entreprise->abonnementActif;
        
        if (!$abonnementActif) {
            return [
                Stat::make('Limite d\'employés', 'Non disponible')
                    ->description('Aucun abonnement actif')
                    ->descriptionIcon('heroicon-m-exclamation-circle')
                    ->color('danger'),
            ];
        }
        
        // Récupérer la limite d'employés depuis l'abonnement
        $limit = $entreprise->nombre_employes ?? $abonnementActif->nombre_personnels ?? 
                $abonnementActif->planAbonnement->nombre_employes_max ?? 0;
        
        if ($limit <= 0) {
            return [
                Stat::make('Limite d\'employés', 'Non disponible')
                    ->description('Limite non définie dans votre abonnement')
                    ->descriptionIcon('heroicon-m-exclamation-circle')
                    ->color('danger'),
            ];
        }
        
        // Récupérer le nombre actuel d'employés
        $currentEmployeeCount = $entreprise->getEmployeCount();
        
        // Calculer le pourcentage d'utilisation
        $utilisationPercent = round(($currentEmployeeCount / $limit) * 100);
        
        // Déterminer la couleur en fonction du pourcentage d'utilisation
        $color = 'success';
        if ($utilisationPercent >= 90) {
            $color = 'danger';
        } elseif ($utilisationPercent >= 75) {
            $color = 'warning';
        }
        
        // Créer le message de description
        $description = "Vous utilisez {$utilisationPercent}% de votre limite d'employés";
        
        // Ajouter un avertissement si proche de la limite
        if ($utilisationPercent >= 90) {
            $description .= " - Limite presque atteinte!";
        } elseif ($utilisationPercent >= 75) {
            $description .= " - Considérez une mise à niveau";
        }
        
        return [
            Stat::make('Employés utilisés', "{$currentEmployeeCount} / {$limit}")
                ->description($description)
                ->descriptionIcon('heroicon-m-user-group')
                ->color($color)
                ->chart([
                    $currentEmployeeCount,
                    $limit - $currentEmployeeCount,
                ]),
        ];
    }
}
