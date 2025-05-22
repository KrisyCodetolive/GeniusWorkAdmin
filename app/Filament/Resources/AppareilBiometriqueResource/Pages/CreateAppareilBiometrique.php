<?php

namespace App\Filament\Resources\AppareilBiometriqueResource\Pages;

use App\Filament\Resources\AppareilBiometriqueResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\KeyValue;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use App\Services\Biometrique\AppareilBiometriqueService;
use Filament\Notifications\Notification;

class CreateAppareilBiometrique extends CreateRecord
{
    use HasWizard;
    
    protected static string $resource = AppareilBiometriqueResource::class;
    
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
                ->description('Informations générales de l\'appareil')
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
                                        ->reactive()
                                        ->afterStateUpdated(fn (callable $set) => $set('site_id', null))
                                        ->visible($isSuperAdminOrSupport),
                                    Select::make('site_id')
                                        ->label('Site')
                                        ->relationship('site', 'nom', function ($query, callable $get) use ($user, $isSuperAdminOrSupport) {
                                            if ($isSuperAdminOrSupport) {
                                                $entrepriseId = $get('entreprise_id');
                                                if ($entrepriseId) {
                                                    return $query->where('entreprise_id', $entrepriseId);
                                                }
                                            } else {
                                                // Pour les autres utilisateurs, filtrer par leur entreprise
                                                return $query->where('entreprise_id', $user->entreprise_id);
                                            }
                                            return $query;
                                        })
                                        ->searchable()
                                        ->preload(),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('nom')
                                        ->label('Nom de l\'appareil')
                                        ->required()
                                        ->maxLength(255),
                                    Select::make('statut')
                                        ->label('Statut')
                                        ->options([
                                            'actif' => 'Actif',
                                            'inactif' => 'Inactif',
                                            'maintenance' => 'Maintenance',
                                            'erreur' => 'Erreur',
                                        ])
                                        ->default('actif')
                                        ->required(),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('modele')
                                        ->label('Modèle')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('fabricant')
                                        ->label('Fabricant')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('numero_serie')
                                        ->label('Numéro de série')
                                        ->maxLength(255),
                                ]),
                        ]),
                ]),
                
            Step::make('Configuration réseau')
                ->icon('heroicon-o-server')
                ->description('Paramètres de connexion')
                ->schema([
                    Section::make('Paramètres réseau')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('adresse_ip')
                                        ->label('Adresse IP')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('port')
                                        ->label('Port')
                                        ->numeric()
                                        ->default(4370)
                                        ->required(),
                                    Select::make('protocole')
                                        ->label('Protocole')
                                        ->options([
                                            'TCP' => 'TCP',
                                            'UDP' => 'UDP',
                                            'HTTP' => 'HTTP',
                                            'HTTPS' => 'HTTPS',
                                        ])
                                        ->default('TCP')
                                        ->required(),
                                ]),
                        ]),
                        
                    Section::make('Authentification')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('identifiant_connexion')
                                        ->label('Identifiant')
                                        ->maxLength(255),
                                    TextInput::make('mot_de_passe')
                                        ->label('Mot de passe')
                                        ->password()
                                        ->maxLength(255),
                                    TextInput::make('cle_api')
                                        ->label('Clé API')
                                        ->maxLength(255),
                                ]),
                        ]),
                ]),
                
            Step::make('Capacités')
                ->icon('heroicon-o-cpu-chip')
                ->description('Capacités de l\'appareil')
                ->schema([
                    Section::make('Spécifications')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('version_firmware')
                                        ->label('Version du firmware')
                                        ->maxLength(255),
                                    Select::make('type_authentification')
                                        ->label('Type d\'authentification')
                                        ->options([
                                            'empreinte' => 'Empreinte',
                                            'visage' => 'Visage',
                                            'carte' => 'Carte',
                                            'code' => 'Code',
                                            'multiple' => 'Multiple',
                                        ])
                                        ->default('multiple')
                                        ->required(),
                                ]),
                                
                            Grid::make(4)
                                ->schema([
                                    TextInput::make('capacite_empreintes')
                                        ->label('Capacité empreintes')
                                        ->numeric()
                                        ->placeholder('3000'),
                                    TextInput::make('capacite_visages')
                                        ->label('Capacité visages')
                                        ->numeric()
                                        ->placeholder('1000'),
                                    TextInput::make('capacite_cartes')
                                        ->label('Capacité cartes')
                                        ->numeric()
                                        ->placeholder('5000'),
                                    TextInput::make('capacite_logs')
                                        ->label('Capacité logs')
                                        ->numeric()
                                        ->placeholder('100000'),
                                ]),
                        ]),
                        
                    Section::make('Options disponibles')
                        ->schema([
                            KeyValue::make('options_disponibles')
                                ->label('Options disponibles')
                                ->keyLabel('Option')
                                ->valueLabel('Disponible')
                                ->keyPlaceholder('Ex: empreinte')
                                ->valuePlaceholder('true/false')
                                ->addable()
                                ->default([
                                    'empreinte' => 'true',
                                    'visage' => 'false',
                                    'carte' => 'true',
                                    'code' => 'true',
                                ])
                                ->rules([
                                    function () {
                                        return function (string $attribute, $value, \Closure $fail) {
                                            foreach ($value as $key => $val) {
                                                if (!in_array(strtolower($val), ['true', 'false'])) {
                                                    $fail("La valeur pour {$key} doit être 'true' ou 'false'");
                                                }
                                            }
                                        };
                                    },
                                ]),
                        ]),
                ]),
                
            Step::make('Synchronisation')
                ->icon('heroicon-o-arrow-path')
                ->description('Paramètres de synchronisation')
                ->schema([
                    Section::make('Synchronisation automatique')
                        ->schema([
                            Grid::make(1)
                                ->schema([
                                    Toggle::make('sync_auto_enabled')
                                        ->label('Activer la synchronisation automatique')
                                        ->helperText('Permet de synchroniser automatiquement les données de l\'appareil')
                                        ->default(false)
                                        ->reactive(),
                                ]),
                                
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('sync_logs_interval')
                                        ->label('Intervalle logs (minutes)')
                                        ->numeric()
                                        ->default(60)
                                        ->required()
                                        ->helperText('Intervalle de synchronisation des logs en minutes'),
                                    TextInput::make('sync_users_interval')
                                        ->label('Intervalle utilisateurs (minutes)')
                                        ->numeric()
                                        ->default(1440)
                                        ->required()
                                        ->helperText('Intervalle de synchronisation des utilisateurs en minutes'),
                                    TextInput::make('sync_time_interval')
                                        ->label('Intervalle horloge (minutes)')
                                        ->numeric()
                                        ->default(1440)
                                        ->required()
                                        ->helperText('Intervalle de synchronisation de l\'horloge en minutes'),
                                ])
                                ->visible(fn (callable $get) => $get('sync_auto_enabled')),
                                
                            KeyValue::make('sync_options')
                                ->label('Options de synchronisation')
                                ->keyLabel('Option')
                                ->valueLabel('Valeur')
                                ->addable()
                                ->visible(fn (callable $get) => $get('sync_auto_enabled')),
                        ]),
                ]),
                
            Step::make('Paramètres avancés')
                ->icon('heroicon-o-adjustments-horizontal')
                ->description('Paramètres avancés de l\'appareil')
                ->schema([
                    Section::make('Configuration avancée')
                        ->schema([
                            KeyValue::make('parametres_avances')
                                ->label('Paramètres avancés')
                                ->keyLabel('Paramètre')
                                ->valueLabel('Valeur')
                                ->addable(),
                                
                            Textarea::make('notes')
                                ->label('Notes')
                                ->rows(5)
                                ->maxLength(1000),
                        ]),
                ]),
        ];
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, utiliser son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Tester la connexion après la création
        $record = $this->record;
        $appareilService = app(AppareilBiometriqueService::class);
        
        try {
            $success = $appareilService->testerConnexion($record);
            
            if ($success) {
                Notification::make()
                    ->title('Connexion réussie à l\'appareil')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Impossible de se connecter à l\'appareil')
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Appareil biométrique créé')
                ->body('Impossible de tester la connexion: ' . $e->getMessage())
                ->warning()
                ->send();
        }
    }
}
