<?php

namespace App\Filament\Resources\PlageHoraireBaseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Employeur;
use App\Models\Jour;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class JoursRelationManager extends RelationManager
{
    protected static string $relationship = 'jours';

    protected static ?string $recordTitleAttribute = 'jour_semaine';

    protected static ?string $title = 'Jours assignés';

    public function form(Form $form): Form
    {
        $user = auth()->user();
        $isAdminOrHigher = $user->isSuperAdmin() || $user->isSupport();

        return $form
            ->schema([
                Forms\Components\Select::make('employeur_id')
                    ->label('Employé')
                    ->options(function () use ($isAdminOrHigher, $user) {
                        $query = Employeur::query();
                        
                        if (!$isAdminOrHigher) {
                            $query->where('entreprise_id', $user->entreprise_id);
                        } else {
                            $plageHoraire = $this->getOwnerRecord();
                            if ($plageHoraire && $plageHoraire->entreprise_id) {
                                $query->where('entreprise_id', $plageHoraire->entreprise_id);
                            }
                        }
                        
                        return $query->pluck('nom', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('jour_semaine')
                    ->label('Jour de la semaine')
                    ->options([
                        'Lundi' => 'Lundi',
                        'Mardi' => 'Mardi',
                        'Mercredi' => 'Mercredi',
                        'Jeudi' => 'Jeudi',
                        'Vendredi' => 'Vendredi',
                        'Samedi' => 'Samedi',
                        'Dimanche' => 'Dimanche',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('est_travaille')
                    ->label('Est travaillé')
                    ->default(true),
                Forms\Components\Textarea::make('commentaire')
                    ->label('Commentaire')
                    ->maxLength(1000),
            ]);
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $isAdminOrHigher = $user->isSuperAdmin() || $user->isSupport();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employé')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jour_semaine')
                    ->label('Jour de la semaine')
                    ->formatStateUsing(fn ($state) => $state ?? '-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('est_travaille')
                    ->label('Travaillé')
                    ->boolean(),
                Tables\Columns\TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employeur_id')
                    ->label('Employé')
                    ->options(function () use ($isAdminOrHigher, $user) {
                        $query = Employeur::query();
                        
                        if (!$isAdminOrHigher) {
                            $query->where('entreprise_id', $user->entreprise_id);
                        } else {
                            $plageHoraire = $this->getOwnerRecord();
                            if ($plageHoraire && $plageHoraire->entreprise_id) {
                                $query->where('entreprise_id', $plageHoraire->entreprise_id);
                            }
                        }
                        
                        return $query->pluck('nom', 'id');
                    })
                    ->searchable(),
                SelectFilter::make('jour_semaine')
                    ->label('Jour de la semaine')
                    ->options([
                        'Lundi' => 'Lundi',
                        'Mardi' => 'Mardi',
                        'Mercredi' => 'Mercredi',
                        'Jeudi' => 'Jeudi',
                        'Vendredi' => 'Vendredi',
                        'Samedi' => 'Samedi',
                        'Dimanche' => 'Dimanche',
                    ]),
                TernaryFilter::make('est_travaille')
                    ->label('Travaillé'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
