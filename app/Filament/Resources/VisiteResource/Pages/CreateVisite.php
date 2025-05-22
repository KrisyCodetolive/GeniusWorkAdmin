<?php

namespace App\Filament\Resources\VisiteResource\Pages;

use App\Filament\Resources\VisiteResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use App\Services\Visite\VisiteurService;
use App\Services\Visite\VisiteService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\Entreprise;
use App\Models\Site;
use App\Models\Visiteur;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateVisite extends CreateRecord
{
    use HasWizard;

    protected static string $resource = VisiteResource::class;

    /**
     * @var VisiteurService
     */
    protected $visiteurService;

    /**
     * @var VisiteService
     */
    protected $visiteService;
    
    /**
     * Initialise les services lors du montage de la page
     */
    public function mount(): void
    {
        parent::mount();
        // Initialize services
        $this->visiteurService = app(\App\Services\Visite\VisiteurService::class);
        $this->visiteService = app(\App\Services\Visite\VisiteService::class);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSteps(): array
    {
        return [
            Wizard\Step::make('Information du visiteur')
                ->description('Saisissez le numéro de téléphone du visiteur')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\TextInput::make('telephone')
                        ->label('Numéro de téléphone')
                        ->tel()
                        ->required()
                        ->maxLength(20)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (empty($state)) {
                                $set('visiteur_uuid', null);
                                $set('visiteur_existe', false);
                                return;
                            }
                            
                            $entrepriseId = auth()->user()->entreprise_id;
                            $visiteur = \App\Models\Visiteur::findByTelephone($state, $entrepriseId);
                            
                            if ($visiteur) {
                                // Visiteur existant trouvé
                                $set('visiteur_id', $visiteur->id);
                                $set('visiteur_existe', true);
                                $set('visiteur_info', $visiteur->nom_complet . ' - ' . $visiteur->organisation);
                                
                                // Pré-remplir les champs pour référence
                                $set('nouveau_visiteur.nom', $visiteur->nom);
                                $set('nouveau_visiteur.prenom', $visiteur->prenom);
                                $set('nouveau_visiteur.email', $visiteur->email);
                                $set('nouveau_visiteur.organisation', $visiteur->organisation);
                                $set('nouveau_visiteur.fonction', $visiteur->fonction);
                            } else {
                                // Nouveau visiteur
                                $set('visiteur_id', null);
                                $set('visiteur_existe', false);
                                $set('visiteur_info', 'Nouveau visiteur - Veuillez compléter les informations');
                                
                                // Pré-remplir le numéro de téléphone pour le nouveau visiteur
                                $set('nouveau_visiteur.telephone', $state);
                            }
                        }),
                    
                    Forms\Components\Hidden::make('visiteur_id'),
                    Forms\Components\Hidden::make('visiteur_existe'),
                    
                    Forms\Components\TextInput::make('visiteur_info')
                        ->label('Statut du visiteur')
                        ->disabled()
                        ->dehydrated(false),
                    
                    Forms\Components\Section::make('Informations du visiteur')
                        ->description(function ($get) {
                            return $get('visiteur_existe') 
                                ? 'Visiteur existant trouvé. Vous pouvez passer à l\'étape suivante.' 
                                : 'Nouveau visiteur. Veuillez compléter les informations.'; 
                        })
                        ->schema([
                            Forms\Components\TextInput::make('nouveau_visiteur.nom')
                                ->label('Nom')
                                ->required(fn ($get) => !$get('visiteur_existe'))
                                ->disabled(fn ($get) => $get('visiteur_existe'))
                                ->maxLength(100),
                            Forms\Components\TextInput::make('nouveau_visiteur.prenom')
                                ->label('Prénom')
                                ->required(fn ($get) => !$get('visiteur_existe'))
                                ->disabled(fn ($get) => $get('visiteur_existe'))
                                ->maxLength(100),
                            Forms\Components\TextInput::make('nouveau_visiteur.telephone')
                                ->label('Téléphone')
                                ->tel()
                                ->required(fn ($get) => !$get('visiteur_existe'))
                                ->disabled(true) // Toujours désactivé car déjà saisi plus haut
                                ->maxLength(20),
                            Forms\Components\TextInput::make('nouveau_visiteur.email')
                                ->label('Email')
                                ->email()
                                ->disabled(fn ($get) => $get('visiteur_existe'))
                                ->maxLength(255),
                            Forms\Components\TextInput::make('nouveau_visiteur.organisation')
                                ->label('Organisation/Entreprise')
                                ->disabled(fn ($get) => $get('visiteur_existe'))
                                ->maxLength(255),
                            Forms\Components\TextInput::make('nouveau_visiteur.fonction')
                                ->label('Fonction/Poste')
                                ->disabled(fn ($get) => $get('visiteur_existe'))
                                ->maxLength(255),
                        ])
                        ->columns(2),
                ])
                ->afterValidation(function ($state, callable $set, $livewire) {
                    // Validation déjà gérée par les règles de champ
                }),
            
            Wizard\Step::make('Informations de la visite')
                ->description('Détails de la visite')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Forms\Components\Section::make('Détails de la visite')
                        ->schema([
                            Forms\Components\Select::make('site_id')
                                ->label('Site')
                                ->options(function () {
                                    $entrepriseId = auth()->user()->entreprise_id;
                                    return Site::where('entreprise_id', $entrepriseId)
                                        ->pluck('nom', 'id');
                                })
                                ->searchable()
                                ->required(),
                            Forms\Components\DateTimePicker::make('date_arrivee')
                                ->label('Date et heure d\'arrivée')
                                ->default(now())
                                ->required(),
                            Forms\Components\TextInput::make('motif_visite')
                                ->label('Motif de la visite')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('personne_a_rencontrer')
                                ->label('Personne à rencontrer')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('departement_a_visiter')
                                ->label('Département à visiter')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('badge_visiteur')
                                ->label('Badge visiteur')
                                ->maxLength(50),
                            Forms\Components\Textarea::make('commentaires')
                                ->label('Commentaires')
                                ->rows(3)
                                ->maxLength(1000),
                            Forms\Components\Hidden::make('entreprise_id')
                                ->default(fn () => auth()->user()->entreprise_id),
                            Forms\Components\Hidden::make('statut')
                                ->default('en_cours'),
                        ])
                ]),
        ];
    }

    public function create(bool $another = false): void
    {
        try {
            // Ensure VisiteService is available
            if (!$this->visiteService) {
                $this->visiteService = app(\App\Services\Visite\VisiteService::class);
            }
            
            // Préparer les données pour le traitement
            $data = $this->form->getState();
            
            // Extraire les données du visiteur et de la visite
            $visiteurData = [];
            $visiteData = [];
            
            // Traitement selon que le visiteur existe ou non
            if (isset($data['visiteur_existe']) && $data['visiteur_existe'] && isset($data['visiteur_id']) && !empty($data['visiteur_id'])) {
                // Cas d'un visiteur existant - on ajoute l'ID à la visite
                $visiteData['visiteur_id'] = $data['visiteur_id'];
                Log::info('Utilisation d\'un visiteur existant avec ID: ' . $data['visiteur_id']);
            } elseif (isset($data['nouveau_visiteur'])) {
                // Cas d'un nouveau visiteur
                $visiteurData = $data['nouveau_visiteur'];
                $visiteurData['telephone'] = $data['telephone'] ?? $visiteurData['telephone']; // S'assurer que le téléphone est bien récupéré
                $visiteurData['entreprise_id'] = $data['entreprise_id'] ?? auth()->user()->entreprise_id;
                $visiteurData['statut'] = 'actif';
                Log::info('Création d\'un nouveau visiteur avec téléphone: ' . $visiteurData['telephone']);
            }
            
            // Préparer les données de la visite
            foreach ($data as $key => $value) {
                if (!in_array($key, ['telephone', 'visiteur_existe', 'visiteur_info', 'visiteur_id', 'nouveau_visiteur'])) {
                    $visiteData[$key] = $value;
                }
            }
            
            // S'assurer que l'entreprise_id est défini
            if (!isset($visiteData['entreprise_id'])) {
                $visiteData['entreprise_id'] = auth()->user()->entreprise_id;
            }
            
            // Log data before creating visite
            Log::info('Création de visite avec les données: ' . json_encode([
                'visiteur' => $visiteurData,
                'visite' => $visiteData
            ]));
            
            // Créer la visite en utilisant le service
            $visite = $this->visiteService->createVisite(
                $visiteurData,
                $visiteData
            );

            // Notification de succès
            Notification::make()
                ->title('Visite créée avec succès')
                ->body('La visite a été créée avec succès.')
                ->success()
                ->send();

            // Redirection
            if ($another) {
                $this->redirect($this->getResource()::getUrl('create'));
            } else {
                $this->redirect($this->getRedirectUrl());
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la visite: ' . $e->getMessage());

            Notification::make()
                ->title('Erreur lors de la création de la visite')
                ->body('Une erreur est survenue lors de la création de la visite : ' . $e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
