<?php

namespace App\Filament\Resources\Paie\BulletinPaieResource\Pages;

use App\Filament\Resources\Paie\BulletinPaieResource;
use App\Interfaces\Paie\CalculPaieServiceInterface;
use App\Models\Employeur;
use App\Models\Paie\ConfigurationPaie;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CreateBulletinPaie extends CreateRecord
{
    use HasWizard;

    protected static string $resource = BulletinPaieResource::class;
    
    // Mode de génération (individuel ou masse)
    public string $generationMode = 'individuel';
    
    // Dates pour les périodes de paie
    public string $debutMois;
    public string $finMois;
    public string $datePaiement;
    
    public function mount(): void
    {
        // Initialiser les dates avant l'appel à parent::mount()
        $this->debutMois = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->finMois = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->datePaiement = Carbon::now()->format('Y-m-d');
        
        parent::mount();
        
        // Remplir le formulaire avec les valeurs initiales
        // Utiliser fill() avec des valeurs par défaut pour éviter les erreurs
        $this->form->fill([
            'generation_mode' => $this->generationMode,
        ]);
    }
    
    /**
     * Méthode appelée après que le formulaire a été créé
     * Utilisée pour définir les valeurs par défaut des champs
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // S'assurer que les dates sont définies dans le formulaire
        $data['periode_debut'] = $data['periode_debut'] ?? $this->debutMois;
        $data['periode_fin'] = $data['periode_fin'] ?? $this->finMois;
        $data['date_paiement'] = $data['date_paiement'] ?? $this->datePaiement;
        
        return $data;
    }
    
    /**
     * Méthode pour mettre à jour le mode de génération
     */
    public function setGenerationMode(string $value): void
    {
        $this->form->fill(['generation_mode' => $value]);
        
        // Émettre un événement pour notifier le changement de mode
        $this->dispatch('generation-mode-changed', $value);
    }
    

    protected function handleRecordCreation(array $data): Model
    {
        // S'assurer que les dates sont définies
        $data['periode_debut'] = $data['periode_debut'] ?? $this->debutMois;
        $data['periode_fin'] = $data['periode_fin'] ?? $this->finMois;
        $data['date_paiement'] = $data['date_paiement'] ?? $this->datePaiement;
        
        // Si on est en mode masse, on ne devrait pas arriver ici
        if (($data['generation_mode'] ?? 'individuel') === 'masse') {
            $this->generateMasse();
            return new Employeur(); // Retourne un modèle vide (ne sera pas utilisé)
        }
        
        // Récupérer l'employeur et la configuration de paie
        $employeur = Employeur::findOrFail($data['employeur_id']);
        $configuration = ConfigurationPaie::findOrFail($data['configuration_paie_id']);
        
        // Préparer les paramètres pour le service de calcul de paie
        $parametres = $this->_prepareBulletinParameters($employeur, $data);
        
        // Générer le bulletin de paie via le service
        $calculPaieService = app(CalculPaieServiceInterface::class);
        $bulletin = $calculPaieService->genererBulletinPaie($employeur, $configuration, $parametres);
        
        // Valider directement le bulletin si demandé
        if ($data['valider_directement'] ?? false) {
            $this->_validerBulletin($bulletin);
            
            Notification::make()
                ->title('Bulletin généré et validé')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Bulletin généré en brouillon')
                ->success()
                ->send();
        }
        
        return $bulletin;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getSteps(): array
    {       
        
        // Étape de choix du mode de génération (toujours la première)
        $steps = [
            Step::make('Mode de génération')
                ->description('Choisissez le mode de génération des bulletins')
                ->icon('heroicon-o-document')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Section::make('Bulletin individuel')
                                ->icon('heroicon-o-user')
                                ->description('Générer un bulletin pour un seul employé')
                                ->collapsible(false)
                                ->compact()
                                ->extraAttributes(['class' => 'cursor-pointer border-2 hover:border-primary-500 transition duration-200'])
                                ->schema([
                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('select_individual')
                                            ->label('Sélectionner')
                                            ->color('primary')
                                            ->action(function (Forms\Set $set) {
                                                $set('generation_mode', 'individuel');
                                                $this->setGenerationMode('individuel');
                                            })
                                    ])
                                ])
                                ->columnSpan(1),
                                
                            Forms\Components\Section::make('Génération en masse')
                                ->icon('heroicon-o-users')
                                ->description('Générer des bulletins pour plusieurs employés')
                                ->collapsible(false)
                                ->compact()
                                ->extraAttributes(['class' => 'cursor-pointer border-2 hover:border-primary-500 transition duration-200'])
                                ->schema([
                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('select_mass')
                                            ->label('Sélectionner')
                                            ->color('primary')
                                            ->action(function (Forms\Set $set) {
                                                $set('generation_mode', 'masse');
                                                $this->setGenerationMode('masse');
                                            })
                                    ])
                                ])
                                ->columnSpan(1),
                        ])->columnSpanFull(),
                        
                    Forms\Components\Hidden::make('generation_mode')
                        ->default('individuel')
                        ->required()
                        ->reactive()
                ])
        ];
        
        // Étapes pour le mode individuel
        $stepsIndividuel = [
            Step::make('Informations générales')
                ->description('Sélectionnez l\'employé et la période de paie')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\Select::make('employeur_id')
                        ->label('Employé')
                        ->options(function () {
                            return Employeur::where('entreprise_id', Auth::user()->entreprise_id)
                                ->where('statut', 'actif')
                                ->get()
                                ->pluck('nom_complet', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $employe = Employeur::find($state);
                                if ($employe) {
                                    $set('salaire_base', $employe->salaire_base ?? 0);
                                }
                            }
                        }),
                    
                    Forms\Components\Select::make('configuration_paie_id')
                        ->label('Configuration de paie')
                        ->options(function () {
                            return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                                ->get()
                                ->pluck('nom', 'id');
                        })
                    ->default(function () {
                        return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                            ->where('est_defaut', true)
                            ->first()?->id;
                    })
                    ->searchable()
                    ->required(),
                    
                    Forms\Components\DatePicker::make('periode_debut')
                    ->label('Début de période')
                    ->required()
                    ->afterStateHydrated(function (Forms\Components\DatePicker $component, $state) {
                        if (empty($state)) {
                            $component->state($this->debutMois);
                        }
                    }),
                    
                    Forms\Components\DatePicker::make('periode_fin')
                    ->label('Fin de période')
                    ->required()
                    ->after('periode_debut')
                    ->afterStateHydrated(function (Forms\Components\DatePicker $component, $state) {
                        if (empty($state)) {
                            $component->state($this->finMois);
                        }
                    }),
                    
                    Forms\Components\DatePicker::make('date_paiement')
                    ->label('Date de paiement')
                    ->required()
                    ->afterStateHydrated(function (Forms\Components\DatePicker $component, $state) {
                        if (empty($state)) {
                            $component->state($this->datePaiement);
                        }
                    }),
                ])
                ->columns(2)
                ->visible(fn (Forms\Get $get) => $get('generation_mode') === 'individuel'),
            
            Step::make('Éléments de rémunération')
                ->description('Définissez les éléments de rémunération')
                ->icon('heroicon-o-currency-dollar')
                ->visible(fn (Forms\Get $get) => $get('generation_mode') === 'individuel')
                ->schema([
                Forms\Components\TextInput::make('salaire_base')
                    ->label('Salaire de base')
                    ->numeric()
                    ->required()
                    ->default(0),
                    
                Forms\Components\Toggle::make('calcul_auto')
                    ->label('Calcul automatique')
                    ->helperText('Activer pour calculer automatiquement les éléments du bulletin')
                    ->default(true),
                    
                Forms\Components\Repeater::make('indemnites')
                    ->label('Indemnités')
                    ->schema($this->_getRemunerationElementSchema())
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                    ->collapsible()
                    ->defaultItems(0),
                    
                Forms\Components\Repeater::make('primes')
                    ->label('Primes')
                    ->schema($this->_getRemunerationElementSchema())
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                    ->collapsible()
                    ->defaultItems(0),
                    
                Forms\Components\Repeater::make('retenues')
                    ->label('Retenues supplémentaires')
                    ->schema([
                        Forms\Components\TextInput::make('libelle')
                            ->label('Libellé')
                            ->required(),
                            
                        Forms\Components\TextInput::make('montant')
                            ->label('Montant (FCFA)')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                    ->collapsible()
                    ->defaultItems(0),
                ]),
    
            Step::make('Aperçu et validation')
                ->description('Vérifiez les informations et générez le bulletin')
                ->icon('heroicon-o-document-check')
                ->visible(fn (Forms\Get $get) => $get('generation_mode') === 'individuel')
                ->schema([
                Forms\Components\Placeholder::make('apercu_titre')
                    ->label('Aperçu du bulletin de paie')
                    ->content(function (Forms\Get $get) {
                        $employeur = Employeur::find($get('employeur_id'));
                        return 'Bulletin de paie pour ' . ($employeur ? $employeur->nom_complet : 'Employé inconnu');
                    }),
                    
                Forms\Components\Section::make('Résultats calculés')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('salaire_brut')
                                    ->label('Salaire brut')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('total_indemnites')
                                    ->label('Total indemnités')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('total_primes')
                                    ->label('Total primes')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('cnps_employe')
                                    ->label('CNPS employé')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('igr')
                                    ->label('IGR')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('total_retenues')
                                    ->label('Total retenues')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('salaire_net')
                                    ->label('Salaire net')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('charges_patronales')
                                    ->label('Charges patronales')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                            ]),
                    ]),
                    
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('calculer')
                        ->label('Calculer le bulletin')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Forms\Get $get, Forms\Set $set) {
                            // Récupérer le service de calcul de paie
                            $calculPaieService = App::make(CalculPaieServiceInterface::class);
                            
                            // Récupérer l'employé et la configuration
                            $employeur = Employeur::find($get('employeur_id'));
                            $configuration = ConfigurationPaie::find($get('configuration_paie_id'));
                            
                            if (!$employeur || !$configuration) {
                                return;
                            }
                            
                            // Préparer les éléments supplémentaires
                            $elementsSupplementaires = [
                                'salaire_base' => $get('salaire_base'),
                                'indemnites' => $get('indemnites') ?? [],
                                'primes' => $get('primes') ?? [],
                                'retenues' => $get('retenues') ?? [],
                            ];
                            
                            // Calculer le salaire brut
                            $resultatSalaireBrut = $calculPaieService->calculerSalaireBrut($employeur, $configuration, $elementsSupplementaires);
                            $salaireBrut = $resultatSalaireBrut['salaire_brut'];
                            $set('salaire_brut', $salaireBrut);
                            
                            // Mettre à jour les totaux des indemnités et primes
                            $set('total_indemnites', $resultatSalaireBrut['total_indemnites']);
                            $set('total_primes', $resultatSalaireBrut['total_primes']);
                            
                            // Calculer les retenues salariales
                            $elementsImposables = [];
                            $retenues = $calculPaieService->calculerRetenuesSalariales($salaireBrut, $configuration, $elementsImposables);
                            $set('cnps_employe', $retenues['cnps_employe']);
                            $set('igr', $retenues['igr']);
                            $set('total_retenues', $retenues['total_retenues']);
                            
                            // Calculer le salaire net
                            $salaireNet = $calculPaieService->calculerSalaireNet($salaireBrut, $retenues['total_retenues']);
                            $set('salaire_net', $salaireNet);
                            
                            // Calculer les charges patronales
                            $chargesPatronales = $calculPaieService->calculerChargesPatronales($salaireBrut, $configuration);
                            $set('charges_patronales', $chargesPatronales['total_charges']);
                            
                            // Note: les totaux des indemnités et primes ont déjà été mis à jour plus haut
                            // à partir du résultat de calculerSalaireBrut
                        })
                        ->color('primary')
                        ->size('lg'),
                ])
                ->alignment('center'),
                
                Forms\Components\Checkbox::make('valider_directement')
                    ->label('Valider directement le bulletin')
                    ->helperText('Si coché, le bulletin sera directement validé après sa génération. Sinon, il sera enregistré en brouillon.'),
                ]),


            
        ];    // Étapes pour le mode masse
        $stepsMasse = [
            Step::make('Sélection des employés')
                ->description('Sélectionnez les employés pour lesquels générer des bulletins')
                ->icon('heroicon-o-users')
                ->visible(fn (Forms\Get $get) => $get('generation_mode') === 'masse')
                ->schema([
                    Forms\Components\CheckboxList::make('employeur_ids')
                    ->label('Employés')
                    ->options(function () {
                        return Employeur::where('entreprise_id', Auth::user()->entreprise_id)
                            ->where('statut', 'actif')
                            ->get()
                            ->pluck('nom_complet', 'id');
                    })
                    ->searchable()
                    ->bulkToggleable()
                    ->required()
                    ->columns(2),
                    
                Forms\Components\Placeholder::make('info_selection')
                    ->label('')
                    ->content(new HtmlString('<div class="text-sm text-gray-500">Sélectionnez les employés pour lesquels vous souhaitez générer des bulletins de paie. Vous pouvez sélectionner tous les employés en utilisant le bouton "Tout sélectionner".</div>')),
             ]),
            
            Step::make('Paramètres communs')
                ->description('Définissez les paramètres communs pour tous les bulletins')
                ->icon('heroicon-o-cog')
                ->visible(fn (Forms\Get $get) => $get('generation_mode') === 'masse')
                ->schema([
                Forms\Components\Select::make('configuration_paie_id')
                    ->label('Configuration de paie')
                    ->options(function () {
                        return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                            ->get()
                            ->pluck('nom', 'id');
                    })
                    ->default(function () {
                        return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                            ->where('est_defaut', true)
                            ->first()?->id;
                    })
                    ->searchable()
                    ->required(),
                    
                Forms\Components\DatePicker::make('periode_debut')
                    ->label('Début de période')
                    ->required()
                    ->afterStateHydrated(function (Forms\Components\DatePicker $component, $state) {
                        if (empty($state)) {
                            $component->state($this->debutMois);
                        }
                    }),
                    
                Forms\Components\DatePicker::make('periode_fin')
                    ->label('Fin de période')
                    ->required()
                    ->after('periode_debut')
                    ->afterStateHydrated(function (Forms\Components\DatePicker $component, $state) {
                        if (empty($state)) {
                            $component->state($this->finMois);
                        }
                    }),
                    
                Forms\Components\DatePicker::make('date_paiement')
                    ->label('Date de paiement')
                    ->required()
                    ->afterStateHydrated(function (Forms\Components\DatePicker $component, $state) {
                        if (empty($state)) {
                            $component->state($this->datePaiement);
                        }
                    }),
                    
                Forms\Components\Toggle::make('utiliser_salaire_base_employe')
                    ->label('Utiliser le salaire de base de chaque employé')
                    ->helperText('Si désactivé, vous pourrez définir un salaire de base commun pour tous les employés.')
                    ->default(true)
                    ->reactive(),
                    
                Forms\Components\TextInput::make('salaire_base_commun')
                    ->label('Salaire de base commun')
                    ->numeric()
                    ->visible(fn (Forms\Get $get) => !$get('utiliser_salaire_base_employe'))
                    ->required(fn (Forms\Get $get) => !$get('utiliser_salaire_base_employe')),
                    
                Forms\Components\Toggle::make('appliquer_indemnites_communes')
                    ->label('Appliquer des indemnités communes')
                    ->helperText('Si activé, les mêmes indemnités seront appliquées à tous les employés.')
                    ->default(false)
                    ->reactive(),
                    
                Forms\Components\Repeater::make('indemnites_communes')
                    ->label('Indemnités communes')
                    ->schema([
                        Forms\Components\TextInput::make('libelle')
                            ->label('Libellé')
                            ->required(),
                            
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'pourcentage' => 'Pourcentage du salaire de base',
                                'montant_fixe' => 'Montant fixe',
                            ])
                            ->required()
                            ->default('montant_fixe'),
                            
                        Forms\Components\TextInput::make('taux')
                            ->label('Taux (%)')
                            ->numeric()
                            ->step(0.01)
                            ->visible(fn (Forms\Get $get) => $get('type') === 'pourcentage'),
                            
                        Forms\Components\TextInput::make('montant')
                            ->label('Montant (FCFA)')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'montant_fixe'),
                            
                        Forms\Components\Toggle::make('imposable')
                            ->label('Imposable')
                            ->default(true),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('appliquer_indemnites_communes'))
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                    ->collapsible(),
                    
                Forms\Components\Toggle::make('appliquer_primes_communes')
                    ->label('Appliquer des primes communes')
                    ->helperText('Si activé, les mêmes primes seront appliquées à tous les employés.')
                    ->default(false)
                    ->reactive(),
                    
                Forms\Components\Repeater::make('primes_communes')
                    ->label('Primes communes')
                    ->schema([
                        Forms\Components\TextInput::make('libelle')
                            ->label('Libellé')
                            ->required(),
                            
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'pourcentage' => 'Pourcentage du salaire de base',
                                'montant_fixe' => 'Montant fixe',
                            ])
                            ->required()
                            ->default('montant_fixe'),
                            
                        Forms\Components\TextInput::make('taux')
                            ->label('Taux (%)')
                            ->numeric()
                            ->step(0.01)
                            ->visible(fn (Forms\Get $get) => $get('type') === 'pourcentage'),
                            
                        Forms\Components\TextInput::make('montant')
                            ->label('Montant (FCFA)')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'montant_fixe'),
                            
                        Forms\Components\Toggle::make('imposable')
                            ->label('Imposable')
                            ->default(true),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('appliquer_primes_communes'))
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                    ->collapsible(),
                    
                Forms\Components\Toggle::make('calcul_auto')
                    ->label('Calcul automatique')
                    ->helperText('Activer pour calculer automatiquement les éléments des bulletins')
                    ->default(true),
                ])
                ->columns(2),
            
            Step::make('Aperçu et validation')
                ->description('Vérifiez les informations et générez les bulletins')
                ->icon('heroicon-o-document-check')
                ->visible(fn (Forms\Get $get) => $get('generation_mode') === 'masse')
                ->schema([
                Forms\Components\Placeholder::make('apercu_titre')
                    ->label('Aperçu des bulletins à générer')
                    ->content(function (Forms\Get $get) {
                        $nbEmployes = count($get('employeur_ids') ?? []);
                        return 'Génération de ' . $nbEmployes . ' bulletin(s) de paie';
                    }),
                    
                Forms\Components\Placeholder::make('apercu_tableau')
                    ->label('')
                    ->content(function (Forms\Get $get) {
                        $employeurIds = $get('employeur_ids') ?? [];
                        if (empty($employeurIds)) {
                            return 'Aucun employé sélectionné.';
                        }
                        
                        $html = '<div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employé</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salaire de base</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">';
                        
                        $employes = Employeur::whereIn('id', $employeurIds)->get();
                        $utiliserSalaireBaseEmploye = $get('utiliser_salaire_base_employe') ?? true;
                        $salaireBaseCommun = $get('salaire_base_commun') ?? 0;
                        $periodeDebut = $get('periode_debut') ?? $this->debutMois;
                        $periodeFin = $get('periode_fin') ?? $this->finMois;
                        
                        foreach ($employes as $employe) {
                            $salaireBase = $utiliserSalaireBaseEmploye ? ($employe->salaire_base ?? 0) : $salaireBaseCommun;
                            
                            $html .= '<tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . $employe->nom_complet . '</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . number_format($salaireBase, 0, ',', ' ') . ' FCFA</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . Carbon::parse($periodeDebut)->format('d/m/Y') . ' - ' . Carbon::parse($periodeFin)->format('d/m/Y') . '</td>
                            </tr>';
                        }
                        
                        $html .= '</tbody></table></div>';
                        
                        return new HtmlString($html);
                    }),
                    
                Forms\Components\Checkbox::make('valider_directement')
                    ->label('Valider directement les bulletins')
                    ->helperText('Si coché, les bulletins seront directement validés après leur génération. Sinon, ils seront enregistrés en brouillon.'),
                    
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('generer_masse')
                        ->label('Générer les bulletins en masse')
                        ->action('generateMasse')
                        ->color('primary')
                        ->size('lg'),
                ])
                ->alignment('center'),
                ]),
        ];

        
        // Combiner les étapes
        return array_merge($steps, $stepsIndividuel, $stepsMasse);
    }
    
    public function generateMasse()
    {
        $data = $this->form->getState();
        
        try {
            // Récupérer le service de calcul de paie
            $calculPaieService = App::make(CalculPaieServiceInterface::class);
            
            // Récupérer la configuration
            $configuration = ConfigurationPaie::findOrFail($data['configuration_paie_id']);
            
            // Récupérer les employés
            $employeurIds = $data['employeur_ids'] ?? [];
            if (empty($employeurIds)) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Aucun employé sélectionné.')
                    ->danger()
                    ->send();
                return;
            }
            
            $employes = Employeur::whereIn('id', $employeurIds)->get();
            
            // Paramètres communs
            $periodeDebut = $data['periode_debut'] ?? $this->debutMois;
            $periodeFin = $data['periode_fin'] ?? $this->finMois;
            $datePaiement = $data['date_paiement'] ?? $this->datePaiement;
            $utiliserSalaireBaseEmploye = $data['utiliser_salaire_base_employe'] ?? true;
            $salaireBaseCommun = $data['salaire_base_commun'] ?? 0;
            $appliquerIndemnitesCommunes = $data['appliquer_indemnites_communes'] ?? false;
            $indemnites = $appliquerIndemnitesCommunes ? ($data['indemnites_communes'] ?? []) : [];
            $appliquerPrimesCommunes = $data['appliquer_primes_communes'] ?? false;
            $primes = $appliquerPrimesCommunes ? ($data['primes_communes'] ?? []) : [];
            $calculAuto = $data['calcul_auto'] ?? true;
            $validerDirectement = $data['valider_directement'] ?? false;
            
            // Générer les bulletins
            $bulletinsGeneres = [];
            
            foreach ($employes as $employe) {
                // Déterminer le salaire de base
                $salaireBase = $utiliserSalaireBaseEmploye ? ($employe->salaire_base ?? 0) : $salaireBaseCommun;
                
                // Préparer les éléments supplémentaires
                $elementsSupplementaires = [
                    'salaire_base' => $salaireBase,
                    'indemnites' => $indemnites,
                    'primes' => $primes,
                    'retenues' => [],
                ];
                
                // Préparer les paramètres pour le service de calcul de paie
                $parametres = [
                    'periode_debut' => $periodeDebut,
                    'periode_fin' => $periodeFin,
                    'date_paiement' => $datePaiement,
                    'genere_par' => Auth::id(),
                    'entreprise_id' => Auth::user()->entreprise_id,
                    'elements_supplementaires' => $elementsSupplementaires,
                    'calcul_auto' => $calculAuto,
                ];
                
                // Générer le bulletin de paie
                $bulletin = $calculPaieService->genererBulletinPaie($employe, $configuration, $parametres);
                
                // Valider directement le bulletin si demandé
                if ($validerDirectement) {
                    $this->_validerBulletin($bulletin);
                }
                
                $bulletinsGeneres[] = $bulletin;
            }
            
            // Notification de succès
            $message = count($bulletinsGeneres) . ' bulletin(s) de paie généré(s)';
            if ($validerDirectement) {
                $message .= ' et validé(s)';
            }
            
            Notification::make()
                ->title($message)
                ->success()
                ->send();
            
            return redirect()->route('filament.admin.resources.paie.bulletin-paies.index');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors de la génération des bulletins')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Méthode utilitaire pour valider un bulletin
     */
    private function _validerBulletin(Model $bulletin): void
    {
        $bulletin->update([
            'statut' => 'validé',
            'valide_par' => Auth::id(),
            'date_validation' => now(),
        ]);
    }
    
    /**
     * Méthode utilitaire pour préparer les paramètres du bulletin
     */
    private function _prepareBulletinParameters(Employeur $employeur, array $data): array
    {
        // Préparer les éléments supplémentaires
        $elementsSupplementaires = [
            'salaire_base' => $data['salaire_base'] ?? $employeur->salaire_base ?? 0,
            'indemnites' => $data['indemnites'] ?? [],
            'primes' => $data['primes'] ?? [],
            'retenues' => $data['retenues'] ?? [],
        ];
        
        // Préparer les paramètres pour le service de calcul de paie
        return [
            'periode_debut' => $data['periode_debut'],
            'periode_fin' => $data['periode_fin'],
            'date_paiement' => $data['date_paiement'],
            'genere_par' => Auth::id(),
            'entreprise_id' => Auth::user()->entreprise_id,
            'elements_supplementaires' => $elementsSupplementaires,
            'calcul_auto' => $data['calcul_auto'] ?? true,
        ];
    }
    
    /**
     * Méthode utilitaire pour obtenir le schéma commun des éléments de rémunération
     */
    private function _getRemunerationElementSchema(): array
    {
        return [
            Forms\Components\TextInput::make('libelle')
                ->label('Libellé')
                ->required(),
                
            Forms\Components\Select::make('type')
                ->label('Type')
                ->options([
                    'pourcentage' => 'Pourcentage du salaire de base',
                    'montant_fixe' => 'Montant fixe',
                ])
                ->required()
                ->default('montant_fixe')
                ->reactive(),
                
            Forms\Components\TextInput::make('taux')
                ->label('Taux (%)')
                ->numeric()
                ->step(0.01)
                ->visible(fn (Forms\Get $get) => $get('type') === 'pourcentage'),
                
            Forms\Components\TextInput::make('montant')
                ->label('Montant (FCFA)')
                ->numeric()
                ->visible(fn (Forms\Get $get) => $get('type') === 'montant_fixe'),
                
            Forms\Components\Toggle::make('imposable')
                ->label('Imposable')
                ->default(true),
        ];
    }
}
