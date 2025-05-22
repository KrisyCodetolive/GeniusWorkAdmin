<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RapportResource\Pages;
use App\Models\Rapport;
use App\Services\RapportFinancierService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

class RapportResource extends Resource
{
    protected static ?string $model = Rapport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Rapports';
    protected static bool $shouldRegisterNavigation = false;


    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'titre';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du rapport')
                    ->schema([
                        Forms\Components\TextInput::make('titre')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['default' => 2, 'md' => 2]),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->columnSpan(['default' => 2, 'md' => 2]),

                        Forms\Components\Select::make('type')
                            ->label('Type de rapport')
                            ->options([
                                'financier' => 'Rapport financier',
                                'presence' => 'Rapport de présence',
                                'performance' => 'Rapport de performance',
                                'retard_absence' => 'Rapport de retards et absences',
                                'heures_supplementaires' => 'Rapport d\'heures supplémentaires',
                                'conge' => 'Rapport de congés',
                                'personnalise' => 'Rapport personnalisé',
                            ])
                            ->required()
                            ->live()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\Select::make('format')
                            ->label('Format')
                            ->options([
                                'pdf' => 'PDF',
                                'excel' => 'Excel',
                                'csv' => 'CSV',
                                'json' => 'JSON',
                                'html' => 'HTML',
                            ])
                            ->required()
                            ->columnSpan(['default' => 1, 'md' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Période')
                    ->schema([
                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date de début')
                            ->required()
                            ->default(now()->startOfMonth())
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date de fin')
                            ->required()
                            ->default(now())
                            ->after('date_debut')
                            ->columnSpan(['default' => 1, 'md' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Filtres')
                    ->schema([
                        Forms\Components\Select::make('employeur_id')
                            ->label('Employeur')
                            ->relationship('employeur', 'nom')
                            ->searchable()
                            ->preload()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\Select::make('departement_id')
                            ->label('Département')
                            ->relationship('departement', 'nom')
                            ->searchable()
                            ->preload()
                            ->visible(fn (callable $get) => in_array($get('type'), ['presence', 'performance', 'retard_absence', 'heures_supplementaires', 'conge']))
                            ->columnSpan(['default' => 1, 'md' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Paramètres avancés')
                    ->schema([
                        Forms\Components\KeyValue::make('parametres')
                            ->label('Paramètres')
                            ->keyLabel('Paramètre')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter un paramètre')
                            ->columnSpan(['default' => 2, 'md' => 2]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'financier',
                        'success' => 'presence',
                        'warning' => 'performance',
                        'danger' => 'retard_absence',
                        'info' => 'heures_supplementaires',
                        'secondary' => 'conge',
                        'gray' => 'personnalise',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'financier' => 'Financier',
                        'presence' => 'Présence',
                        'performance' => 'Performance',
                        'retard_absence' => 'Retards & Absences',
                        'heures_supplementaires' => 'Heures supp.',
                        'conge' => 'Congés',
                        'personnalise' => 'Personnalisé',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('format')
                    ->label('Format')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_debut')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employeur')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('departement.nom')
                    ->label('Département')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('createur.name')
                    ->label('Créé par')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type de rapport')
                    ->options([
                        'financier' => 'Rapport financier',
                        'presence' => 'Rapport de présence',
                        'performance' => 'Rapport de performance',
                        'retard_absence' => 'Rapport de retards et absences',
                        'heures_supplementaires' => 'Rapport d\'heures supplémentaires',
                        'conge' => 'Rapport de congés',
                        'personnalise' => 'Rapport personnalisé',
                    ]),
                Tables\Filters\SelectFilter::make('format')
                    ->label('Format')
                    ->options([
                        'pdf' => 'PDF',
                        'excel' => 'Excel',
                        'csv' => 'CSV',
                        'json' => 'JSON',
                        'html' => 'HTML',
                    ]),
                Tables\Filters\SelectFilter::make('employeur_id')
                    ->relationship('employeur', 'nom')
                    ->label('Employeur')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('debut')
                            ->label('Début'),
                        Forms\Components\DatePicker::make('fin')
                            ->label('Fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['debut'],
                                fn (Builder $query, $date): Builder => $query->where('date_debut', '>=', $date),
                            )
                            ->when(
                                $data['fin'],
                                fn (Builder $query, $date): Builder => $query->where('date_fin', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('telecharger')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Rapport $record): string => route('rapports.telecharger', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('regenerer')
                    ->label('Régénérer')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (Rapport $record) {
                        // Logique pour régénérer le rapport
                        if ($record->type === 'financier') {
                            $service = app(RapportFinancierService::class);
                            
                            if ($record->employeur_id) {
                                $entreprise = \App\Models\Entreprise::find($record->employeur_id);
                                $rapport = $service->genererRapportFinancier($entreprise, [
                                    'date_debut' => $record->date_debut,
                                    'date_fin' => $record->date_fin,
                                ]);
                            } else {
                                $rapport = $service->genererRapportGlobal([
                                    'date_debut' => $record->date_debut,
                                    'date_fin' => $record->date_fin,
                                ]);
                            }
                            
                            // Mettre à jour les paramètres avec les nouvelles données
                            $record->update([
                                'parametres' => $rapport,
                                'updated_at' => now(),
                            ]);
                        }
                        
                        return redirect()->back()->with('success', 'Rapport régénéré avec succès');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListRapports::route('/'),
            'create' => Pages\CreateRapport::route('/create'),
            'view' => Pages\ViewRapport::route('/{record}'),
            'edit' => Pages\EditRapport::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
