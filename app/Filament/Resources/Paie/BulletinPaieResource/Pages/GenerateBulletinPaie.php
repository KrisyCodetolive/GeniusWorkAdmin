<?php

namespace App\Filament\Resources\Paie\BulletinPaieResource\Pages;

use App\Filament\Resources\Paie\BulletinPaieResource;
use App\Interfaces\Paie\CalculPaieServiceInterface;
use App\Models\Employeur;
use App\Models\Paie\BulletinPaie;
use App\Models\Paie\ConfigurationPaie;
use App\Models\Paie\ElementPaie;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class GenerateBulletinPaie extends Page
{
    protected static string $resource = BulletinPaieResource::class;
    
    protected static string $view = 'filament.resources.paie.bulletin-paie-resource.pages.generate-bulletin-paie';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        // Obtenir le premier jour du mois courant
        $debutMois = Carbon::now()->startOfMonth()->format('Y-m-d');
        // Obtenir le dernier jour du mois courant
        $finMois = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        return $form
            ->schema([
                Wizard::make([
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
                                ->default($debutMois)
                                ->required(),
                                
                            Forms\Components\DatePicker::make('periode_fin')
                                ->label('Fin de période')
                                ->default($finMois)
                                ->required()
                                ->after('periode_debut'),
                                
                            Forms\Components\DatePicker::make('date_paiement')
                                ->label('Date de paiement')
                                ->default(Carbon::now()->format('Y-m-d'))
                                ->required(),
                        ])
                        ->columns(2),
                        
                    Step::make('Éléments de rémunération')
                        ->description('Définissez les éléments de rémunération')
                        ->icon('heroicon-o-currency-dollar')
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
                                ->columns(2)
                                ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                                ->collapsible()
                                ->defaultItems(0),
                                
                            Forms\Components\Repeater::make('primes')
                                ->label('Primes')
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
                                        $salaireBrut = $calculPaieService->calculerSalaireBrut($employeur, $configuration, $elementsSupplementaires);
                                        $set('salaire_brut', $salaireBrut);
                                        
                                        // Calculer les retenues
                                        $retenues = $calculPaieService->calculerRetenues($employeur, $configuration, $salaireBrut, $elementsSupplementaires);
                                        $set('cnps_employe', $retenues['cnps_employe']);
                                        $set('igr', $retenues['igr']);
                                        $set('total_retenues', $retenues['total']);
                                        
                                        // Calculer le salaire net
                                        $salaireNet = $calculPaieService->calculerSalaireNet($salaireBrut, $retenues['total']);
                                        $set('salaire_net', $salaireNet);
                                        
                                        // Calculer les charges patronales
                                        $chargesPatronales = $calculPaieService->calculerChargesPatronales($employeur, $configuration, $salaireBrut);
                                        $set('charges_patronales', $chargesPatronales['total']);
                                        
                                        // Calculer les totaux des indemnités et primes
                                        $set('total_indemnites', $calculPaieService->calculerTotalIndemnites($elementsSupplementaires['indemnites'], $get('salaire_base')));
                                        $set('total_primes', $calculPaieService->calculerTotalPrimes($elementsSupplementaires['primes'], $get('salaire_base')));
                                    })
                                    ->color('primary')
                                    ->size('lg'),
                            ])
                            ->alignment('center'),
                            
                            Forms\Components\Checkbox::make('valider_directement')
                                ->label('Valider directement le bulletin')
                                ->helperText('Si coché, le bulletin sera directement validé après sa génération. Sinon, il sera enregistré en brouillon.'),
                        ]),
                ])
                ->skippable()
                ->persistStepInQueryString('step')
            ])
            ->statePath('data');
    }
    
    public function generate()
    {
        $data = $this->form->getState();
        
        try {
            // Récupérer le service de calcul de paie
            $calculPaieService = App::make(CalculPaieServiceInterface::class);
            
            // Récupérer l'employé et la configuration
            $employeur = Employeur::findOrFail($data['employeur_id']);
            $configuration = ConfigurationPaie::findOrFail($data['configuration_paie_id']);
            
            // Préparer les éléments supplémentaires
            $elementsSupplementaires = [
                'salaire_base' => $data['salaire_base'] ?? $employeur->salaire_base ?? 0,
                'indemnites' => $data['indemnites'] ?? [],
                'primes' => $data['primes'] ?? [],
                'retenues' => $data['retenues'] ?? [],
            ];
            
            // Préparer les paramètres pour le service de calcul de paie
            $parametres = [
                'periode_debut' => $data['periode_debut'],
                'periode_fin' => $data['periode_fin'],
                'date_paiement' => $data['date_paiement'],
                'genere_par' => Auth::id(),
                'entreprise_id' => Auth::user()->entreprise_id,
                'elements_supplementaires' => $elementsSupplementaires,
                'calcul_auto' => $data['calcul_auto'] ?? true,
            ];
            
            // Générer le bulletin de paie
            $bulletin = $calculPaieService->genererBulletinPaie($employeur, $configuration, $parametres);
            
            // Valider directement le bulletin si demandé
            if ($data['valider_directement'] ?? false) {
                $bulletin->update([
                    'statut' => 'validé',
                    'valide_par' => Auth::id(),
                    'date_validation' => now(),
                ]);
                
                Notification::make()
                    ->title('Bulletin généré et validé')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Bulletin généré')
                    ->success()
                    ->send();
            }
            
            return redirect()->route('filament.admin.resources.paie.bulletin-paies.view', ['record' => $bulletin->id]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors de la génération du bulletin')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
