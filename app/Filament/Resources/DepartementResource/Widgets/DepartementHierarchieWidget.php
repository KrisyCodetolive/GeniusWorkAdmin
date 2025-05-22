<?php

namespace App\Filament\Resources\DepartementResource\Widgets;

use App\Models\Departement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartementHierarchieWidget extends ChartWidget
{
    protected static ?string $heading = 'Répartition par niveau hiérarchique';
    
    protected static ?int $sort = 2;
    
    protected int|string|array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $user = Auth::user();
        
        // Base query
        $query = Departement::query()
            ->select('niveau', DB::raw('count(*) as total'))
            ->groupBy('niveau')
            ->orderBy('niveau');
        
        // Filtrer par entreprise si l'utilisateur n'est pas SuperAdmin ou Support
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        $data = $query->get();
        
        // Préparer les données pour le graphique
        $labels = $data->pluck('niveau')->map(function ($niveau) {
            return 'Niveau ' . $niveau;
        })->toArray();
        
        $values = $data->pluck('total')->toArray();
        
        return [
            'datasets' => [
                [
                    'label' => 'Nombre de départements',
                    'data' => $values,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 205, 86, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(54, 162, 235)',
                        'rgb(75, 192, 192)',
                        'rgb(255, 159, 64)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 99, 132)',
                        'rgb(255, 205, 86)',
                    ],
                    'borderWidth' => 1
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
}
