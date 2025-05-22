<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanAbonnementResource\Pages;
use App\Models\PlanAbonnement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Policies\PlanAbonnementPolicy;

class PlanAbonnementResource extends Resource
{
    protected static ?string $model = PlanAbonnement::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Abonnements';
    protected static ?int $navigationSort = 2;
    
    protected static string $policy = PlanAbonnementPolicy::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du plan')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Basic, Pro, Entreprise'),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Description détaillée du plan'),
                        Forms\Components\TextInput::make('prix_mensuel')
                            ->numeric()
                            ->required()
                            ->prefix('XOF')
                            ->placeholder('99.99')
                            ->label('Prix mensuel'),
                        Forms\Components\TextInput::make('prix_annuel')
                            ->numeric()
                            ->required()
                            ->prefix('XOF')
                            ->placeholder('999.99')
                            ->label('Prix annuel'),
                        Forms\Components\Select::make('periode_facturation')
                            ->options([
                                'mensuel' => 'Mensuel',
                                'trimestriel' => 'Trimestriel',
                                'semestriel' => 'Semestriel',
                                'annuel' => 'Annuel',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\ColorPicker::make('couleur')
                            ->hex()
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Limites et Fonctionnalités')
                    ->schema([
                        Forms\Components\TextInput::make('nombre_employes_max')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->placeholder('Nombre maximum d\'employés'),
                        Forms\Components\TextInput::make('nombre_departements_max')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->placeholder('Nombre maximum de départements'),
                        Forms\Components\TextInput::make('nombre_filiales_max')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->placeholder('Nombre maximum de filiales'),
                        Forms\Components\Toggle::make('rapports_avances')
                            ->required()
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger'),
                        Forms\Components\Toggle::make('support_prioritaire')
                            ->required()
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger'),
                        Forms\Components\Toggle::make('api_access')
                            ->required()
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger')
                            ->label('Accès API'),
                    ])->columns(2),
                Forms\Components\Section::make('Fonctionnalités Avancées')
                    ->schema([
                        Forms\Components\CheckboxList::make('fonctionnalites')
                            ->options([
                                'export_donnees' => 'Export des données',
                                'import_masse' => 'Import en masse',
                                'personnalisation' => 'Personnalisation avancée',
                                'integration_api' => 'Intégration API',
                                'support_24_7' => 'Support 24/7',
                                'backup_donnees' => 'Backup des données',
                                'multi_langue' => 'Multi-langue',
                                'statistiques_avancees' => 'Statistiques avancées',
                            ])
                            ->columns(2),
                        Forms\Components\RichEditor::make('conditions_speciales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                            ])
                            ->placeholder('Conditions spéciales pour ce plan...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): string => \Str::limit($record->description, 50)),
                Tables\Columns\TextColumn::make('prix_mensuel')
                    ->money('EUR')
                    ->sortable()
                    ->label('Prix mensuel'),
                Tables\Columns\TextColumn::make('prix_annuel')
                    ->money('EUR')
                    ->sortable()
                    ->label('Prix annuel'),
                Tables\Columns\TextColumn::make('periode_facturation')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mensuel' => 'info',
                        'trimestriel' => 'warning',
                        'semestriel' => 'success',
                        'annuel' => 'primary',
                    }),
                Tables\Columns\TextColumn::make('nombre_employes_max')
                    ->numeric()
                    ->sortable()
                    ->label('Max Employés'),
                Tables\Columns\IconColumn::make('rapports_avances')
                    ->boolean()
                    ->label('Rapports'),
                Tables\Columns\IconColumn::make('support_prioritaire')
                    ->boolean()
                    ->label('Support'),
                Tables\Columns\IconColumn::make('api_access')
                    ->boolean()
                    ->label('API'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periode_facturation')
                    ->options([
                        'mensuel' => 'Mensuel',
                        'trimestriel' => 'Trimestriel',
                        'semestriel' => 'Semestriel',
                        'annuel' => 'Annuel',
                    ]),
                Tables\Filters\TernaryFilter::make('rapports_avances')
                    ->label('Rapports avancés'),
                Tables\Filters\TernaryFilter::make('support_prioritaire')
                    ->label('Support prioritaire'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(fn ($record) => $record->replicate()->save())
                    ->authorize('duplicate'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanAbonnements::route('/'),
            'create' => Pages\CreatePlanAbonnement::route('/create'),
            'edit' => Pages\EditPlanAbonnement::route('/{record}/edit'),
        ];
    }
}
