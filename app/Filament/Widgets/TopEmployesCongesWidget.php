<?php

namespace App\Filament\Widgets;

use App\Models\Conge;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopEmployesCongesWidget extends ChartWidget
{
    protected static ?string $heading = 'Top 10 des employés par jours de congés';
    
    protected static ?string $pollingInterval = '300s';
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $topEmployes = Conge::where('statut', 'approuve')
            ->select('employeur_id', DB::raw('SUM(duree_jours) as total_jours'))
            ->with('employeur')
            ->groupBy('employeur_id')
            ->orderByDesc('total_jours')
            ->limit(10)
            ->get();
            
        return [
            'datasets' => [
                [
                    'label' => 'Jours de congés pris',
                    'data' => $topEmployes->pluck('total_jours')->toArray(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(55, 125, 240, 0.8)',
                        'rgba(51, 120, 235, 0.8)',
                        'rgba(47, 115, 230, 0.8)',
                        'rgba(43, 110, 225, 0.8)',
                        'rgba(39, 105, 220, 0.8)',
                        'rgba(35, 100, 215, 0.8)',
                        'rgba(31, 95, 210, 0.8)',
                        'rgba(27, 90, 205, 0.8)',
                        'rgba(23, 85, 200, 0.8)',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $topEmployes->pluck('employeur.name')->toArray(),
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 1,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Jours de congés',
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'autoSkip' => false,
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
            ],
            'indexAxis' => 'y',
        ];
    }
}
