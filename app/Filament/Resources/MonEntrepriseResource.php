<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonEntrepriseResource\Pages;
use App\Filament\Resources\MonEntrepriseResource\RelationManagers;
use App\Models\Entreprise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class MonEntrepriseResource extends Resource
{
    protected static ?string $model = Entreprise::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Mon Entreprise';
    protected static ?string $modelLabel = 'Mon Entreprise';
    protected static ?string $pluralModelLabel = 'Mon Entreprise';
    protected static ?string $navigationGroup = 'Mon Compte';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'mon-entreprise';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        // Pour les SuperAdmin et Support, montrer toutes les entreprises
        if ($user && $user->hasRole(['SuperAdmin', 'Support'])) {
            return parent::getEloquentQuery();
        }
        
        // Pour les autres utilisateurs, montrer uniquement leur entreprise
        return parent::getEloquentQuery()
            ->where('id', $user->entreprise_id ?? 0); // Utiliser 0 comme valeur par défaut si entreprise_id est null
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->can('viewOwn', Entreprise::class);
    }

    public static function canCreate(): bool
    {
        // Seuls les SuperAdmin et Support peuvent créer des entreprises depuis cette ressource
        $user = Auth::user();
        return $user && $user->can('create', Entreprise::class);
    }

    public static function canDelete(Model $record): bool
    {
        // Seuls les SuperAdmin et Support peuvent supprimer des entreprises
        $user = Auth::user();
        return $user && $user->can('delete', $record);
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user && $user->can('updateOwn', $record);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations générales')
                    ->description('Informations principales de votre entreprise')
                    ->schema([
                        TextInput::make('nom')
                            ->label('Nom de l\'entreprise')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('code')
                            ->label('Code entreprise')
                            ->disabled(function () {
                                $user = Auth::user();
                                return !$user || !$user->hasRole(['SuperAdmin', 'Support']);
                            })
                            ->dehydrated(function ($state) {
                                $user = Auth::user();
                                return $user && $user->hasRole(['SuperAdmin', 'Support']);
                            })
                            ->maxLength(50),
                        
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('telephone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),
                        
                        TextInput::make('site_web')
                            ->label('Site web')
                            ->url()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-globe-alt'),
                        // Nombre d'employés
                        TextInput::make('nombre_employes')
                            ->label('Nombre d\'employés')
                            ->numeric()
                            ->minValue(1)
                            ->disabled(),    
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('logos')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Section::make('Informations légales')
                    ->description('Informations légales et fiscales')
                    ->schema([
                        TextInput::make('secteur_activite')
                            ->label('Secteur d\'activité')
                            ->maxLength(255),
                        
                        TextInput::make('raison_sociale')
                            ->label('Raison sociale')
                            ->maxLength(255),
                        
                        TextInput::make('rccm')
                            ->label('RCCM')
                            ->maxLength(50),
                        
                        TextInput::make('nif')
                            ->label('NIF')
                            ->maxLength(50),
                            
                        TextInput::make('configuration.numero_contribuable')
                            ->label('Numéro Contribuable')
                            ->maxLength(50),
                            
                        TextInput::make('configuration.cnps')
                            ->label('CNPS')
                            ->maxLength(50),
                    ])
                    ->columns(2),
                
                Section::make('Adresse')
                    ->description('Adresse physique de votre entreprise')
                    ->schema([
                        TextInput::make('adresse')
                            ->label('Adresse')
                            ->maxLength(255),
                        
                        TextInput::make('code_postal')
                            ->label('Code postal')
                            ->maxLength(20),
                        
                        TextInput::make('ville')
                            ->label('Ville')
                            ->maxLength(100),
                        
                        TextInput::make('pays')
                            ->label('Pays')
                            ->maxLength(100)
                            ->default('France'),
                    ])
                    ->columns(2),
                
                Section::make('Paramètres')
                    ->description('Paramètres de fonctionnement')
                    ->schema([
                        TextInput::make('devise')
                            ->label('Devise')
                            ->maxLength(10)
                            ->default('FCFA'),
                        
                        TextInput::make('fuseau_horaire')
                            ->label('Fuseau horaire')
                            ->maxLength(50)
                            ->default('UTC+1'),
                        
                        Select::make('langue')
                            ->label('Langue')
                            ->options([
                                'fr' => 'Français',
                                'en' => 'Anglais',
                                'es' => 'Espagnol',
                            ])
                            ->default('fr'),
                    ])
                    ->columns(3),
                
                // Section visible uniquement pour les SuperAdmin et Support
                Section::make('Administration')
                    ->description('Paramètres administratifs (réservés aux administrateurs)')
                    ->schema([
                        Select::make('statut')
                            ->label('Statut')
                            ->options([
                                'actif' => 'Actif',
                                'inactif' => 'Inactif',
                                'suspendu' => 'Suspendu',
                            ])
                            ->default('actif')
                            ->required(),
                    ])
                    ->columns(2)
                    ->visible(fn () => Auth::user() && Auth::user()->hasRole(['SuperAdmin', 'Support'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),
                
                TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                
                TextColumn::make('telephone')
                    ->label('Téléphone'),
                
                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        'suspendu' => 'warning',
                        default => 'gray',
                    }),
                
                TextColumn::make('abonnements.plan.nom')
                    ->label('Abonnement actif')
                    ->getStateUsing(function (Entreprise $record) {
                        $abonnement = $record->abonnements()
                            ->where('statut', 'actif')
                            ->latest()
                            ->first();
                        
                        return $abonnement ? $abonnement->plan->nom : 'Aucun';
                    }),
                
                TextColumn::make('abonnement_expiration')
                    ->label('Expiration')
                    ->getStateUsing(function (Entreprise $record) {
                        $abonnement = $record->abonnements()
                            ->where('statut', 'actif')
                            ->latest()
                            ->first();
                        
                        return $abonnement ? $abonnement->date_fin->format('d/m/Y') : '-';
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Actions en masse uniquement pour SuperAdmin et Support
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
                ->visible(fn () => Auth::user() && Auth::user()->hasRole(['SuperAdmin', 'Support'])),
            ]);
    }

    public static function getRelations(): array
    {
        return [
           // RelationManagers\VisiteursRelationManager::class,
           // RelationManagers\VisitesRelationManager::class,
        ];
    }

    //Widgets
    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\EntrepriseStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonEntreprise::route('/'),
            'view' => Pages\ViewMonEntreprise::route('/{record}'),
            'edit' => Pages\EditMonEntreprise::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        // Afficher dans la navigation si l'utilisateur a une entreprise ou est SuperAdmin/Support
        return $user && ($user->entreprise_id || $user->hasRole(['SuperAdmin', 'Support']));
    }
}
