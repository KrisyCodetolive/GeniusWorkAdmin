<?php

namespace App\Filament\Widgets;

use App\Models\Employeur;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeurTendanceWidget extends ChartWidget
{
    protected static ?string $heading = 'Tendance des embauches';
    
    protected static ?int $sort = 3;
    
    protected int|string|array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $user = Auth::user();
        
        // Obtenir les données des 12 derniers mois
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        
        // Base query
        $query = Employeur::query()
            ->select(DB::raw('DATE_FORMAT(date_embauche, "%Y-%m") as mois'), DB::raw('count(*) as total'))
            ->where('date_embauche', '>=', $startDate)
            ->where('date_embauche', '<=', $endDate)
            ->groupBy('mois')
            ->orderBy('mois');
        
        // Filtrer par entreprise si l'utilisateur n'est pas SuperAdmin ou Support
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        $data = $query->get();
        
        // Créer un tableau pour tous les mois, même ceux sans embauches
        $months = [];
        $values = [];
        
        // Générer tous les mois entre la date de début et la date de fin
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            $months[] = $currentDate->format('M Y'); // Format pour l'affichage (ex: Jan 2025)
            
            // Trouver si nous avons des données pour ce mois
            $monthData = $data->firstWhere('mois', $monthKey);
            $values[] = $monthData ? $monthData->total : 0;
            
            $currentDate->addMonth();
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Nouvelles embauches',
                    'data' => $values,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $months,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
}
