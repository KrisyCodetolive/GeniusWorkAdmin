<?php

namespace App\Filament\Resources\FilialeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Employeur;
use App\Models\Filiale;
use Illuminate\Support\Carbon;

class ResponsablesRelationManager extends RelationManager
{
    protected static string $relationship = 'responsables';

    protected static ?string $recordTitleAttribute = 'nom';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employeur_id')
                    ->label('Employé')
                    ->options(function (RelationManager $livewire) {
                        $filiale = $livewire->getOwnerRecord();
                        return Employeur::where('entreprise_id', $filiale->entreprise_id)
                            ->where('statut', 'actif')
                            ->get()
                            ->pluck('nom_complet', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('role')
                    ->label('Rôle')
                    ->options([
                        Filiale::ROLE_RESPONSABLE_PRINCIPAL => 'Responsable Principal',
                        Filiale::ROLE_RESPONSABLE_ADJOINT => 'Responsable Adjoint',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Rôle')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Filiale::ROLE_RESPONSABLE_PRINCIPAL => 'success',
                        Filiale::ROLE_RESPONSABLE_ADJOINT => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Filiale::ROLE_RESPONSABLE_PRINCIPAL => 'Responsable Principal',
                        Filiale::ROLE_RESPONSABLE_ADJOINT => 'Responsable Adjoint',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('pivot.date_debut')
                    ->label('Date de début')
                    ->date(),
                Tables\Columns\TextColumn::make('pivot.date_fin')
                    ->label('Date de fin')
                    ->date()
                    ->placeholder('En cours'),
                Tables\Columns\IconColumn::make('actif')
                    ->label('Actif')
                    ->boolean()
                    ->getStateUsing(function ($record): bool {
                        return $record->pivot->date_fin === null || Carbon::parse($record->pivot->date_fin)->isFuture();
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        Filiale::ROLE_RESPONSABLE_PRINCIPAL => 'Responsable Principal',
                        Filiale::ROLE_RESPONSABLE_ADJOINT => 'Responsable Adjoint',
                    ]),
                Tables\Filters\Filter::make('actifs')
                    ->label('Responsables actifs')
                    ->query(fn (Builder $query): Builder => $query->where(function ($query) {
                        $query->whereNull('filiale_responsables.date_fin')
                            ->orWhere('filiale_responsables.date_fin', '>=', now());
                    })),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, RelationManager $livewire): Employeur {
                        $filiale = $livewire->getOwnerRecord();
                        
                        // Si c'est un responsable principal, terminer le mandat du responsable principal actuel
                        if ($data['role'] === Filiale::ROLE_RESPONSABLE_PRINCIPAL) {
                            $filiale->terminerMandatResponsablePrincipal();
                        }
                        
                        $employeur = Employeur::find($data['employeur_id']);
                        
                        // Attacher l'employeur comme responsable
                        $filiale->responsables()->attach($employeur->id, [
                            'role' => $data['role'],
                            'date_debut' => $data['date_debut'],
                            'date_fin' => $data['date_fin'],
                        ]);
                        
                        return $employeur;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Employeur $record, array $data, RelationManager $livewire): Employeur {
                        $filiale = $livewire->getOwnerRecord();
                        
                        // Si le rôle est changé en responsable principal, terminer le mandat du responsable principal actuel
                        if ($data['role'] === Filiale::ROLE_RESPONSABLE_PRINCIPAL && $record->pivot->role !== Filiale::ROLE_RESPONSABLE_PRINCIPAL) {
                            $filiale->terminerMandatResponsablePrincipal();
                        }
                        
                        // Mettre à jour la relation pivot
                        $filiale->responsables()->updateExistingPivot($record->id, [
                            'role' => $data['role'],
                            'date_debut' => $data['date_debut'],
                            'date_fin' => $data['date_fin'],
                        ]);
                        
                        return $record;
                    }),
                Tables\Actions\Action::make('terminer')
                    ->label('Terminer le mandat')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Employeur $record): bool => $record->pivot->date_fin === null)
                    ->action(function (Employeur $record, RelationManager $livewire): void {
                        $filiale = $livewire->getOwnerRecord();
                        $filiale->terminerMandat($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
