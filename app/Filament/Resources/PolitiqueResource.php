<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PolitiqueResource\Pages;
use App\Models\Politique;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;

class PolitiqueResource extends Resource
{
    use HasEntrepriseScope;
    
    protected static ?string $model = Politique::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Politiques RH');
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $form
            ->schema([
                Tabs::make('Politique')
                    ->tabs([
                        Tabs\Tab::make('Informations générales')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Select::make('entreprise_id')
                                    ->relationship('entreprise', 'nom')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->visible($isSuperAdminOrSupport)
                                    ->columnSpanFull(),
                                
                                Section::make('Notifications')
                                    ->schema([
                                        Toggle::make('notifier_utilisateurs')
                                            ->label('Notifier les utilisateurs')
                                            ->helperText('Envoyer des notifications aux utilisateurs pour les pointages, retards, etc.')
                                            ->default(true),
                                    ]),
                                
                                Section::make('Nombre de pointages')
                                    ->schema([
                                        TextInput::make('nombre_pointages_par_jour')
                                            ->label('Nombre de pointages par jour')
                                            ->numeric()
                                            ->minValue(2)
                                            ->maxValue(10)
                                            ->default(2)
                                            ->helperText('Minimum 2 (entrée/sortie). Augmentez pour gérer les pauses.'),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Présences')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Section::make('Tolérances')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('tolerance_retard')
                                            ->label('Tolérance retard (minutes)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(60)
                                            ->default(10),
                                        
                                        TextInput::make('tolerance_depart_anticipe')
                                            ->label('Tolérance départ anticipé (minutes)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(60)
                                            ->default(5),
                                    ]),
                                
                                Section::make('Options de présence')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('annuler_horaires_si_sortie_manquee')
                                            ->label('Annuler horaires si sortie manquée')
                                            ->helperText('Ne pas comptabiliser les heures si l\'employé n\'a pas pointé sa sortie')
                                            ->default(true),
                                        
                                        Toggle::make('activer_pauses')
                                            ->label('Activer les pauses')
                                            ->helperText('Permettre la gestion des pauses')
                                            ->default(true),
                                    ]),
                                
                                Section::make('Règles de présence')
                                    ->schema([
                                        Forms\Components\KeyValue::make('regles_presence')
                                            ->label('Règles de présence')
                                            ->keyLabel('Règle')
                                            ->valueLabel('Configuration')
                                            ->keyPlaceholder('Nom de la règle')
                                            ->valuePlaceholder('Valeur de configuration')
                                            ->addable()
                                            ->deletable(),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Heures supplémentaires')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Section::make('Options des heures supplémentaires')
                                    ->schema([
                                        Toggle::make('activer_heures_supplementaires')
                                            ->label('Activer les heures supplémentaires')
                                            ->helperText('Permettre l\'enregistrement des heures supplémentaires')
                                            ->default(true),
                                        
                                        Toggle::make('autoriser_travail_weekend')
                                            ->label('Autoriser le travail le weekend')
                                            ->helperText('Permettre aux employés de travailler le weekend')
                                            ->default(false),
                                    ]),
                                
                                Section::make('Règles des heures supplémentaires')
                                    ->schema([
                                        Forms\Components\KeyValue::make('regles_supplementaires')
                                            ->label('Règles des heures supplémentaires')
                                            ->keyLabel('Règle')
                                            ->valueLabel('Configuration')
                                            ->keyPlaceholder('Nom de la règle')
                                            ->valuePlaceholder('Valeur de configuration')
                                            ->addable()
                                            ->deletable(),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Congés')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Section::make('Règles des congés')
                                    ->schema([
                                        Forms\Components\KeyValue::make('regles_conges')
                                            ->label('Règles des congés')
                                            ->keyLabel('Règle')
                                            ->valueLabel('Configuration')
                                            ->keyPlaceholder('Nom de la règle')
                                            ->valuePlaceholder('Valeur de configuration')
                                            ->addable()
                                            ->deletable(),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Flexibilité')
                            ->icon('heroicon-o-arrows-right-left')
                            ->schema([
                                Section::make('Options de flexibilité')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('autoriser_permutations')
                                            ->label('Autoriser les permutations')
                                            ->helperText('Permettre aux employés d\'échanger leurs horaires')
                                            ->default(true),
                                        
                                        Toggle::make('autoriser_recuperations')
                                            ->label('Autoriser les récupérations')
                                            ->helperText('Permettre aux employés de récupérer des heures de travail')
                                            ->default(true),
                                    ]),
                            ]),
                        
                        Tabs\Tab::make('Configuration avancée')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Configuration personnalisée')
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
        return $table
            ->columns([
                TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                
                IconColumn::make('notifier_utilisateurs')
                    ->label('Notifications')
                    ->boolean(),
                
                TextColumn::make('tolerance_retard')
                    ->label('Tolérance retard')
                    ->suffix(' min'),
                
                TextColumn::make('tolerance_depart_anticipe')
                    ->label('Tolérance départ anticipé')
                    ->suffix(' min'),
                
                IconColumn::make('autoriser_permutations')
                    ->label('Permutations')
                    ->boolean(),
                
                IconColumn::make('autoriser_recuperations')
                    ->label('Récupérations')
                    ->boolean(),
                
                IconColumn::make('activer_heures_supplementaires')
                    ->label('Heures supp.')
                    ->boolean(),
                
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
                    ->visible(fn () => auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                
                Tables\Filters\TernaryFilter::make('autoriser_permutations')
                    ->label('Permutations autorisées'),
                
                Tables\Filters\TernaryFilter::make('autoriser_recuperations')
                    ->label('Récupérations autorisées'),
                
                Tables\Filters\TernaryFilter::make('activer_heures_supplementaires')
                    ->label('Heures supplémentaires activées'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPolitiques::route('/'),
            'create' => Pages\CreatePolitique::route('/create'),
            'edit' => Pages\EditPolitique::route('/{record}/edit'),
            'view' => Pages\ViewPolitique::route('/{record}'),
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
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return static::mutateFormDataWithEntreprise($data);
    }
}
