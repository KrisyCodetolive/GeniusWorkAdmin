<?php

namespace App\Filament\Resources\DemandeDepenseResource\RelationManagers;

use App\Models\ValidationDepense;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Journal des étapes de la demande, en lecture seule : il n'est jamais modifié ni supprimé.
 */
class ValidationsRelationManager extends RelationManager
{
    protected static string $relationship = 'validations';

    protected static ?string $title = 'Historique et signatures';

    protected static ?string $icon = 'heroicon-o-clock';

    protected static ?string $modelLabel = 'étape';

    protected static ?string $pluralModelLabel = 'étapes';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->validations()->count() ?: null;
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->description("Chaque étape est enregistrée et ne peut plus être modifiée. L'empreinte vérifie que la demande n'a pas changé depuis la signature.")
            ->columns([
                Tables\Columns\TextColumn::make('etape')
                    ->label('Étape')
                    ->formatStateUsing(fn (string $state) => ValidationDepense::ETAPES[$state] ?? $state)
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('decision')
                    ->label('Décision')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ValidationDepense::DECISIONS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ValidationDepense::DECISION_APPROUVE => 'success',
                        ValidationDepense::DECISION_REJETE => 'danger',
                        ValidationDepense::DECISION_RENVOYE => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Par'),
                Tables\Columns\TextColumn::make('signe_le')
                    ->label('Le')
                    ->dateTime('d/m/Y à H:i'),
                Tables\Columns\TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->placeholder('—')
                    ->wrap(),
                Tables\Columns\ImageColumn::make('signature')
                    ->label('Signature')
                    ->state(fn (ValidationDepense $record) => $record->signature_path
                        ? route('depenses.signature', $record)
                        : null)
                    ->height(45),
                Tables\Columns\IconColumn::make('empreinte')
                    ->label('Empreinte')
                    ->state(fn (ValidationDepense $record) => $record->signature_path ? $record->hashValide() : null)
                    ->boolean()
                    ->tooltip(fn (ValidationDepense $record) => match (true) {
                        ! $record->signature_path => null,
                        $record->hashValide() => 'La demande n\'a pas été modifiée depuis cette signature.',
                        default => 'Attention : la demande a été modifiée après cette signature.',
                    }),
                Tables\Columns\TextColumn::make('ip')
                    ->label('Adresse IP')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('Aucune étape pour le moment')
            ->emptyStateDescription('Le circuit démarre quand la demande est soumise.')
            ->paginated(false);
    }
}
