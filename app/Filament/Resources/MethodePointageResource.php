<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MethodePointageResource\Pages;
use App\Filament\Resources\MethodePointageResource\RelationManagers;
use App\Models\MethodePointage;
use App\Traits\HasEntrepriseScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Collection;

class MethodePointageResource extends Resource
{
    use HasEntrepriseScope;
    
    protected static ?string $model = MethodePointage::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 4;

    

    public static function getNavigationLabel(): string
    {
        return __('Méthodes de Pointage');
    }

    public static function getNavigationBadge(): ?string
    {
        return MethodePointage::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
    
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->isSuperAdmin() || $user->isSupport() ;
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Informations de base')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('entreprise_id')
                                        ->relationship('entreprise', 'nom')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->visible($isSuperAdminOrSupport),
                                    
                                    TextInput::make('nom')
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('code')
                                        ->required()
                                        ->maxLength(50)
                                        ->unique(ignoreRecord: true),
                                    
                                    Select::make('statut')
                                        ->options([
                                            'actif' => 'Actif',
                                            'inactif' => 'Inactif',
                                        ])
                                        ->default('actif')
                                        ->required(),
                                ]),
                            
                            Textarea::make('description')
                                ->maxLength(1000)
                                ->columnSpanFull(),
                        ]),
                    
                    Wizard\Step::make('Exigences de pointage')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
                            Section::make('Méthodes de vérification')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('necessite_photo')
                                                ->label('Photo requise')
                                                ->helperText('Exiger une photo lors du pointage')
                                                ->default(false),
                                            
                                            Toggle::make('necessite_signature')
                                                ->label('Signature requise')
                                                ->helperText('Exiger une signature lors du pointage')
                                                ->default(false),
                                        ]),
                                    
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('necessite_geolocalisation')
                                                ->label('Géolocalisation requise')
                                                ->helperText('Exiger les coordonnées GPS lors du pointage')
                                                ->default(true),
                                            
                                            Toggle::make('necessite_validation')
                                                ->label('Validation requise')
                                                ->helperText('Exiger une validation par un superviseur')
                                                ->default(false),
                                        ]),
                                ]),
                            
                            Section::make('Paramètres de géolocalisation')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('autoriser_hors_site')
                                                ->label('Autoriser pointage hors site')
                                                ->helperText('Permettre le pointage même si l\'employé est hors du rayon du site')
                                                ->default(false),
                                            
                                            TextInput::make('rayon_geofencing')
                                                ->label('Rayon de géofencing (mètres)')
                                                ->numeric()
                                                ->default(100)
                                                ->minValue(10)
                                                ->maxValue(5000),
                                        ]),
                                ]),
                        ]),
                    
                    Wizard\Step::make('Configuration avancée')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Section::make('Règles de validation')
                                ->schema([
                                    Forms\Components\KeyValue::make('validation_regles')
                                        ->label('Règles de validation')
                                        ->keyLabel('Règle')
                                        ->valueLabel('Configuration')
                                        ->keyPlaceholder('Nom de la règle')
                                        ->valuePlaceholder('Valeur de configuration')
                                        ->addable()
                                        ->deletable(),
                                ]),
                            
                            Section::make('Configuration supplémentaire')
                                ->schema([
                                    Forms\Components\KeyValue::make('configuration')
                                        ->label('Configuration personnalisée')
                                        ->keyLabel('Paramètre')
                                        ->valueLabel('Valeur')
                                        ->keyPlaceholder('Nom du paramètre')
                                        ->valuePlaceholder('Valeur du paramètre')
                                        ->addable()
                                        ->deletable(),
                                ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $table
            ->columns([
                TextColumn::make('nom')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                
                IconColumn::make('necessite_photo')
                    ->label('Photo')
                    ->boolean(),
                
                IconColumn::make('necessite_geolocalisation')
                    ->label('GPS')
                    ->boolean(),
                
                IconColumn::make('necessite_signature')
                    ->label('Signature')
                    ->boolean(),
                
                IconColumn::make('necessite_validation')
                    ->label('Validation')
                    ->boolean(),
                
                TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->relationship('entreprise', 'nom')
                    ->searchable()
                    ->preload()
                    ->visible($isSuperAdminOrSupport),
                
                SelectFilter::make('statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ]),
                
                TernaryFilter::make('necessite_photo')
                    ->label('Photo requise'),
                
                TernaryFilter::make('necessite_geolocalisation')
                    ->label('Géolocalisation requise'),
                
                TernaryFilter::make('necessite_signature')
                    ->label('Signature requise'),
                
                TernaryFilter::make('necessite_validation')
                    ->label('Validation requise'),
                
                TernaryFilter::make('autoriser_hors_site')
                    ->label('Pointage hors site'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activer')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['statut' => 'actif']);
                            }
                        }),
                    Tables\Actions\BulkAction::make('desactiver')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['statut' => 'inactif']);
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMethodePointages::route('/'),
            'create' => Pages\CreateMethodePointage::route('/create'),
            'edit' => Pages\EditMethodePointage::route('/{record}/edit'),
            'view' => Pages\ViewMethodePointage::route('/{record}'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = auth()->user();
        
        // Si l'utilisateur n'est ni SuperAdmin ni Support, filtrer par entreprise
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        return $query;
    }
    
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, utiliser son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        return $data;
    }
}
