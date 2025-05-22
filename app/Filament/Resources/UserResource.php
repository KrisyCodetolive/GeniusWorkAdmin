<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Support\Facades\Hash;
use App\Policies\UserPolicy;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';
    
    protected static string $policy = UserPolicy::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Informations de base')
                        ->schema([
                            Section::make('Informations personnelles')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Nom complet')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('email')
                                                ->label('Email')
                                                ->email()
                                                ->required()
                                                ->maxLength(255)
                                                ->unique(ignoreRecord: true),
                                            TextInput::make('telephone')
                                                ->label('Téléphone')
                                                ->tel()
                                                ->maxLength(20),
                                            FileUpload::make('photo')
                                                ->label('Photo de profil')
                                                ->image()
                                                ->directory('users/photos')
                                                ->visibility('public')
                                                ->maxSize(2048)
                                                ->circleCropper(),
                                        ]),
                                ]),
                            Section::make('Authentification')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('password')
                                                ->label('Mot de passe')
                                                ->password()
                                                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                                ->dehydrated(fn (?string $state): bool => filled($state))
                                                ->required(fn (string $operation): bool => $operation === 'create')
                                                ->maxLength(255),
                                            TextInput::make('pin')
                                                ->label('Code PIN')
                                                ->numeric()
                                                ->minLength(4)
                                                ->maxLength(6)
                                                ->dehydrateStateUsing(fn (string $state): string => $state)
                                                ->dehydrated(fn (?string $state): bool => filled($state)),
                                            Toggle::make('require_pin_change')
                                                ->label('Exiger changement de PIN')
                                                ->default(false),
                                        ]),
                                ]),
                        ]),
                    Wizard\Step::make('Rôle et statut')
                        ->schema([
                            Section::make('Rôle et statut')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('role')
                                                ->label('Rôle')
                                                ->options([
                                                    'admin' => 'Administrateur',
                                                    'employeur' => 'Employé',
                                                    'support' => 'Support',
                                                ])
                                                ->required(),
                                            Select::make('statut')
                                                ->label('Statut')
                                                ->options([
                                                    'actif' => 'Actif',
                                                    'inactif' => 'Inactif',
                                                    'suspendu' => 'Suspendu',
                                                ])
                                                ->default('actif')
                                                ->required(),
                                            Select::make('entreprise_id')
                                                ->label('Entreprise')
                                                ->relationship('entreprise', 'nom')
                                                ->searchable()
                                                ->preload(),
                                            Select::make('employeur_id')
                                                ->label('Employeur')
                                                ->relationship('employeur', 'nom')
                                                ->searchable()
                                                ->preload(),
                                        ]),
                                ]),
                        ]),
                    Wizard\Step::make('Préférences')
                        ->schema([
                            Section::make('Préférences utilisateur')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('langue')
                                                ->label('Langue')
                                                ->options([
                                                    'fr' => 'Français',
                                                    'en' => 'English',
                                                ])
                                                ->default('fr'),
                                            Select::make('fuseau_horaire')
                                                ->label('Fuseau horaire')
                                                ->options([
                                                    'Africa/Abidjan' => 'Africa/Abidjan (GMT+0)',
                                                    'Africa/Accra' => 'Africa/Accra (GMT+0)',
                                                    'Africa/Dakar' => 'Africa/Dakar (GMT+0)',
                                                    'Africa/Douala' => 'Africa/Douala (GMT+1)',
                                                    'Africa/Lagos' => 'Africa/Lagos (GMT+1)',
                                                    'Africa/Libreville' => 'Africa/Libreville (GMT+1)',
                                                    'Africa/Casablanca' => 'Africa/Casablanca (GMT+1)',
                                                ])
                                                ->default('Africa/Abidjan'),
                                        ]),
                                ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'employeur' => 'info',
                        'support' => 'warning',
                        'super_admin' => 'purple',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'gray',
                        'suspendu' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'admin' => 'Administrateur',
                        'employeur' => 'Employé',
                        'support' => 'Gestionnaire',
                        'super_admin' => 'Super Admin',
                    ]),
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                        'suspendu' => 'Suspendu',
                    ]),
                SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->relationship('entreprise', 'nom'),
                TernaryFilter::make('email_verified_at')
                    ->label('Email vérifié')
                    ->nullable(),
                TernaryFilter::make('telephone_verified_at')
                    ->label('Téléphone vérifié')
                    ->nullable(),
                Tables\Filters\Filter::make('recents_connectes')
                    ->label('Récemment connectés')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('last_login_at')
                        ->where('last_login_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
