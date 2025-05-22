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

class GenerateBulletinPaieMasse extends Page
{
    protected static string $resource = BulletinPaieResource::class;
    
    protected static string $view = 'filament.resources.paie.bulletin-paie-resource.pages.generate-bulletin-paie-masse';
    
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
                    Step::make('Sélection des employés')
                        ->description('Sélectionnez les employés pour lesquels générer des bulletins')
                        ->icon('heroicon-o-users')
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
                                    $periodeDebut = $get('periode_debut') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
                                    $periodeFin = $get('periode_fin') ?? Carbon::now()->endOfMonth()->format('Y-m-d');
                                    
                                    foreach ($employes as $employe) {
                                        $salaireBase = $utiliserSalaireBaseEmploye ? ($employe->salaire_base ?? 0) : $salaireBaseCommun;
                                        
                                        $html .= '<tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . $employe->nom_complet . '</td>
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
                        ]),
                ])
                ->skippable()
                ->persistStepInQueryString('step')
            ])
            ->statePath('data');
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
            $periodeDebut = $data['periode_debut'];
            $periodeFin = $data['periode_fin'];
            $datePaiement = $data['date_paiement'];
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
                    $bulletin->update([
                        'statut' => 'validé',
                        'valide_par' => Auth::id(),
                        'date_validation' => now(),
                    ]);
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
}
