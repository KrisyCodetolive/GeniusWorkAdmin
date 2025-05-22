<?php

namespace App\Filament\Resources\SecurityKeyResource\Widgets;

use App\Models\SecurityKey;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Crypt;

class SecurityKeyOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalKeys = SecurityKey::count();
        $activeKeys = SecurityKey::where('is_active', true)->count();
        $expiredKeys = SecurityKey::where('expires_at', '<', now())->count();
        
        // Récupérer la clé active actuelle
        $activeKey = SecurityKey::where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
            
        $keyStatus = 'Aucune clé active';
        $keyExpiry = null;
        $keyColor = 'danger';
        
        if ($activeKey) {
            $keyStatus = 'Clé active disponible';
            $keyExpiry = $activeKey->expires_at;
            
            // Déterminer la couleur en fonction de la date d'expiration
            $daysUntilExpiry = now()->diffInDays($keyExpiry, false);
            
            if ($daysUntilExpiry > 3) {
                $keyColor = 'success';
            } elseif ($daysUntilExpiry > 1) {
                $keyColor = 'warning';
            } else {
                $keyColor = 'danger';
            }
        }

        return [
            Stat::make('Clés totales', $totalKeys)
                ->description('Nombre total de clés générées')
                ->descriptionIcon('heroicon-m-key')
                ->color('primary'),
                
            Stat::make('Clés actives', $activeKeys)
                ->description('Nombre de clés marquées comme actives')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($activeKeys > 0 ? 'success' : 'danger'),
                
            Stat::make('Statut de la clé', $keyStatus)
                ->description($keyExpiry ? 'Expire le ' . $keyExpiry->format('d/m/Y H:i') : 'Aucune clé active disponible')
                ->descriptionIcon($keyExpiry ? 'heroicon-m-clock' : 'heroicon-m-x-circle')
                ->color($keyColor)
                ->chart($activeKey ? $this->generateExpiryChart($activeKey->expires_at) : [0, 0, 0, 0, 0, 0, 0]),
        ];
    }
    
    /**
     * Génère un graphique simple pour visualiser l'expiration de la clé
     */
    protected function generateExpiryChart($expiryDate)
    {
        $now = now();
        $totalDays = max(1, $now->copy()->startOfDay()->diffInDays($expiryDate->copy()->endOfDay()));
        $remainingDays = max(0, $now->diffInDays($expiryDate, false));
        
        // Limiter à 7 jours maximum pour le graphique
        $days = min(7, $totalDays);
        
        $chart = [];
        
        // Remplir le graphique avec des valeurs représentant le temps écoulé/restant
        for ($i = 0; $i < $days; $i++) {
            if ($i < ($days - $remainingDays)) {
                // Jours écoulés
                $chart[] = 100;
            } else {
                // Jours restants
                $chart[] = 0;
            }
        }
        
        // Assurer qu'il y a au moins 7 points de données
        while (count($chart) < 7) {
            $chart[] = 0;
        }
        
        return $chart;
    }
}
