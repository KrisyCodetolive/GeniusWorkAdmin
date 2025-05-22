<?php

namespace App\Filament\Actions;

use App\Models\Site;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class GenerateHeuresTravailReportAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'generateHeuresTravailReport';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Rapport d\'heures')
            ->icon('heroicon-o-clock')
            ->color('warning')
            ->form([
                Section::make('Paramètres du rapport d\'heures de travail')
                    ->description('Configurez les options du rapport d\'heures de travail')
                    ->schema([
                        Radio::make('type_rapport')
                            ->label('Type de rapport')
                            ->options([
                                'journalier' => 'Journalier',
                                'hebdomadaire' => 'Hebdomadaire',
                                'mensuel' => 'Mensuel',
                                'personnalise' => 'Période personnalisée',
                            ])
                            ->default('journalier')
                            ->live()
                            ->required(),
                        
                        DatePicker::make('date_journalier')
                            ->label('Date')
                            ->default(now()->startOfDay())
                            ->required()
                            ->visible(fn (Get $get) => $get('type_rapport') === 'journalier'),
                            
                        DatePicker::make('date_hebdomadaire')
                            ->label('Semaine')
                            ->default(now()->startOfDay())
                            ->required()
                            ->visible(fn (Get $get) => $get('type_rapport') === 'hebdomadaire'),
                            
                        Select::make('date_mensuel')
                            ->label('Mois')
                            ->options(function() {
                                $options = [];
                                $currentMonth = Carbon::now()->startOfMonth();
                                
                                // Générer les options pour les 12 derniers mois et les 3 prochains mois
                                for ($i = -12; $i <= 3; $i++) {
                                    $date = $currentMonth->copy()->addMonths($i);
                                    $key = $date->format('Y-m');
                                    $label = $date->translatedFormat('F Y'); // Nom du mois traduit + année
                                    $options[$key] = $label;
                                }
                                
                                return $options;
                            })
                            ->default(now()->format('Y-m'))
                            ->required()
                            ->visible(fn (Get $get) => $get('type_rapport') === 'mensuel'),
                        
                        DatePicker::make('date_debut')
                            ->label('Date de début')
                            ->default(now()->startOfDay())
                            ->required()
                            ->live()
                            ->visible(fn (Get $get) => $get('type_rapport') === 'personnalise'),
                        
                        DatePicker::make('date_fin')
                            ->label('Date de fin')
                            ->default(now()->startOfDay())
                            ->required()
                            ->minDate(fn (Get $get) => $get('date_debut'))
                            ->visible(fn (Get $get) => $get('type_rapport') === 'personnalise'),
                        
                        Select::make('site_id')
                            ->label('Site')
                            ->options(function () {
                                $user = Auth::user();
                                $query = Site::query();
                                
                                if (!$user->isSuperAdmin() && !$user->isSupport()) {
                                    $query->where('entreprise_id', $user->entreprise_id);
                                }
                                
                                return $query->pluck('nom', 'id')->prepend('Tous les sites', '');
                            })
                            ->searchable()
                            ->preload(),
                    ]),
            ])
            ->action(function (array $data) {
                // Préparer les paramètres pour la redirection
                $params = [
                    'type_rapport' => $data['type_rapport'],
                ];
                
                // Déterminer les dates en fonction du type de rapport
                switch ($data['type_rapport']) {
                    case 'journalier':
                        $params['date_debut'] = $data['date_journalier'];
                        $params['date_fin'] = $data['date_journalier'];
                        break;
                    case 'hebdomadaire':
                        $dateDebut = Carbon::parse($data['date_hebdomadaire'])->startOfWeek()->format('Y-m-d');
                        $dateFin = Carbon::parse($data['date_hebdomadaire'])->endOfWeek()->format('Y-m-d');
                        $params['date_debut'] = $dateDebut;
                        $params['date_fin'] = $dateFin;
                        break;
                    case 'mensuel':
                        // Format attendu pour date_mensuel est 'Y-m' (ex: 2025-05)
                        $dateDebut = Carbon::parse($data['date_mensuel'] . '-01')->startOfMonth()->format('Y-m-d');
                        $dateFin = Carbon::parse($data['date_mensuel'] . '-01')->endOfMonth()->format('Y-m-d');
                        $params['date_debut'] = $dateDebut;
                        $params['date_fin'] = $dateFin;
                        break;
                    case 'personnalise':
                        $params['date_debut'] = $data['date_debut'];
                        $params['date_fin'] = $data['date_fin'];
                        break;
                }
                
                // Ajouter le site_id s'il est spécifié
                if (!empty($data['site_id'])) {
                    $params['site_id'] = $data['site_id'];
                }
                
                // Rediriger vers la route de génération de rapport
                return Redirect::route('rapports.heures-travail.generer', $params);
            });
    }
}
