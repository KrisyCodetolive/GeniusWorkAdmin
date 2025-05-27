<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartementResource\Pages;
use App\Filament\Resources\DepartementResource\RelationManagers;
use App\Filament\Resources\DepartementResource\Widgets\DepartementStatsWidget;
use App\Filament\Resources\DepartementResource\Widgets\DepartementHierarchieWidget;
use App\Filament\Resources\DepartementResource\Widgets\DepartementFilialeWidget;
use App\Models\Departement;
use App\Models\Entreprise;
use App\Models\Filiale;
use App\Models\Employeur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class DepartementResource extends Resource
{
    protected static ?string $model = Departement::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Structure Organisationnelle';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'nom';

    public static function getNavigationLabel(): string
    {
        return __('Départements');
    }

    public static function getNavigationBadge(): ?string
    {
        return Departement::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Card::make()
                            ->schema([
                                Select::make('entreprise_id')
                                    ->label('Entreprise')
                                    ->options(Entreprise::pluck('nom', 'id'))
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('filiale_id', null))
                                    ->visible($isSuperAdminOrSupport),
                                
                                Select::make('filiale_id')
                                    ->label('Filiale')
                                    ->options(function (callable $get) {
                                        $entrepriseId = $get('entreprise_id');
                                        if (!$entrepriseId && !auth()->user()->isSuperAdmin() && !auth()->user()->isSupport()) {
                                            $entrepriseId = auth()->user()->entreprise_id;
                                        }
                                        if (!$entrepriseId) {
                                            return [];
                                        }
                                        return Filiale::where('entreprise_id', $entrepriseId)
                                            ->pluck('nom', 'id');
                                    })
                                    ->required(),
                                
                                TextInput::make('nom')
                                    ->required()
                                    ->maxLength(255),
                                
                                TextInput::make('code')
                                    ->maxLength(50)
                                    ->helperText('Laissez vide pour générer automatiquement'),
                                
                                Textarea::make('description')
                                    ->maxLength(500)
                                    ->columnSpan(2),
                            ])
                            ->columnSpan(2),
                        
                        Card::make()
                            ->schema([
                                Select::make('responsable_id')
                                    ->label('Responsable')
                                    ->options(function (callable $get) {
                                        $entrepriseId = $get('entreprise_id');
                                        if (!$entrepriseId) {
                                            return [];
                                        }
                                        return Employeur::where('entreprise_id', $entrepriseId)
                                            ->get()
                                            ->mapWithKeys(function ($employeur) {
                                                return [$employeur->id => $employeur->prenom . ' ' . $employeur->nom];
                                            });
                                    })
                                    ->searchable(),
                                
                                Select::make('parent_id')
                                    ->label('Département parent')
                                    ->options(function (callable $get, ?Departement $record) {
                                        $filialeId = $get('filiale_id');
                                        if (!$filialeId) {
                                            return [];
                                        }
                                        
                                        $query = Departement::where('filiale_id', $filialeId);
                                        
                                        // Exclure le département actuel et ses descendants
                                        if ($record) {
                                            $descendants = $record->getTousLesSousDepartements()->pluck('id')->toArray();
                                            $descendants[] = $record->id;
                                            $query->whereNotIn('id', $descendants);
                                        }
                                        
                                        return $query->pluck('nom', 'id');
                                    })
                                    ->searchable(),
                                
                                TextInput::make('niveau')
                                    ->label('Niveau hiérarchique')
                                    ->disabled()
                                    ->helperText('Calculé automatiquement'),
                                
                                Select::make('statut')
                                    ->options([
                                        Departement::STATUT_ACTIF => 'Actif',
                                        Departement::STATUT_INACTIF => 'Inactif',
                                    ])
                                    ->default(Departement::STATUT_ACTIF)
                                    ->required(),
                                
                                KeyValue::make('configuration')
                                    ->keyLabel('Paramètre')
                                    ->valueLabel('Valeur')
                                    ->reorderable()
                                    ->default([
                                        'limite_employes' => '0',
                                        'budget' => '0',
                                    ])
                                    ->columnSpan(2),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('nom')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('filiale.nom')
                    ->label('Filiale')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('niveau')
                    ->label('Niveau')
                    ->sortable(),
                
                TextColumn::make('responsable.nom_complet')
                    ->label('Responsable')
                    ->getStateUsing(fn (Departement $record) => $record->responsable ? $record->responsable->prenom . ' ' . $record->responsable->nom : null)
                    ->searchable(),
                
                TextColumn::make('getCheminComplet')
                    ->label('Hiérarchie')
                    ->getStateUsing(fn (Departement $record) => $record->getCheminComplet())
                    ->searchable(false)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('employeurs_count')
                    ->label('Employés')
                    ->counts('employeurs')
                    ->sortable(),
                
                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Departement::STATUT_ACTIF => 'success',
                        Departement::STATUT_INACTIF => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->options([
                        Departement::STATUT_ACTIF => 'Actif',
                        Departement::STATUT_INACTIF => 'Inactif',
                    ]),
                
                SelectFilter::make('filiale_id')
                    ->label('Filiale')
                    ->options(fn () => Filiale::pluck('nom', 'id')),
                
                SelectFilter::make('niveau')
                    ->label('Niveau hiérarchique')
                    ->options(fn () => Departement::distinct()->pluck('niveau', 'niveau')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('arborescence')
                    ->label('Voir l\'arborescence')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (Departement $record) => route('filament.admin.resources.departements.arborescence', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('genererExemples')
                    ->label('Générer des exemples')
                    ->icon('heroicon-o-building-office-2')
                    ->visible(fn () => auth()->user()->entreprise_id && !auth()->user()->isSuperAdmin() && !auth()->user()->isSupport())
                    ->action(function () {
                        $user = auth()->user();
                        $entreprise = $user->entreprise;
                        
                        if (!$entreprise) {
                            Filament\Notifications\Notification::make()
                                ->title('Erreur')
                                ->body('Vous devez être associé à une entreprise pour générer des exemples de départements.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Vérifier si l'entreprise a au moins une filiale
                        $filiale = $entreprise->filiales()->first();
                        
                        if (!$filiale) {
                            Filament\Notifications\Notification::make()
                                ->title('Erreur')
                                ->body('Vous devez avoir au moins une filiale pour générer des exemples de départements.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Générer les exemples de départements
                        $result = \Database\Seeders\DepartementExempleSeeder::createForFiliale($filiale);
                        
                        // Notification de succès
                        Filament\Notifications\Notification::make()
                            ->title('Exemples générés')
                            ->body(count($result['created']) . ' départements exemples ont été créés pour votre filiale "' . $filiale->nom . '".' . 
                                   ($result['existants'] > 0 ? ' ' . $result['existants'] . ' départements existaient déjà.' : ''))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Générer des exemples de départements')
                    ->modalDescription('Cette action va créer des départements exemples pour la première filiale de votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                    ->modalSubmitActionLabel('Générer'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmployeursRelationManager::class,
            RelationManagers\SousDepartementsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            DepartementStatsWidget::class,
            DepartementHierarchieWidget::class,
            DepartementFilialeWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartements::route('/'),
            'create' => Pages\CreateDepartement::route('/create'),
            'view' => Pages\ViewDepartement::route('/{record}'),
            'edit' => Pages\EditDepartement::route('/{record}/edit'),
            'arborescence' => Pages\ArborescenceDepartement::route('/{record}/arborescence'),
            'stats' => Pages\StatsDepartements::route('/stats'),
        ];
    }
}
