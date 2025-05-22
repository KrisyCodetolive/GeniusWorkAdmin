<?php

namespace App\Filament\Resources\SiteResource\Widgets;

use App\Models\Site;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiteGeographieWidget extends ChartWidget
{
    protected static ?string $heading = 'Répartition géographique des sites';
    
    protected static ?int $sort = 2;
    
    protected int|string|array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $user = Auth::user();
        
        // Base query
        $query = Site::query()
            ->select('pays', DB::raw('count(*) as total'))
            ->groupBy('pays')
            ->orderBy('total', 'desc');
        
        // Filtrer par entreprise si l'utilisateur n'est pas SuperAdmin ou Support
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        $data = $query->get();
        
        // Préparer les données pour le graphique
        $labels = $data->pluck('pays')->toArray();
        $values = $data->pluck('total')->toArray();
        
        // Générer des couleurs aléatoires pour chaque pays
        $colors = $this->generateRandomColors(count($labels));
        
        return [
            'datasets' => [
                [
                    'label' => 'Nombre de sites',
                    'data' => $values,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'pie';
    }
    
    /**
     * Génère un tableau de couleurs aléatoires
     */
    private function generateRandomColors(int $count): array
    {
        $colors = [];
        
        // Quelques couleurs prédéfinies pour les premiers pays
        $predefinedColors = [
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 205, 86, 0.7)',
            'rgba(201, 203, 207, 0.7)',
        ];
        
        // Utiliser les couleurs prédéfinies d'abord
        for ($i = 0; $i < $count; $i++) {
            if (isset($predefinedColors[$i])) {
                $colors[] = $predefinedColors[$i];
            } else {
                // Générer une couleur aléatoire si nous n'avons plus de couleurs prédéfinies
                $r = rand(0, 255);
                $g = rand(0, 255);
                $b = rand(0, 255);
                $colors[] = "rgba($r, $g, $b, 0.7)";
            }
        }
        
        return $colors;
    }
}
