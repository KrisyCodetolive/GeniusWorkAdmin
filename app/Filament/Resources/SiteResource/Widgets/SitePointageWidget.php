<?php

namespace App\Filament\Resources\SiteResource\Widgets;

use App\Models\Presence;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SitePointageWidget extends ChartWidget
{
    protected static ?string $heading = 'Activité de pointage par site';
    
    protected static ?int $sort = 3;
    
    protected int|string|array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $user = Auth::user();
        
        // Obtenir les données des 7 derniers jours
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        
        // Base query
        $query = Presence::query()
            ->join('sites', 'presences.site_id', '=', 'sites.id')
            ->select('sites.nom as site', DB::raw('DATE(presences.created_at) as date'), DB::raw('count(*) as total'))
            ->where('presences.created_at', '>=', $startDate)
            ->where('presences.created_at', '<=', $endDate)
            ->whereNotNull('presences.site_id')
            ->groupBy('sites.nom', 'date')
            ->orderBy('date');
        
        // Filtrer par entreprise si l'utilisateur n'est pas SuperAdmin ou Support
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('presences.entreprise_id', $user->entreprise_id);
        }
        
        $data = $query->get();
        
        // Regrouper les données par site
        $sites = $data->pluck('site')->unique()->values()->toArray();
        $dates = [];
        
        // Générer toutes les dates entre la date de début et la date de fin
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('d/m');
            $currentDate->addDay();
        }
        
        // Préparer les datasets pour chaque site
        $datasets = [];
        $colors = $this->generateRandomColors(count($sites));
        
        foreach ($sites as $index => $site) {
            $siteData = [];
            
            foreach ($dates as $i => $date) {
                $dateKey = Carbon::createFromFormat('d/m', $date)->format('Y-m-d');
                $pointage = $data->where('site', $site)->where('date', $dateKey)->first();
                $siteData[] = $pointage ? $pointage->total : 0;
            }
            
            $datasets[] = [
                'label' => $site,
                'data' => $siteData,
                'borderColor' => str_replace('0.7', '1', $colors[$index]),
                'backgroundColor' => $colors[$index],
            ];
        }
        
        return [
            'datasets' => $datasets,
            'labels' => $dates,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    /**
     * Génère un tableau de couleurs aléatoires
     */
    private function generateRandomColors(int $count): array
    {
        $colors = [];
        
        // Quelques couleurs prédéfinies
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
