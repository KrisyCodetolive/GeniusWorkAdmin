<?php

namespace App\Filament\Resources\EntrepriseResource\Pages;

use App\Filament\Resources\EntrepriseResource;
use App\Models\Abonnement;
use App\Models\Employeur;
use App\Models\PlanAbonnement;
use App\Models\Role;
use App\Models\User;
use App\Services\EntrepriseService;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CreateEntreprise extends CreateRecord
{
    use HasWizard;

    protected static string $resource = EntrepriseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Informations de l\'entreprise')
                ->icon('heroicon-o-building-office')
                ->description('Informations générales de l\'entreprise')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('nom')
                                ->label('Nom de l\'entreprise')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('nif')
                                ->label('Numéro d\'identification fiscale')
                                ->maxLength(20)
                                ->unique('entreprises', 'nif')
                                ->helperText('SIRET, SIREN, TVA, NIF, etc.'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('rccm')
                                ->label('Registre du Commerce (RCCM)')
                                ->maxLength(20)
                                ->unique('entreprises', 'rccm')
                                ->helperText('Numéro d\'immatriculation au registre du commerce'),
                            TextInput::make('raison_sociale')
                                ->label('Raison sociale')
                                ->maxLength(255),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('telephone')
                                ->label('Téléphone')
                                ->tel()
                                ->required()
                                ->maxLength(20),
                            TextInput::make('site_web')
                                ->label('Site web')
                                ->maxLength(255)
                                ->placeholder('geniuswork.com')
                                ->helperText('Exemple: geniuswork.com')
                                ->rules(['nullable', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]\.[a-zA-Z]{2,}$/'])
                                ->dehydrateStateUsing(function ($state) {
                                    if (empty($state)) {
                                        return null;
                                    }
                                    // Nettoyer l'URL
                                    $url = preg_replace('#^https?://#', '', $state);
                                    return 'https://' . $url;
                                }),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('secteur_activite')
                                ->label('Secteur d\'activité')
                                ->options([
                                    'technologie' => 'Technologie',
                                    'sante' => 'Santé',
                                    'education' => 'Éducation',
                                    'finance' => 'Finance',
                                    'commerce' => 'Commerce',
                                    'industrie' => 'Industrie',
                                    'services' => 'Services',
                                    'autre' => 'Autre',
                                ])
                                ->required(),
                        ]),
                    Textarea::make('description')
                        ->label('Description')
                        ->maxLength(1000)
                        ->rows(3),
                ]),

            Step::make('Adresse')
                ->icon('heroicon-o-map-pin')
                ->description('Adresse de l\'entreprise')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('adresse')
                                ->label('Adresse')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('complement_adresse')
                                ->label('Complément d\'adresse')
                                ->maxLength(255),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('code_postal')
                                ->label('Code postal')
                                ->maxLength(10),
                            TextInput::make('ville')
                                ->label('Ville')
                                ->required()
                                ->maxLength(255),
                            Select::make('pays')
                                ->label('Pays')
                                ->options([
                                    'CI' => 'Côte d\'Ivoire',
                                    'BF' => 'Burkina Faso',
                                    'BJ' => 'Bénin',
                                    'CD' => 'Congo',
                                    'GH' => 'Ghana',
                                    'ML' => 'Mali',
                                    'NE' => 'Nigéria',
                                    'SN' => 'Sénégal',
                                    'TG' => 'Togo',
                                    'AO' => 'Angola',
                                    'BW' => 'Botswana',
                                    'BW' => 'Botswana',
                                    'CM' => 'Cameroun',
                                    'GA' => 'Gabon',
                                    'GH' => 'Ghana',
                                    'GM' => 'Gambie',
                                    'GN' => 'Guiné',
                                    'GW' => 'Guiné-Bissau',
                                    'LR' => 'Liberia',
                                    'MG' => 'Madagascar',
                                    'MW' => 'Malawi',
                                    'MV' => 'Maldives',
                                    'ML' => 'Mali',
                                    'MR' => 'Mauritania',
                                    'MZ' => 'Mozambique',
                                    'NA' => 'Namibia',
                                    'NE' => 'Niger',
                                    'NG' => 'Nigeria',
                                    'RW' => 'Rwanda',
                                    'SC' => 'Seychelles',
                                    'SL' => 'Sierra Leone',
                                    'ST' => 'Sao Tome et Principe',
                                    'TZ' => 'Tanzanie',
                                    'UG' => 'Ouganda',
                                    'ZM' => 'Zambie',
                                    'ZW' => 'Zimbabwe',
                                    'MA' => 'Maroc',
                                    'DZ' => 'Algerie',
                                    'TN' => 'Tunisie',
                                    'ES' => 'Espagne',
                                    'FR' => 'France',
                                    'BE' => 'Belgique',
                                    'CH' => 'Suisse',
                                    'LU' => 'Luxembourg',
                                ])
                                ->default('CI'),
                        ]),
                ]),

            Step::make('Abonnement')
                ->icon('heroicon-o-credit-card')
                ->description('Choisissez un plan d\'abonnement')
                ->schema([
                    Section::make('Plan d\'abonnement')
                        ->schema([
                            TextInput::make('nombre_personnels')
                                ->label('Nombre de personnels')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, $get) {
                                    $periode = $get('periode_facturation') ?? 'mensuel';
                                    $reduction = $get('reduction') ?? 0;
                                    $this->calculerMontantAbonnement($state, $periode, $reduction, $set, $get('code_promo'));
                                    
                                    // Déterminer et afficher le forfait
                                    if ($state <= 50) {
                                        $set('forfait_affiche', 'Starter (1-50 utilisateurs)');
                                    } elseif ($state <= 100) {
                                        $set('forfait_affiche', 'Side Business (51-100 utilisateurs)');
                                    } else {
                                        $set('forfait_affiche', 'Entreprise (100+ utilisateurs)');
                                    }
                                }),
                            TextInput::make('forfait_affiche')
                                ->label('Forfait')
                                ->default('Starter (1-50 utilisateurs)')
                                ->disabled(),
                            Select::make('periode_facturation')
                                ->label('Période de facturation')
                                ->options([
                                    'mensuel' => 'Mensuel',
                                    'trimestriel' => 'Trimestriel',
                                    'semestriel' => 'Semestriel',
                                    'annuel' => 'Annuel',
                                ])
                                ->default('mensuel')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, $get) {
                                    $nombrePersonnels = $get('nombre_personnels');
                                    $reduction = $get('reduction') ?? 0;
                                    $this->calculerMontantAbonnement($nombrePersonnels, $state, $reduction, $set, $get('code_promo'));
                                }),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('code_promo')
                                        ->label('Code promo')
                                        ->placeholder('Saisir un code promo')
                                        ->helperText('Laissez vide si vous n\'avez pas de code promo')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, $get) {
                                            if (!empty($state)) {
                                                // Convertir en majuscules
                                                $state = strtoupper($state);
                                                $set('code_promo', $state);
                                                
                                                // Vérifier si le code promo existe et est valide
                                                $codePromo = \App\Models\CodePromo::where('code', $state)->first();
                                                
                                                if ($codePromo && $codePromo->estValide()) {
                                                    $set('code_promo_valide', true);
                                                    $set('code_promo_message', 'Code promo valide : ' . $codePromo->reduction . '% de réduction');
                                                    $set('code_promo_id', $codePromo->id);
                                                    $set('reduction_code_promo', $codePromo->reduction);
                                                    
                                                    // Recalculer le montant
                                                    $nombrePersonnels = $get('nombre_personnels');
                                                    $periode = $get('periode_facturation');
                                                    $reduction = $get('reduction') ?? 0;
                                                    $this->calculerMontantAbonnement($nombrePersonnels, $periode, $reduction, $set, $state);
                                                } else {
                                                    $set('code_promo_valide', false);
                                                    $set('code_promo_message', $codePromo ? 'Code promo expiré ou invalide' : 'Code promo inexistant');
                                                    $set('code_promo_id', null);
                                                    $set('reduction_code_promo', null);
                                                    
                                                    // Recalculer le montant sans code promo
                                                    $nombrePersonnels = $get('nombre_personnels');
                                                    $periode = $get('periode_facturation');
                                                    $reduction = $get('reduction') ?? 0;
                                                    $this->calculerMontantAbonnement($nombrePersonnels, $periode, $reduction, $set, null);
                                                }
                                            } else {
                                                $set('code_promo_valide', null);
                                                $set('code_promo_message', null);
                                                $set('code_promo_id', null);
                                                $set('reduction_code_promo', null);
                                                
                                                // Recalculer le montant sans code promo
                                                $nombrePersonnels = $get('nombre_personnels');
                                                $periode = $get('periode_facturation');
                                                $reduction = $get('reduction') ?? 0;
                                                $this->calculerMontantAbonnement($nombrePersonnels, $periode, $reduction, $set, null);
                                            }
                                        }),
                                    
                                    TextInput::make('reduction')
                                        ->label('Réduction manuelle (%)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->default(0)
                                        ->suffix('%')
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, $get) {
                                            $nombrePersonnels = $get('nombre_personnels');
                                            $periode = $get('periode_facturation');
                                            $this->calculerMontantAbonnement($nombrePersonnels, $periode, $state, $set, $get('code_promo'));
                                        }),
                                ]),
                            
                            // Afficher le message de validation du code promo
                            Forms\Components\Placeholder::make('code_promo_message')
                                ->label('')
                                ->content(fn ($get) => $get('code_promo_message'))
                                ->visible(fn ($get) => $get('code_promo_message') !== null)
                                ->extraAttributes(fn ($get) => [
                                    'class' => $get('code_promo_valide') ? 'text-success-600' : 'text-danger-600',
                                ]),
                            
                            // Champs cachés pour stocker les informations du code promo
                            Forms\Components\Hidden::make('code_promo_id'),
                            Forms\Components\Hidden::make('reduction_code_promo'),
                            Forms\Components\Hidden::make('code_promo_valide'),
                            
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('montant')
                                        ->label('Montant')
                                        ->numeric()
                                        ->required()
                                        ->disabled(),
                                    Select::make('devise')
                                        ->label('Devise')
                                        ->options([
                                            'CI' => 'CFA (XOF)',
                                            'EUR' => 'Euro (€)',
                                            'USD' => 'Dollar ($)',
                                            'CHF' => 'Franc suisse (CHF)',
                                        ])
                                        ->default('CI')
                                        ->required()
                                        ->disabled(),
                                ]),
                            DatePicker::make('date_debut')
                                ->label('Date de début')
                                ->default(now())
                                ->required(),
                            Grid::make(2)
                                ->schema([
                                    Select::make('statut')
                                        ->label('Statut')
                                        ->options([
                                            'actif' => 'Actif',
                                            'inactif' => 'Inactif',
                                            'suspendu' => 'Suspendu',
                                            'en_attente' => 'En attente'
                                        ])
                                        ->default('actif')
                                        ->required(),
                                    Toggle::make('renouvellement_automatique')
                                        ->label('Renouvellement automatique')
                                        ->default(true),
                                ]),
                        ]),
                ]),

            Step::make('Configuration')
                ->icon('heroicon-o-cog-6-tooth')
                ->description('Configuration de l\'entreprise')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('fuseau_horaire')
                                ->label('Fuseau horaire')
                                ->options([
                                    'Abidjan' => 'Abidjan (UTC)',
                                    'UTC' => 'Temps universel (UTC)',
                                    'Europe/Paris' => 'Paris (UTC+1)',
                                    'Europe/Brussels' => 'Bruxelles (UTC+1)',
                                    'Europe/Zurich' => 'Zurich (UTC+1)',
                                    'Europe/Luxembourg' => 'Luxembourg (UTC+1)',
                                ])
                                ->default('Abidjan')
                                ->required(),
                            Select::make('langue')
                                ->label('Langue')
                                ->options([
                                    'fr' => 'Français',
                                    'en' => 'Anglais',
                                    'nl' => 'Néerlandais',
                                    'de' => 'Allemand',
                                ])
                                ->default('fr')
                                ->required(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('notifications_email')
                                ->label('Activer les notifications par email')
                                ->default(true),
                            Toggle::make('notifications_sms')
                                ->label('Activer les notifications par SMS')
                                ->default(false),
                        ]),
                    FileUpload::make('logo')
                        ->label('Logo de l\'entreprise')
                        ->image()
                        ->directory('entreprises/logos')
                        ->maxSize(5120)
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('1:1')
                        ->imageResizeTargetWidth('200')
                        ->imageResizeTargetHeight('200'),
                ]),

            Step::make('Administrateur')
                ->icon('heroicon-o-user')
                ->description('Créez un compte administrateur')
                ->schema([
                    Section::make('Informations personnelles')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('admin.prenom')
                                        ->label('Prénom')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('admin.nom')
                                        ->label('Nom')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('admin.email')
                                        ->label('Email')
                                        ->email()
                                        ->required()
                                        ->unique('users', 'email')
                                        ->maxLength(255)
                                        ->rules(['email:rfc,dns'])
                                        ->helperText('Entrez une adresse email valide')
                                        ->placeholder('admin@entreprise.com'),
                                    TextInput::make('admin.telephone')
                                        ->label('Téléphone')
                                        ->tel()
                                        ->maxLength(20),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('admin.password')
                                        ->label('Mot de passe')
                                        ->password()
                                        ->required()
                                        ->minLength(8)
                                        ->rules([
                                            'regex:/[a-z]/',
                                            'regex:/[A-Z]/',
                                            'regex:/[0-9]/',
                                            'regex:/[@$!%*#?&]/'
                                        ])
                                        ->helperText('Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial')
                                        ->confirmed(),
                                    TextInput::make('admin.password_confirmation')
                                        ->label('Confirmation du mot de passe')
                                        ->password()
                                        ->required()
                                        ->minLength(8),
                                ]),
                        ]),
                ]),
        ];
    }

    protected function calculerMontantAbonnement($nombrePersonnels, $periode, $reduction, $set, $codePromo = null)
    {
        // Déterminer le forfait en fonction du nombre de personnels
        $coutFixeMensuel = 0;
        
        if ($nombrePersonnels <= 50) {
            // Forfait Starter (1-50)
            $coutFixeMensuel = 10000;
        } elseif ($nombrePersonnels <= 100) {
            // Forfait Side Business (50-100)
            $coutFixeMensuel = 15000;
        } else {
            // Forfait Entreprise (100+)
            $coutFixeMensuel = 30000;
        }
        
        // Coût par utilisateur (100 FCFA par utilisateur)
        $coutUtilisateurs = $nombrePersonnels * 100;
        
        // Coût mensuel total
        $coutMensuelTotal = $coutFixeMensuel + $coutUtilisateurs;
        
        // Calculer le montant en fonction de la période sans réduction
        $montantSansReduction = $coutMensuelTotal;
        
        switch ($periode) {
            case 'mensuel':
                $montantSansReduction = $coutMensuelTotal;
                $set('date_fin', now()->addMonth());
                break;
            case 'trimestriel':
                $montantSansReduction = $coutMensuelTotal * 3 ; 
                $set('date_fin', now()->addMonths(3));
                break;
            case 'semestriel':
                $montantSansReduction = $coutMensuelTotal * 6 ; 
                $set('date_fin', now()->addMonths(6));
                break;
            case 'annuel':
                $montantSansReduction = $coutMensuelTotal * 12 ; 
                $set('date_fin', now()->addYear());
                break;
            default:
                $montantSansReduction = $coutMensuelTotal;
                $set('date_fin', now()->addMonth());
        }
        
        // Appliquer la réduction manuelle
        $reductionDecimale = $reduction / 100;
        $montantReduction = $montantSansReduction * $reductionDecimale;
        $montantFinal = $montantSansReduction - $montantReduction;
        
        // Appliquer la réduction du code promo si présent et valide
        if (!empty($codePromo)) {
            $codePromoObj = \App\Models\CodePromo::where('code', $codePromo)->first();
            
            if ($codePromoObj && $codePromoObj->estValide()) {
                $reductionCodePromo = $codePromoObj->reduction / 100;
                $montantReductionCodePromo = $montantFinal * $reductionCodePromo;
                $montantFinal = $montantFinal - $montantReductionCodePromo;
            }
        }
        
        $set('montant', round($montantFinal, 2));
    }

    public function create(bool $another = false): void
    {
        try {
            $entrepriseService = new EntrepriseService();
            $entreprise = $entrepriseService->createEntreprise($this->data);

            Notification::make()
                ->title('Entreprise créée avec succès')
                ->body('L\'entreprise ' . $entreprise->nom . ' a été créée avec succès.')
                ->success()
                ->send();

            // Redirection
            if ($another) {
                $this->redirect($this->getResource()::getUrl('create'));
            } else {
                $this->redirect($this->getRedirectUrl());
            }

        } catch (\Exception $e) {
            Log::error('Error creating enterprise: ' . $e->getMessage());

            Notification::make()
                ->title('Erreur lors de la création de l\'entreprise')
                ->body('Une erreur est survenue lors de la création de l\'entreprise : ' . $e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
