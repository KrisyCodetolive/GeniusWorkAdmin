<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategorieDepenseResource\Pages;
use App\Filament\Resources\CategorieDepenseResource\RelationManagers;
use App\Filament\Resources\Concerns\AvecEntreprise;
use App\Models\CategorieDepense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategorieDepenseResource extends Resource
{
    use AvecEntreprise;

    protected static ?string $model = CategorieDepense::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Comptabilité';

    protected static ?string $navigationLabel = 'Catégories de dépense';

    protected static ?string $modelLabel = 'catégorie de dépense';

    protected static ?string $pluralModelLabel = 'catégories de dépense';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin() || $user->isComptable();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        static::champEntreprise(),
                        Forms\Components\TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code_comptable')
                            ->label('Code comptable')
                            ->helperText('Compte du plan comptable (ex. 6061), repris sur le bon de sortie.')
                            ->maxLength(50),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('actif')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code_comptable')
                    ->label('Code comptable')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->visible(fn () => static::choisitEntreprise()),
                Tables\Columns\TextColumn::make('demandes_count')
                    ->label('Demandes')
                    ->counts('demandes'),
                Tables\Columns\IconColumn::make('actif')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('actif')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('nom');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DemandesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategorieDepenses::route('/'),
            'create' => Pages\CreateCategorieDepense::route('/create'),
            'edit' => Pages\EditCategorieDepense::route('/{record}/edit'),
        ];
    }
}
