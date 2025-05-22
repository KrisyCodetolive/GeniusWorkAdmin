<?php

namespace App\Filament\Resources\EntrepriseResource\Widgets;

use App\Models\Entreprise;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EntreprisesChart extends ChartWidget
{
    protected static ?string $heading = 'Création d\'entreprises';

    protected function getData(): array
    {
        $data = Entreprise::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $counts = [];

        // Initialiser les 12 derniers mois
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $labels[] = $date->format('M Y');
            $counts[$monthKey] = 0;
        }

        // Remplir avec les données réelles
        foreach ($data as $item) {
            $monthKey = "{$item->year}-" . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            if (isset($counts[$monthKey])) {
                $counts[$monthKey] = $item->count;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Entreprises créées',
                    'data' => array_values($counts),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
