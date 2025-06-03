<?php

namespace App\Filament\Resources\FilialeResource\Pages;

use App\Filament\Resources\FilialeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Facades\Log;

class CreateFiliale extends CreateRecord
{
    use HasWizard;
    
    protected static string $resource = FilialeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSteps(): array
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return [
            Step::make('Informations de base')
                ->icon('heroicon-o-information-circle')
                ->description('Informations générales de la filiale')
                ->schema([
                    Section::make('Informations générales')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('entreprise_id')
                                        ->label('Entreprise')
                                        ->relationship('entreprise', 'nom')
                                        ->required()
                                        ->searchable()
                                        ->visible($isSuperAdminOrSupport),
                                    TextInput::make('nom')
                                        ->label('Nom de la filiale')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('code')
                                        ->label('Code')
                                        ->helperText('Laisser vide pour une génération automatique')
                                        ->maxLength(50),
                                    Select::make('statut')
                                        ->label('Statut')
                                        ->options([
                                            'actif' => 'Actif',
                                            'inactif' => 'Inactif',
                                        ])
                                        ->default('actif')
                                        ->required(),
                                ]),
                            Textarea::make('description')
                                ->label('Description')
                                ->maxLength(500)
                                ->rows(3),
                        ]),
                ]),
                
            Step::make('Coordonnées')
                ->icon('heroicon-o-map-pin')
                ->description('Adresse et coordonnées de la filiale')
                ->schema([
                    Section::make('Adresse et contact')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('adresse')
                                        ->label('Adresse')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('ville')
                                        ->label('Ville')
                                        ->required()
                                        ->maxLength(100),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('pays')
                                        ->label('Pays')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('telephone')
                                        ->label('Téléphone')
                                        ->tel()
                                        ->required()
                                        ->maxLength(20),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('site_web')
                                        ->label('Site web')
                                        ->url()
                                        ->maxLength(255)
                                        ->placeholder('exemple.com')
                                        ->helperText('Exemple: exemple.com'),
                                ]),
                        ]),
                ]),
                
            Step::make('Site')
                ->icon('heroicon-o-building-office-2')
                ->description('Gestion du site associé à la filiale')
                ->schema([
                    Section::make('Gestion du site')
                        ->description('Sélectionnez un site existant ou créez-en un nouveau pour cette filiale')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('site_id')
                                        ->label('Site existant')
                                        ->options(function () {
                                            $entrepriseId = auth()->user->entreprise_id;
                                            if (!$entrepriseId) {
                                                return [];
                                            }
                                            return Site::where('entreprise_id', $entrepriseId)
                                                ->pluck('nom', 'id')
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, callable $set) => $state ? $set('create_new_site', false) : null),
                                    
                                    Toggle::make('create_new_site')
                                        ->label('Créer un nouveau site')
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, callable $set) => $state ? $set('site_id', null) : null),
                                ]),
                        ]),
                        
                    Section::make('Informations du nouveau site')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('nouveau_site_nom')
                                        ->label('Nom du site')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('nouveau_site_adresse')
                                        ->label('Adresse')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('nouveau_site_code_postal')
                                        ->label('Code postal')
                                        ->maxLength(20),
                                    TextInput::make('nouveau_site_ville')
                                        ->label('Ville')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('nouveau_site_pays')
                                        ->label('Pays')
                                        ->required()
                                        ->maxLength(100),
                                ]),
                            Grid::make(1)
                                ->schema([
                                    Toggle::make('nouveau_site_has_geofencing')
                                        ->label('Activer le geofencing')
                                        ->reactive(),
                                ]),
                            Section::make('Paramètres de geofencing')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('nouveau_site_latitude')
                                                ->label('Latitude')
                                                ->numeric()
                                                ->required(),
                                            TextInput::make('nouveau_site_longitude')
                                                ->label('Longitude')
                                                ->numeric()
                                                ->required(),
                                            TextInput::make('nouveau_site_rayon_geofencing')
                                                ->label('Rayon (mètres)')
                                                ->numeric()
                                                ->default(100)
                                                ->required(),
                                        ]),
                                ])
                                ->visible(fn (callable $get) => $get('nouveau_site_has_geofencing')),
                        ])
                        ->visible(fn (callable $get) => $get('create_new_site')),
                ]),
                
            Step::make('Autres')
                ->icon('heroicon-o-cog')
                ->description('Logo et configuration')
                ->schema([
                    Section::make('Logo et configuration')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    FileUpload::make('logo')
                                        ->label('Logo de la filiale')
                                        ->image()
                                        ->disk('public')
                                        ->directory('filiales/logos')
                                        ->visibility('public')
                                        ->maxSize(2048)
                                        ->imageResizeMode('cover')
                                        ->imageCropAspectRatio('1:1')
                                        ->imageResizeTargetWidth('200')
                                        ->imageResizeTargetHeight('200'),
                                    Textarea::make('configuration')
                                        ->label('Configuration (JSON)')
                                        ->helperText('Configuration au format JSON')
                                        ->json()
                                        ->rows(4),
                                ]),
                        ]),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            $user = auth()->user();
            
            // Si l'utilisateur n'est pas SuperAdmin ou Support, on utilise son entreprise_id
            if (!$user->isSuperAdmin() && !$user->isSupport()) {
                $data['entreprise_id'] = $user->entreprise_id;
            }
            
            // Générer un code automatiquement si non fourni
            if (empty($data['code']) && !empty($data['nom']) && !empty($data['entreprise_id'])) {
                $data['code'] = \App\Models\Filiale::genererCode($data['nom'], $data['entreprise_id']);
            }
            
            // Création d'un nouveau site si demandé
            if (isset($data['create_new_site']) && $data['create_new_site']) {
                // Créer un nouveau site
                $site = Site::create([
                    'entreprise_id' => $data['entreprise_id'],
                    'nom' => $data['nouveau_site_nom'],
                    'adresse' => $data['nouveau_site_adresse'],
                    'code_postal' => $data['nouveau_site_code_postal'] ?? null,
                    'ville' => $data['nouveau_site_ville'],
                    'pays' => $data['nouveau_site_pays'],
                    'latitude' => $data['nouveau_site_latitude'] ?? null,
                    'longitude' => $data['nouveau_site_longitude'] ?? null,
                    'has_geofencing' => $data['nouveau_site_has_geofencing'] ?? false,
                    'rayon_geofencing' => $data['nouveau_site_rayon_geofencing'] ?? 100,
                    'statut' => 'actif',
                ]);
                
                // Assigner l'ID du site nouvellement créé
                $data['site_id'] = $site->id;
                
                Notification::make()
                    ->title('Site créé avec succès')
                    ->body('Le site ' . $site->nom . ' a été créé et associé à cette filiale.')
                    ->success()
                    ->send();
            }
            
            // Supprimer les champs temporaires du formulaire
            unset($data['create_new_site']);
            unset($data['nouveau_site_nom']);
            unset($data['nouveau_site_adresse']);
            unset($data['nouveau_site_code_postal']);
            unset($data['nouveau_site_ville']);
            unset($data['nouveau_site_pays']);
            unset($data['nouveau_site_latitude']);
            unset($data['nouveau_site_longitude']);
            unset($data['nouveau_site_has_geofencing']);
            unset($data['nouveau_site_rayon_geofencing']);
            
            return $data;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du site: ' . $e->getMessage());
            
            Notification::make()
                ->title('Erreur lors de la création du site')
                ->body('Une erreur est survenue: ' . $e->getMessage())
                ->danger()
                ->send();
                
            throw $e;
        }
    }
    
    public function create(bool $another = false): void
    {
        try {
            parent::create($another);
            
            Notification::make()
                ->title('Filiale créée avec succès')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la filiale: ' . $e->getMessage());
            
            Notification::make()
                ->title('Erreur lors de la création de la filiale')
                ->body('Une erreur est survenue: ' . $e->getMessage())
                ->danger()
                ->send();
                
            throw $e;
        }
    }
}
