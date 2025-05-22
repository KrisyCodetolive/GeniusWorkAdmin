<?php

namespace App\Filament\Resources\VisiteResource\Pages;

use App\Filament\Resources\VisiteResource;
use Filament\Resources\Pages\Page;
use App\Models\Visite;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;

class VisiteStats extends Page
{
    protected static string $resource = VisiteResource::class;

    protected static string $view = 'filament.resources.visite-resource.pages.visite-stats';
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'Statistiques';
    
    protected static ?int $navigationSort = 3;
    
    public $dateDebut;
    public $dateFin;
    public $siteId;
    public $stats = [];
    public $chartData = [];
    public $showChart = false;
    
    public function mount(): void
    {
        // Par défaut, afficher les statistiques du mois en cours
        $this->dateDebut = now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = now()->format('Y-m-d');
        
        // Charger les statistiques initiales
        $this->loadStats();
    }
    
    public function form(Form $form): Form
    {
        $user = Auth::user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\DatePicker::make('dateDebut')
                            ->label('Date de début')
                            ->required()
                            ->default(now()->startOfMonth())
                            ->maxDate(now()),
                            
                        Forms\Components\DatePicker::make('dateFin')
                            ->label('Date de fin')
                            ->required()
                            ->default(now())
                            ->minDate(fn (Forms\Get $get) => $get('dateDebut'))
                            ->maxDate(now()),
                            
                        Forms\Components\Select::make('siteId')
                            ->label('Site')
                            ->options(function () use ($isSuperAdminOrSupport, $user) {
                                $query = Site::query();
                                
                                if (!$isSuperAdminOrSupport) {
                                    $query->where('entreprise_id', $user->entreprise_id);
                                }
                                
                                $options = $query->pluck('nom', 'id')->toArray();
                                return ['' => 'Tous les sites'] + $options;
                            })
                            ->placeholder('Tous les sites')
                            ->searchable(),
                    ]),
            ])
            ->statePath('data');
    }
    
    public function loadStats(): void
    {
        $user = Auth::user();
        $query = Visite::query()
            ->whereBetween('visites.date_arrivee', [$this->dateDebut, $this->dateFin . ' 23:59:59']);
        
        // Filtrer par entreprise si l'utilisateur n'est pas super admin ou support
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('visites.entreprise_id', $user->entreprise_id);
        }
        
        // Filtrer par site si spécifié
        if ($this->siteId) {
            $query->where('visites.site_id', $this->siteId);
        }
        
        // Statistiques générales
        $this->stats['total'] = $query->count();
        $this->stats['en_cours'] = (clone $query)->where('visites.statut', 'en_cours')->count();
        $this->stats['terminees'] = (clone $query)->where('visites.statut', 'terminee')->count();
        $this->stats['annulees'] = (clone $query)->where('visites.statut', 'annulee')->count();
        
        // Statistiques par site
        $this->stats['par_site'] = (clone $query)
            ->select('sites.nom as site_nom', DB::raw('count(*) as total'))
            ->join('sites', 'visites.site_id', '=', 'sites.id')
            ->where(function ($q) use ($user) {
                // Préciser explicitement la table pour éviter l'ambiguïté
                if (!$user->isSuperAdmin() && !$user->isSupport()) {
                    $q->where('visites.entreprise_id', $user->entreprise_id);
                }
            })
            ->groupBy('sites.id', 'sites.nom')
            ->orderByDesc('total')
            ->get()
            ->toArray();
        
        // Statistiques par jour
        $visitesByDay = (clone $query)
            ->select(DB::raw('DATE(visites.date_arrivee) as date'), DB::raw('count(*) as total'))
            ->where(function ($q) use ($user) {
                // Préciser explicitement la table pour éviter l'ambiguïté
                if (!$user->isSuperAdmin() && !$user->isSupport()) {
                    $q->where('visites.entreprise_id', $user->entreprise_id);
                }
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Préparer les données pour le graphique
        $labels = [];
        $data = [];
        
        foreach ($visitesByDay as $day) {
            $labels[] = Carbon::parse($day->date)->format('d/m/Y');
            $data[] = $day->total;
        }
        
        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Nombre de visites',
                    'data' => $data,
                    'backgroundColor' => '#4f46e5',
                    'borderColor' => '#4f46e5',
                    'borderWidth' => 1
                ]
            ]
        ];
        
        $this->showChart = count($labels) > 0;
    }
    
    public function submitForm(array $data): void
    {
        $this->dateDebut = $data['dateDebut'];
        $this->dateFin = $data['dateFin'];
        $this->siteId = $data['siteId'] ?? null;
        
        $this->loadStats();
        
        Notification::make()
            ->title('Statistiques mises à jour')
            ->success()
            ->send();
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('genererRapport')
                ->label('Générer un rapport')
                ->icon('heroicon-o-document-text')
                ->iconPosition(IconPosition::After)
                ->color('success')
                ->action(function () {
                    return redirect()->route('visites.rapport', [
                        'date_debut' => $this->dateDebut,
                        'date_fin' => $this->dateFin,
                        'site_id' => $this->siteId
                    ]);
                }),
        ];
    }
}
