<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeCongeResource\Pages;
use App\Filament\Resources\TypeCongeResource\RelationManagers;
use App\Models\TypeConge;
use App\Models\Entreprise;
use App\Traits\HasEntrepriseScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;

class TypeCongeResource extends Resource
{
    use HasEntrepriseScope;
    
    protected static ?string $model = TypeConge::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    // Caché dans le menu de navigation mais accessible via CongeResource
    protected static bool $shouldRegisterNavigation = false;
    
    protected static ?string $navigationLabel = 'Types de congés';
    
    protected static ?string $recordTitleAttribute = 'nom';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

     
    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->description('Informations de base du type de congé')
                    ->schema([
                        Forms\Components\Select::make('entreprise_id')
                            ->label('Entreprise')
                            ->relationship('entreprise', 'nom')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nom')
                                    ->label('Nom de l\'entreprise')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->required()
                            ->visible($isSuperAdminOrSupport)
                            ->default(fn () => $isSuperAdminOrSupport ? null : $user->entreprise_id),
                        Forms\Components\TextInput::make('nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('statut')
                            ->options([
                                'actif' => 'Actif',
                                'inactif' => 'Inactif',
                            ])
                            ->default('actif')
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Paramètres')
                    ->description('Configuration des règles du type de congé')
                    ->schema([
                        Forms\Components\TextInput::make('duree_max_annuelle')
                            ->label('Durée maximale annuelle (jours)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.5)
                            ->default(null)
                            ->helperText('Laisser vide si pas de limite'),
                        Forms\Components\TextInput::make('delai_demande_prealable')
                            ->label('Délai de demande préalable (jours)')
                            ->helperText('Nombre de jours minimum avant la date de début du congé')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\Toggle::make('necessite_justificatif')
                            ->label('Nécessite un justificatif')
                            ->helperText('Un document justificatif doit être fourni avec la demande')
                            ->required(),
                        Forms\Components\Toggle::make('est_paye')
                            ->label('Congé payé')
                            ->helperText('L\'employé est rémunéré pendant ce type de congé')
                            ->required(),
                        Forms\Components\Toggle::make('deductible_solde')
                            ->label('Déductible du solde')
                            ->helperText('Ce type de congé est déduit du solde de congés de l\'employé')
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Configuration avancée')
                    ->description('Paramètres avancés et conditions d\'éligibilité')
                    ->schema([
                        Forms\Components\KeyValue::make('conditions_eligibilite')
                            ->label('Conditions d\'éligibilité')
                            ->helperText('Définir les conditions requises pour bénéficier de ce type de congé')
                            ->keyLabel('Condition')
                            ->valueLabel('Valeur')
                            ->reorderable(),
                        Forms\Components\KeyValue::make('configuration')
                            ->label('Configuration supplémentaire')
                            ->helperText('Paramètres additionnels pour ce type de congé')
                            ->keyLabel('Paramètre')
                            ->valueLabel('Valeur')
                            ->reorderable(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable()
                    ->visible($isSuperAdminOrSupport),
                Tables\Columns\TextColumn::make('duree_max_annuelle')
                    ->label('Durée max. (jours)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('necessite_justificatif')
                    ->label('Justificatif')
                    ->boolean(),
                Tables\Columns\IconColumn::make('est_paye')
                    ->label('Payé')
                    ->boolean(),
                Tables\Columns\IconColumn::make('deductible_solde')
                    ->label('Déductible')
                    ->boolean(),
                Tables\Columns\TextColumn::make('delai_demande_prealable')
                    ->label('Délai (jours)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->relationship('entreprise', 'nom')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ]),
                Tables\Filters\TernaryFilter::make('est_paye')
                    ->label('Congé payé'),
                Tables\Filters\TernaryFilter::make('deductible_solde')
                    ->label('Déductible du solde'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Dupliquer')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (TypeConge $record) {
                        $newRecord = $record->replicate();
                        $newRecord->nom = $record->nom . ' (copie)';
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('changeStatus')
                        ->label('Changer le statut')
                        ->icon('heroicon-o-arrow-path')
                        ->deselectRecordsAfterCompletion()
                        ->form([
                            Forms\Components\Select::make('statut')
                                ->label('Nouveau statut')
                                ->options([
                                    'actif' => 'Actif',
                                    'inactif' => 'Inactif',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['statut' => $data['statut']]);
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('genererExemples')
                    ->label('Générer des exemples')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->action(function () {
                        $user = auth()->user();
                        $entreprise = $user->entreprise;
                        
                        // Vérifier que l'utilisateur a une entreprise associée
                        if (!$entreprise) {
                            Filament\Notifications\Notification::make()
                                ->title('Erreur')
                                ->body('Vous devez être associé à une entreprise pour générer des exemples de types de congés.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Générer les exemples de types de congés
                        $result = \Database\Seeders\TypeCongeExempleSeeder::createForEntreprise($entreprise);
                        
                        // Notification de succès
                        Filament\Notifications\Notification::make()
                            ->title('Exemples générés')
                            ->body(count($result['created']) . ' types de congés exemples ont été créés pour votre entreprise.' . 
                                   ($result['existants'] > 0 ? ' ' . $result['existants'] . ' types de congés existaient déjà.' : ''))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Générer des exemples de types de congés')
                    ->modalDescription('Cette action va créer des exemples de types de congés pour votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                    ->modalSubmitActionLabel('Générer')
                    ->visible(function () {
                        $user = auth()->user();
                        // Visible seulement pour les utilisateurs avec une entreprise et qui ne sont pas SuperAdmin ou Support
                        return $user->entreprise_id && !$user->isSuperAdmin() && !$user->isSupport();
                    }),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\CongesRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypeConges::route('/'),
            'create' => Pages\CreateTypeConge::route('/create'),
            'edit' => Pages\EditTypeConge::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        return static::scopeForCurrentEntreprise($query);
    }
}
