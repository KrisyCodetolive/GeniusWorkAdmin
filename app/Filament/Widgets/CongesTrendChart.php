<?php

namespace App\Filament\Widgets;

use App\Models\Conge;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class CongesTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution des demandes de congés';
    
    protected static ?string $pollingInterval = '300s';
    
    protected int | string | array $columnSpan = 2;
    
    protected function getData(): array
    {
        $data = Trend::model(Conge::class)
            ->between(
                start: Carbon::now()->subMonths(12)->startOfMonth(),
                end: Carbon::now()->endOfMonth(),
            )
            ->perMonth()
            ->count();
            
        // Utiliser une requête filtrée pour les congés approuvés
        $approvedData = Trend::query(
                Conge::query()->where('statut', 'approuve')
            )
            ->between(
                start: Carbon::now()->subMonths(12)->startOfMonth(),
                end: Carbon::now()->endOfMonth(),
            )
            ->perMonth()
            ->count();
            
        // Utiliser une requête filtrée pour les congés rejetés
        $rejectedData = Trend::query(
                Conge::query()->where('statut', 'rejete')
            )
            ->between(
                start: Carbon::now()->subMonths(12)->startOfMonth(),
                end: Carbon::now()->endOfMonth(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Toutes les demandes',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'Demandes approuvées',
                    'data' => $approvedData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
                [
                    'label' => 'Demandes rejetées',
                    'data' => $rejectedData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->locale('fr')->monthName),
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
