<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UtilisateurRHResource\Pages;
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
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Placeholder;
use App\Policies\UtilisateurRHPolicy;

class UtilisateurRHResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'Mon Compte';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $modelLabel = 'Utilisateur RH';
    
    protected static ?string $pluralModelLabel = 'Utilisateurs RH';
    
    protected static ?string $slug = 'utilisateurs-rh';
    
    protected static string $policy = UtilisateurRHPolicy::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Utilisateur RH')
                    ->tabs([
                        Tabs\Tab::make('Informations personnelles')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make()
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
                            ]),
                        Tabs\Tab::make('Authentification')
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('password')
                                                    ->label('Mot de passe')
                                                    ->password()
                                                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                                    ->required(fn (string $operation): bool => $operation === 'create'),
                                                TextInput::make('password_confirmation')
                                                    ->label('Confirmation du mot de passe')
                                                    ->password()
                                                    ->dehydrated(false)
                                                    ->requiredWith('password'),
                                            ]),
                                    ]),
                            ]),
                        Tabs\Tab::make('Rôle et statut')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('role')
                                                    ->label('Rôle')
                                                    ->options([
                                                        'admin' => 'Administrateur',
                                                        'manager' => 'Manager',
                                                    ])
                                                    ->required()
                                                    ->default('manager')
                                                    ->reactive(),
                                                Select::make('statut')
                                                    ->label('Statut')
                                                    ->options([
                                                        'actif' => 'Actif',
                                                        'inactif' => 'Inactif',
                                                    ])
                                                    ->default('actif')
                                                    ->required(),
                                                Select::make('langue')
                                                    ->label('Langue')
                                                    ->options([
                                                        'fr' => 'Français',
                                                        'en' => 'Anglais',
                                                    ])
                                                    ->default('fr'),
                                                Select::make('fuseau_horaire')
                                                    ->label('Fuseau horaire')
                                                    ->options([
                                                        'Africa/Abidjan' => 'Abidjan (GMT+0)',
                                                        'Africa/Accra' => 'Accra (GMT+0)',
                                                        'Africa/Dakar' => 'Dakar (GMT+0)',
                                                        'Africa/Douala' => 'Douala (GMT+1)',
                                                        'Africa/Kinshasa' => 'Kinshasa (GMT+1)',
                                                        'Africa/Lagos' => 'Lagos (GMT+1)',
                                                        'Africa/Libreville' => 'Libreville (GMT+1)',
                                                        'Africa/Luanda' => 'Luanda (GMT+1)',
                                                        'Africa/Bangui' => 'Bangui (GMT+1)',
                                                        'Africa/Brazzaville' => 'Brazzaville (GMT+1)',
                                                        'Africa/Casablanca' => 'Casablanca (GMT+1)',
                                                        'Africa/Algiers' => 'Alger (GMT+1)',
                                                        'Africa/Tunis' => 'Tunis (GMT+1)',
                                                        'Africa/Nairobi' => 'Nairobi (GMT+3)',
                                                        'Europe/Paris' => 'Paris (GMT+2)',
                                                    ])
                                                    ->default('Africa/Douala'),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=111827')
                    ->toggleable(),
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
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrateur',
                        'manager' => 'Manager',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
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
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        'admin' => 'Administrateur',
                        'manager' => 'Manager',
                    ]),
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ]),
                TernaryFilter::make('email_verified_at')
                    ->label('Email vérifié')
                    ->nullable(),
                Tables\Filters\Filter::make('recents_connectes')
                    ->label('Récemment connectés')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('last_login_at')
                        ->where('last_login_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->color('primary'),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun utilisateur RH')
            ->emptyStateDescription('Créez votre premier utilisateur RH en cliquant sur le bouton ci-dessous.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Créer un utilisateur RH')
                    ->icon('heroicon-o-plus')
                    ->button()
                    ->color('primary'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUtilisateursRH::route('/'),
            'create' => Pages\CreateUtilisateurRH::route('/create'),
            'edit' => Pages\EditUtilisateurRH::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('entreprise_id', $entrepriseId)
            ->whereIn('role', ['admin', 'manager'])
            ->where('id', '!=', $user->id);
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
    
    /**
     * Vérifie si l'utilisateur peut accéder à cette ressource
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }
}
