<?php

namespace App\Filament\Widgets;

use App\Models\Conge;
use App\Models\TypeConge;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CongesTypesPieChart extends ChartWidget
{
    protected static ?string $heading = 'Répartition par type de congé';
    
    protected static ?string $pollingInterval = '300s';
    
    protected function getData(): array
    {
        $congesByType = Conge::select('type_conge_id', DB::raw('count(*) as total'))
            ->where('statut', 'approuve')
            ->groupBy('type_conge_id')
            ->get()
            ->map(function ($item) {
                $typeConge = TypeConge::find($item->type_conge_id);
                return [
                    'label' => $typeConge ? $typeConge->nom : 'Inconnu',
                    'value' => $item->total,
                ];
            });
            
        return [
            'datasets' => [
                [
                    'data' => $congesByType->pluck('value')->toArray(),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)', // Bleu
                        'rgb(16, 185, 129)', // Vert
                        'rgb(239, 68, 68)',  // Rouge
                        'rgb(245, 158, 11)', // Orange
                        'rgb(139, 92, 246)', // Violet
                        'rgb(236, 72, 153)', // Rose
                        'rgb(20, 184, 166)', // Turquoise
                        'rgb(249, 115, 22)', // Orange foncé
                    ],
                ],
            ],
            'labels' => $congesByType->pluck('label')->toArray(),
        ];
    }
    
    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'cutout' => '70%',
        ];
    }
}
