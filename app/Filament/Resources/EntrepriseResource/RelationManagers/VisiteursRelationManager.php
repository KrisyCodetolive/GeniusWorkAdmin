<?php

namespace App\Filament\Resources\EntrepriseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Visiteur;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\VisiteResource;

class VisiteursRelationManager extends RelationManager
{
    protected static string $relationship = 'visiteurs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('prenom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('telephone')
                    ->required()
                    ->maxLength(20)
                    ->unique(Visiteur::class, 'telephone', fn ($record) => $record)
                    ->unique(table: Visiteur::class, column: 'telephone', ignoreRecord: true, callback: function (TextInput $component, $query) {
                        return $query->where('entreprise_id', $this->getOwnerRecord()->id);
                    }),
                TextInput::make('email')
                    ->email()
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('organisation')
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('fonction')
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('code_visiteur')
                    ->nullable()
                    ->maxLength(50)
                    ->default(fn () => 'V-' . strtoupper(substr(uniqid(), -6))),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom_complet')
            ->columns([
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom complet')
                    ->searchable(['nom', 'prenom']),
                Tables\Columns\TextColumn::make('telephone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('organisation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fonction')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code_visiteur')
                    ->label('Code visiteur'),
                Tables\Columns\TextColumn::make('visites_count')
                    ->label('Nombre de visites')
                    ->counts('visites'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date de création')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('nouvelle_visite')
                    ->label('Nouvelle visite')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->url(fn (Visiteur $record): string => VisiteResource::getUrl('createFromVisiteur', ['visiteur_id' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
