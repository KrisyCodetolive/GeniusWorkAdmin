<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AvecEntreprise;
use App\Filament\Resources\ParametreDepenseResource\Pages;
use App\Models\ParametreDepense;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ParametreDepenseResource extends Resource
{
    use AvecEntreprise;

    protected static ?string $model = ParametreDepense::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Comptabilité';

    protected static ?string $navigationLabel = 'Paramètres dépenses';

    protected static ?string $modelLabel = 'paramètres dépenses';

    protected static ?string $pluralModelLabel = 'paramètres dépenses';

    protected static ?int $navigationSort = 3;

    /**
     * Le seuil et le choix du CEO relèvent de la direction, pas de la comptabilité.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    public static function canCreate(): bool
    {
        return static::choisitEntreprise();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Circuit de validation')
                    ->schema([
                        static::champEntreprise()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('seuil_validation_ceo')
                            ->label('Seuil de validation CEO')
                            ->helperText('Au-delà de ce montant, la validation du CEO est obligatoire après celle de la comptabilité.')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->default(ParametreDepense::SEUIL_PAR_DEFAUT)
                            ->suffix(fn (Forms\Get $get) => $get('devise') ?: 'FCFA'),
                        Forms\Components\Select::make('ceo_user_id')
                            ->label('CEO (validateur final)')
                            ->helperText("Si vide, l'administrateur de l'entreprise valide en tant que CEO.")
                            ->options(fn (Forms\Get $get) => User::where('entreprise_id', static::entrepriseCourante($get))
                                ->where('statut', 'actif')
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Références')
                    ->schema([
                        Forms\Components\TextInput::make('prefixe_reference')
                            ->label('Préfixe des références')
                            ->helperText('Ex. DEP donne DEP-2026-0001.')
                            ->required()
                            ->default('DEP')
                            ->alphaDash()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('devise')
                            ->label('Devise')
                            ->required()
                            ->default('FCFA')
                            ->maxLength(10),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable(),
                Tables\Columns\TextColumn::make('seuil_validation_ceo')
                    ->label('Seuil CEO')
                    ->formatStateUsing(fn ($state, ParametreDepense $record) => number_format((float) $state, 0, ',', ' ').' '.$record->devise),
                Tables\Columns\TextColumn::make('ceo.name')
                    ->label('CEO')
                    ->placeholder("Administrateur de l'entreprise"),
                Tables\Columns\TextColumn::make('prefixe_reference')
                    ->label('Préfixe'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParametreDepenses::route('/'),
            'create' => Pages\CreateParametreDepense::route('/create'),
            'edit' => Pages\EditParametreDepense::route('/{record}/edit'),
        ];
    }
}
