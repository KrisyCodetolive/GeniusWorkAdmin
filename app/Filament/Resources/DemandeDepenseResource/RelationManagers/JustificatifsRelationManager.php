<?php

namespace App\Filament\Resources\DemandeDepenseResource\RelationManagers;

use App\Filament\Resources\DemandeDepenseResource;
use App\Models\JustificatifDepense;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Justificatifs d'une demande : ajout et suppression tant que la demande est un brouillon
 * (y compris depuis la page de détail), consultation seule ensuite.
 */
class JustificatifsRelationManager extends RelationManager
{
    protected static string $relationship = 'justificatifs';

    protected static ?string $title = 'Justificatifs';

    protected static ?string $icon = 'heroicon-o-paper-clip';

    protected static ?string $modelLabel = 'justificatif';

    protected static ?string $pluralModelLabel = 'justificatifs';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->justificatifs()->count() ?: null;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(DemandeDepenseResource::champsJustificatif())
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom_original')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => JustificatifDepense::TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('nom_original')
                    ->label('Fichier')
                    ->state(fn (JustificatifDepense $record) => $record->nom_original ?: basename($record->fichier))
                    ->icon('heroicon-o-document')
                    ->url(fn (JustificatifDepense $record) => route('depenses.justificatif', $record))
                    ->openUrlInNewTab()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y à H:i'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter un justificatif'),
            ])
            ->actions([
                Tables\Actions\Action::make('ouvrir')
                    ->label('Ouvrir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (JustificatifDepense $record) => route('depenses.justificatif', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Aucun justificatif')
            ->emptyStateDescription('Ajoutez les devis, factures ou reçus qui justifient la dépense.')
            ->paginated(false);
    }

    protected function peutModifier(): bool
    {
        return auth()->user()->can('update', $this->getOwnerRecord());
    }

    protected function canCreate(): bool
    {
        return $this->peutModifier();
    }

    protected function canEdit(Model $record): bool
    {
        return false;
    }

    protected function canDelete(Model $record): bool
    {
        return $this->peutModifier();
    }

    protected function canDeleteAny(): bool
    {
        return false;
    }
}
