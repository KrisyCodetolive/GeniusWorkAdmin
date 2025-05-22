<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CodePromoResource\Pages;
use App\Models\CodePromo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Policies\CodePromoPolicy;

class CodePromoResource extends Resource
{
    protected static ?string $model = CodePromo::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Codes Promo';

    protected static ?string $navigationGroup = 'Abonnements';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'code';

    protected static string $policy = CodePromoPolicy::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du code promo')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\TextInput::make('reduction')
                            ->label('Réduction (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(255)
                            ->columnSpan(['default' => 2, 'md' => 2]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Période de validité')
                    ->schema([
                        Forms\Components\DateTimePicker::make('date_debut')
                            ->label('Date de début')
                            ->required()
                            ->withoutSeconds()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\DateTimePicker::make('date_expiration')
                            ->label('Date d\'expiration')
                            ->required()
                            ->withoutSeconds()
                            ->after('date_debut')
                            ->columnSpan(['default' => 1, 'md' => 1]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Limitations')
                    ->schema([
                        Forms\Components\TextInput::make('nombre_utilisations_max')
                            ->label('Nombre maximum d\'utilisations')
                            ->helperText('Laissez vide pour un nombre illimité')
                            ->numeric()
                            ->minValue(1)
                            ->nullable()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\TextInput::make('nombre_utilisations')
                            ->label('Nombre d\'utilisations actuel')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\Toggle::make('actif')
                            ->label('Code actif')
                            ->default(true)
                            ->helperText('Désactivez pour suspendre temporairement ce code promo')
                            ->columnSpan(['default' => 2, 'md' => 2]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reduction')
                    ->label('Réduction')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: ' ',
                    )
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_debut')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_expiration')
                    ->label('Expiration')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_utilisations')
                    ->label('Utilisations')
                    ->numeric()
                    ->formatStateUsing(fn (string $state, CodePromo $record): string => 
                        $record->nombre_utilisations_max 
                            ? "{$state} / {$record->nombre_utilisations_max}" 
                            : $state
                    )
                    ->sortable(),
                Tables\Columns\IconColumn::make('actif')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('estValide')
                    ->label('Valide')
                    ->boolean()
                    ->getStateUsing(fn (CodePromo $record): bool => $record->estValide())
                    ->sortable(false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('actif')
                    ->label('Codes actifs')
                    ->query(fn (Builder $query): Builder => $query->where('actif', true))
                    ->toggle(),
                Tables\Filters\Filter::make('valide')
                    ->label('Codes valides')
                    ->query(function (Builder $query): Builder {
                        $now = now();
                        return $query
                            ->where('actif', true)
                            ->where('date_debut', '<=', $now)
                            ->where('date_expiration', '>=', $now)
                            ->where(function (Builder $query) {
                                $query->whereNull('nombre_utilisations_max')
                                    ->orWhereRaw('nombre_utilisations < nombre_utilisations_max');
                            });
                    })
                    ->toggle(),
                Tables\Filters\Filter::make('expire')
                    ->label('Codes expirés')
                    ->query(function (Builder $query): Builder {
                        $now = now();
                        return $query->where('date_expiration', '<', $now);
                    })
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Dupliquer')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (CodePromo $record) {
                        $newCode = $record->replicate();
                        $newCode->code = $record->code . '-COPY';
                        $newCode->nombre_utilisations = 0;
                        $newCode->save();
                        
                        return redirect()->route('filament.admin.resources.code-promos.edit', ['record' => $newCode->id]);
                    })
                    ->authorize('duplicate'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('activerDesactiver')
                        ->label('Activer/Désactiver')
                        ->icon('heroicon-o-power')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update(['actif' => !$record->actif]);
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->authorize('toggleActive'),
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
            'index' => Pages\ListCodePromos::route('/'),
            'create' => Pages\CreateCodePromo::route('/create'),
            'view' => Pages\ViewCodePromo::route('/{record}'),
            'edit' => Pages\EditCodePromo::route('/{record}/edit'),
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
