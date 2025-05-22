<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntrepriseResource\Pages;
use App\Filament\Resources\EntrepriseResource\RelationManagers;
use App\Models\Entreprise;
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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput\Placeholder;
use Filament\Tables\Columns\TextColumn;

class EntrepriseResource extends Resource
{
    protected static ?string $model = Entreprise::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return __('Entreprises');
    }

    public static function getNavigationBadge(): ?string
    {
        return \App\Models\Entreprise::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->can('viewAny', Entreprise::class);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                TextInput::make('nom')
                ->required()
                ->maxLength(255),
            TextInput::make('code')
                ->required()
                ->maxLength(50),
            Textarea::make('description')
                ->required()
                ->maxLength(500),
            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),
                TextInput::make('telephone')
                ->label('Téléphone')
                ->required()
                ->maxLength(15)

                ->helperText('Format attendu: 000-000-0000')
                ->prefixIcon('heroicon-o-phone'),
            FileUpload::make('logo')
                ->image()
                ->disk('public')
                ->nullable(),
            TextInput::make('site_web')
                ->url()
                ->nullable()
                ->prefixIcon('heroicon-o-globe-alt'),
            TextInput::make('secteur_activite')
                ->required()
                ->maxLength(255),
            TextInput::make('nif')
                ->required()
                ->maxLength(20),
            TextInput::make('rccm')
                ->required()
                ->maxLength(20),
            TextInput::make('raison_sociale')
                ->required()
                ->maxLength(255)
                ->prefixIcon('heroicon-o-globe-americas'),

            Select::make('statut')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->default('active')
                ->required(),
            TextInput::make('devise')
                ->required()
                ->maxLength(10),
            TextInput::make('fuseau_horaire')
                ->required()
                ->maxLength(255),
            Select::make('langue')
                ->options([
                    'fr' => 'Français',
                    'en' => 'Anglais',
                    'es' => 'Espagnol',
                 
                ])
                ->default('fr')
                ->required(),
            Textarea::make('configuration')
                ->json()
                ->nullable(),
            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom')
    ->label('Nom')
    ->sortable()
    ->searchable(),
    TextColumn::make('email')
    ->label('Email')
    ->sortable()
    ->searchable(),
    TextColumn::make('telephone')
    ->label('telephone'),
    TextColumn::make('statut')
    ->label('status'),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VisiteursRelationManager::class,
            RelationManagers\VisitesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntreprises::route('/'),
            'create' => Pages\CreateEntreprise::route('/create'),
            'view' => Pages\ViewEntreprise::route('/{record}'),
            'edit' => Pages\EditEntreprise::route('/{record}/edit'),
        ];
    }
}