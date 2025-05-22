<?php

namespace App\Filament\Resources\DepartementResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Departement;
use App\Models\Employeur;

class SousDepartementsRelationManager extends RelationManager
{
    protected static string $relationship = 'sousDepartements';

    protected static ?string $recordTitleAttribute = 'nom';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->maxLength(50)
                    ->helperText('Laissez vide pour générer automatiquement'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(500),
                Forms\Components\Select::make('responsable_id')
                    ->label('Responsable')
                    ->options(function (RelationManager $livewire) {
                        $entrepriseId = $livewire->ownerRecord->entreprise_id;
                        return Employeur::where('entreprise_id', $entrepriseId)
                            ->pluck('nom_complet', 'id');
                    })
                    ->searchable(),
                Forms\Components\Select::make('statut')
                    ->options([
                        Departement::STATUT_ACTIF => 'Actif',
                        Departement::STATUT_INACTIF => 'Inactif',
                    ])
                    ->default(Departement::STATUT_ACTIF)
                    ->required(),
                Forms\Components\KeyValue::make('configuration')
                    ->keyLabel('Paramètre')
                    ->valueLabel('Valeur')
                    ->default([
                        'limite_employes' => '0',
                        'budget' => '0',
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('niveau')
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsable.nom_complet')
                    ->label('Responsable'),
                Tables\Columns\TextColumn::make('employeurs_count')
                    ->label('Employés')
                    ->counts('employeurs'),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Departement::STATUT_ACTIF => 'success',
                        Departement::STATUT_INACTIF => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        Departement::STATUT_ACTIF => 'Actif',
                        Departement::STATUT_INACTIF => 'Inactif',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, RelationManager $livewire): Departement {
                        $parentDept = $livewire->ownerRecord;
                        
                        // Préparer les données pour le nouveau sous-département
                        $data['entreprise_id'] = $parentDept->entreprise_id;
                        $data['filiale_id'] = $parentDept->filiale_id;
                        $data['parent_id'] = $parentDept->id;
                        $data['niveau'] = $parentDept->niveau + 1;
                        
                        // Générer un code si non fourni
                        if (empty($data['code'])) {
                            $data['code'] = Departement::genererCode($data['nom'], $parentDept->filiale_id);
                        }
                        
                        return Departement::create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($action, $record) {
                        // Vérifier si le département peut être supprimé
                        if (!$record->peutEtreSupprime()) {
                            $action->cancel();
                            $action->failureNotification()?->title('Impossible de supprimer ce département')
                                ->body('Ce département contient des employés ou des sous-départements. Veuillez les réaffecter avant de supprimer ce département.');
                        }
                    }),
                Tables\Actions\Action::make('arborescence')
                    ->label('Voir l\'arborescence')
                    ->icon('heroicon-o-chart-tree')
                    ->url(fn (Departement $record) => route('filament.admin.resources.departements.arborescence', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($action, $records) {
                            foreach ($records as $record) {
                                if (!$record->peutEtreSupprime()) {
                                    $action->cancel();
                                    $action->failureNotification()?->title('Impossible de supprimer certains départements')
                                        ->body('Certains départements contiennent des employés ou des sous-départements. Veuillez les réaffecter avant de supprimer ces départements.');
                                    break;
                                }
                            }
                        }),
                ]),
            ]);
    }
}
