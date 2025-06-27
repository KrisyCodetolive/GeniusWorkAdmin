<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipeResource\Pages;
use App\Filament\Resources\EquipeResource\RelationManagers;
use App\Models\Equipe;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\PlageHoraire;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Carbon\Carbon;

class EquipeResource extends Resource
{
    protected static ?string $model = Equipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'Structure Organisationnelle';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nom';
    
    protected static ?string $modelLabel = 'Équipe';
    
    protected static ?string $pluralModelLabel = 'Équipes';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isAdminOrHigher = $user->isSuperAdmin() || $user->isSupport();

        return $form
            ->schema([
                Section::make('Informations de base')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('entreprise_id')
                                    ->label('Entreprise')
                                    ->options(Entreprise::pluck('nom', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->visible($isAdminOrHigher)
                                    ->default(fn () => $user->isSuperAdmin() || $user->isSupport() ? null : $user->entreprise_id),
                                TextInput::make('nom')
                                    ->label('Nom de l\'équipe')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('responsable_id')
                                    ->label('Responsable')
                                    ->options(function () use ($user, $isAdminOrHigher) {
                                        $query = Employeur::query();
                                        if (!$isAdminOrHigher) {
                                            $query->where('entreprise_id', $user->entreprise_id);
                                        }
                                        return $query->pluck('nom', 'id');
                                    })
                                    ->searchable(),
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
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
                Section::make('Configuration avancée')
                    ->schema([
                        KeyValue::make('configuration')
                            ->label('Configuration')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isAdminOrHigher = $user->isSuperAdmin() || $user->isSupport();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable()
                    ->visible($isAdminOrHigher),
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsable.nom')
                    ->label('Responsable')
                    ->formatStateUsing(function ($record) {
                        if (!$record->responsable) return null;
                        return "{$record->responsable->prenom} {$record->responsable->nom}" . 
                            ($record->responsable->matricule ? " ({$record->responsable->matricule})" : '');
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('membres_count')
                    ->label('Membres')
                    ->counts('membres')
                    ->sortable(),
                Tables\Columns\TextColumn::make('plages_horaires_count')
                    ->label('Plages horaires')
                    ->counts('plagesHoraires')
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'primary',
                        'inactif' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->options(Entreprise::pluck('nom', 'id'))
                    ->searchable()
                    ->visible($isAdminOrHigher),
                SelectFilter::make('responsable_id')
                    ->label('Responsable')
                    ->options(function () use ($user, $isAdminOrHigher) {
                        $query = Employeur::query();
                        if (!$isAdminOrHigher) {
                            $query->where('entreprise_id', $user->entreprise_id);
                        }
                        return $query->pluck('nom', 'id');
                    })
                    ->searchable(),
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('synchroniserHoraires')
                    ->label('Synchroniser les horaires')
                    ->icon('heroicon-o-clock')
                    ->action(function (Equipe $record) {
                        $record->synchroniserHoraires();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Horaires synchronisés')
                            ->body('Les horaires ont été synchronisés pour tous les membres de l\'équipe.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Synchroniser les horaires')
                    ->modalDescription('Cette action va appliquer les plages horaires de l\'équipe à tous ses membres actifs.')
                    ->modalSubmitActionLabel('Synchroniser')
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('synchroniserHoraires')
                        ->label('Synchroniser les horaires')
                        ->icon('heroicon-o-clock')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->synchroniserHoraires();
                                $count++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Horaires synchronisés')
                                ->body("Les horaires ont été synchronisés pour {$count} équipe(s).")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Synchroniser les horaires')
                        ->modalDescription('Cette action va appliquer les plages horaires des équipes sélectionnées à tous leurs membres actifs.')
                        ->modalSubmitActionLabel('Synchroniser')
                        ->color('success'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
              
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MembresRelationManager::class,
            RelationManagers\PlagesHorairesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipes::route('/'),
            'create' => Pages\CreateEquipe::route('/create'),
            'edit' => Pages\EditEquipe::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
               // SoftDeletingScope::class,
            ]);
    }
}
