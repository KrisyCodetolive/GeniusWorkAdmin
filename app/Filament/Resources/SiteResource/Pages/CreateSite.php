<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
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
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class CreateSite extends CreateRecord
{
    use HasWizard;
    
    protected static string $resource = SiteResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on utilise son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        return $data;
    }
    
    protected function getSteps(): array
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return [
            Step::make('Informations de base')
                ->icon('heroicon-o-information-circle')
                ->description('Informations générales du site')
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
                                        ->label('Nom du site')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    Select::make('statut')
                                        ->label('Statut')
                                        ->options([
                                            'actif' => 'Actif',
                                            'inactif' => 'Inactif',
                                        ])
                                        ->default('actif')
                                        ->required(),
                                    TextInput::make('description')
                                        ->label('Description')
                                        ->maxLength(500),
                                ]),
                        ]),
                ]),
                
            Step::make('Coordonnées')
                ->icon('heroicon-o-map-pin')
                ->description('Adresse et coordonnées du site')
                ->schema([
                    Section::make('Adresse')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('adresse')
                                        ->label('Adresse')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('code_postal')
                                        ->label('Code postal')
                                        ->maxLength(20),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('ville')
                                        ->label('Ville')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('pays')
                                        ->label('Pays')
                                        ->required()
                                        ->maxLength(100),
                                ]),
                        ]),
                        
                    Section::make('Contact')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('contact_nom')
                                        ->label('Nom du contact')
                                        ->maxLength(255),
                                    TextInput::make('contact_email')
                                        ->label('Email du contact')
                                        ->email()
                                        ->maxLength(255),
                                    TextInput::make('contact_telephone')
                                        ->label('Téléphone du contact')
                                        ->tel()
                                        ->maxLength(20),
                                ]),
                        ]),
                ]),
                
            Step::make('Geofencing')
                ->icon('heroicon-o-map')
                ->description('Paramètres de géolocalisation')
                ->schema([
                    Section::make('Paramètres de geofencing')
                        ->schema([
                            Grid::make(1)
                                ->schema([
                                    Toggle::make('has_geofencing')
                                        ->label('Activer le geofencing')
                                        ->helperText('Permet de définir une zone géographique pour ce site')
                                        ->default(false)
                                        ->reactive(),
                                ]),
                                
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('latitude')
                                        ->label('Latitude')
                                        ->numeric()
                                        ->required()
                                        ->helperText('Ex: 48.8566')
                                        ->placeholder('48.8566'),
                                    TextInput::make('longitude')
                                        ->label('Longitude')
                                        ->numeric()
                                        ->required()
                                        ->helperText('Ex: 2.3522')
                                        ->placeholder('2.3522'),
                                    TextInput::make('rayon_geofencing')
                                        ->label('Rayon (mètres)')
                                        ->numeric()
                                        ->default(100)
                                        ->required()
                                        ->helperText('Rayon de la zone en mètres'),
                                ])
                                ->visible(fn (callable $get) => $get('has_geofencing')),
                                
                            Section::make('Aide')
                                ->schema([
                                    Textarea::make('geofencing_help')
                                        ->label('Comment obtenir les coordonnées GPS')
                                        ->default('1. Allez sur Google Maps
2. Faites un clic droit sur l\'emplacement du site
3. Sélectionnez "Plus d\'infos sur cet endroit"
4. Les coordonnées apparaîtront en bas de l\'écran (ex: 48.8566, 2.3522)
5. Copiez ces valeurs dans les champs latitude et longitude')
                                        ->disabled()
                                        ->rows(6),
                                ])
                                ->visible(fn (callable $get) => $get('has_geofencing')),
                        ]),
                ]),
                
            Step::make('Horaires')
                ->icon('heroicon-o-clock')
                ->description('Horaires d\'ouverture du site')
                ->schema([
                    Section::make('Horaires d\'ouverture')
                        ->schema([
                            KeyValue::make('horaires')
                                ->label('Horaires d\'ouverture')
                                ->keyLabel('Jour')
                                ->valueLabel('Heures (ex: 09:00-12:00, 14:00-18:00)')
                                ->keyPlaceholder('Ex: Lundi')
                                ->valuePlaceholder('Ex: 09:00-12:00, 14:00-18:00')
                                ->addable()
                                ->reorderable()
                                ->default([
                                    'Lundi' => '09:00-12:00, 14:00-18:00',
                                    'Mardi' => '09:00-12:00, 14:00-18:00',
                                    'Mercredi' => '09:00-12:00, 14:00-18:00',
                                    'Jeudi' => '09:00-12:00, 14:00-18:00',
                                    'Vendredi' => '09:00-12:00, 14:00-18:00',
                                    'Samedi' => 'Fermé',
                                    'Dimanche' => 'Fermé',
                                ]),
                        ]),
                ]),
        ];
    }
}
