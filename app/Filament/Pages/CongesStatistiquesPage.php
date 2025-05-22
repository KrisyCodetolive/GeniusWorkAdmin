<?php

namespace App\Filament\Pages;

use App\Models\Conge;
use App\Models\TypeConge;
use App\Models\SoldeConge;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Actions\Action;

class CongesStatistiquesPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Gestion des Congés';

    protected static string $view = 'filament.pages.conges-statistiques-page';
    
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Statistiques des congés';
    
    protected static ?int $navigationSort = 4;
    
    protected static ?string $title = 'Statistiques des congés';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->data = [
            'date_debut' => Carbon::now()->startOfYear()->format('Y-m-d'),
            'date_fin' => Carbon::now()->format('Y-m-d'),
            'type_conge_id' => null,
        ];
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour à la liste')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => route('filament.admin.resources.conges.index')),
        ];
    }

    public function form(Form $form): Form
    {
        // Récupérer l'utilisateur connecté
        $user = auth()->user();
        
        // Filtrer les types de congé en fonction de l'entreprise de l'utilisateur
        $typeCongeQuery = TypeConge::query();
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $typeCongeQuery->where('entreprise_id', $user->entreprise_id);
        }
        
        return $form
            ->schema([
                Section::make('Filtres')
                    ->schema([
                        DatePicker::make('date_debut')
                            ->label('Date de début')
                            ->default(Carbon::now()->startOfYear())
                            ->required(),
                        DatePicker::make('date_fin')
                            ->label('Date de fin')
                            ->default(Carbon::now())
                            ->required(),
                        Select::make('type_conge_id')
                            ->label('Type de congé')
                            ->options($typeCongeQuery->pluck('nom', 'id'))
                            ->placeholder('Tous les types')
                            ->searchable(),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ])
            ->statePath('data');
    }
    
    public function getViewData(): array
    {
        // Récupérer l'utilisateur connecté
        $user = auth()->user();
        
        // Statistiques générales
        $query = Conge::query();
        
        // Appliquer les filtres d'entreprise pour les utilisateurs Admin
        // Les SuperAdmin et Support peuvent voir toutes les données
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            // Récupérer l'ID de l'entreprise de l'utilisateur
            $entrepriseId = $user->entreprise_id;
            
            // Filtrer les congés des employés de la même entreprise que l'utilisateur
            $query->whereHas('employeur', function ($q) use ($entrepriseId) {
                $q->where('entreprise_id', $entrepriseId);
            });
        }
        
        if (!empty($this->data['date_debut'])) {
            $query->where('date_debut', '>=', $this->data['date_debut']);
        }
        
        if (!empty($this->data['date_fin'])) {
            $query->where('date_debut', '<=', $this->data['date_fin']);
        }
        
        if (!empty($this->data['type_conge_id'])) {
            $query->where('type_conge_id', $this->data['type_conge_id']);
        }
        
        $totalConges = $query->count();
        $congesEnAttente = (clone $query)->where('statut', 'en_attente')->count();
        $congesApprouves = (clone $query)->where('statut', 'approuve')->count();
        $congesRejetes = (clone $query)->where('statut', 'rejete')->count();
        
        // Calcul du taux d'approbation
        $tauxApprobation = $totalConges > 0 
            ? round(($congesApprouves / $totalConges) * 100, 1) 
            : 0;
            
        // Moyenne des jours de congés pris par employé
        $moyenneJoursParEmploye = (clone $query)->where('statut', 'approuve')
            ->select('employeur_id', DB::raw('SUM(duree_jours) as total_jours'))
            ->groupBy('employeur_id')
            ->get()
            ->avg('total_jours') ?? 0;
            
        // Filtrer les types de congé en fonction de l'entreprise de l'utilisateur
        $typeCongeQuery = TypeConge::query();
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $typeCongeQuery->where('entreprise_id', $user->entreprise_id);
        }
        
        // Répartition par type de congé
        $congesByType = (clone $query)->select('type_conge_id', DB::raw('count(*) as total'))
            ->where('statut', 'approuve')
            ->groupBy('type_conge_id')
            ->get()
            ->map(function ($item) use ($typeCongeQuery) {
                $typeConge = $typeCongeQuery->find($item->type_conge_id);
                return [
                    'label' => $typeConge ? $typeConge->nom : 'Inconnu',
                    'value' => $item->total,
                ];
            });
            
        // Top employés par jours de congés
        $topEmployes = (clone $query)->where('statut', 'approuve')
            ->select('employeur_id', DB::raw('SUM(duree_jours) as total_jours'))
            ->with('employeur')
            ->groupBy('employeur_id')
            ->orderByDesc('total_jours')
            ->limit(10)
            ->get();
            
        // Tendance des congés sur les 12 derniers mois
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $congesTrend = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();
            
            $count = (clone $query)
                ->whereBetween('date_debut', [$monthStart, $monthEnd])
                ->count();
                
            $congesTrend[] = [
                'month' => $currentDate->format('M Y'),
                'count' => $count,
            ];
            
            $currentDate->addMonth();
        }
        
        // Dernières demandes de congés
        $dernieresDemandesConges = (clone $query)
            ->with(['employeur', 'typeConge', 'validateur'])
            ->latest()
            ->limit(5)
            ->get();
            
        return [
            'totalConges' => $totalConges,
            'congesEnAttente' => $congesEnAttente,
            'congesApprouves' => $congesApprouves,
            'congesRejetes' => $congesRejetes,
            'tauxApprobation' => $tauxApprobation,
            'moyenneJoursParEmploye' => $moyenneJoursParEmploye,
            'congesByType' => $congesByType,
            'topEmployes' => $topEmployes,
            'congesTrend' => $congesTrend,
            'dernieresDemandesConges' => $dernieresDemandesConges,
        ];
    }
}
