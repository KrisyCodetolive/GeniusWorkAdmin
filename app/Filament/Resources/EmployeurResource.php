<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeurResource\Pages;
use App\Filament\Resources\EmployeurResource\RelationManagers;
use App\Models\Employeur;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Services\EmployeurCarteService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use App\Filament\Widgets\EmployeurStatsWidget;
use App\Filament\Widgets\EmployeurDepartementWidget;
use App\Filament\Widgets\EmployeurTendanceWidget;
use App\Filament\Widgets\EmployeeLimitWidget;

class EmployeurResource extends Resource
{
    protected static ?string $model = Employeur::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nom_complet';


    public static function getNavigationLabel(): string
    {
        return __('Employés');
    }

    public static function getNavigationBadge(): ?string
    {
        return Employeur::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }


    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Informations personnelles')
                        ->schema([
                            Section::make('Entreprise et département')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('entreprise_id')
                                            ->label('Entreprise')
                                            ->relationship('entreprise', 'nom')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->visible($isSuperAdminOrSupport)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $set('filiale_id', null);
                                                $set('departement_id', null);
                                            }),
                                        Select::make('filiale_id')
                                            ->label('Filiale')
                                            ->relationship('filiale', 'nom', function ($query) use ($isSuperAdminOrSupport) {
                                                if (!$isSuperAdminOrSupport) {
                                                    return $query->where('entreprise_id', auth()->user()->entreprise_id);
                                                }
                                                return $query;
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $set('departement_id', null);
                                            })
                                            ->helperText('Optionnel - Sélectionnez une filiale si applicable'),
                                        Select::make('departement_id')
                                            ->label('Département')
                                            ->relationship('departement', 'nom', function ($query, callable $get) use ($isSuperAdminOrSupport) {
                                                $query = $query->when(
                                                    $get('filiale_id'),
                                                    fn ($query, $filialeId) => $query->where('filiale_id', $filialeId),
                                                    function ($query) use ($isSuperAdminOrSupport) {
                                                        if (!$isSuperAdminOrSupport) {
                                                            return $query->where('entreprise_id', auth()->user()->entreprise_id);
                                                        }
                                                        return $query;
                                                    }
                                                );
                                                
                                                return $query;
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),
                                    ]),
                            ]),
                            Section::make('Identité')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('nom')
                                                ->label('Nom')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('prenom')
                                                ->label('Prénom')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('email')
                                                ->label('Email')
                                                ->email()
                                                ->maxLength(255),
                                            TextInput::make('telephone')
                                                ->label('Téléphone')
                                                ->tel()
                                                ->maxLength(20),
                                            DatePicker::make('date_naissance')
                                                ->label('Date de naissance')
                                                ->displayFormat('d/m/Y'),
                                            TextInput::make('lieu_naissance')
                                                ->label('Lieu de naissance')
                                                ->maxLength(255),
                                            Select::make('genre')
                                                ->label('Genre')
                                                ->options([
                                                    'masculin' => 'Masculin',
                                                    'feminin' => 'Féminin',
                                                    'autre' => 'Autre',
                                                ]),
                                            FileUpload::make('photo')
                                                ->label('Photo')
                                                ->image()
                                                ->directory('employeurs/photos')
                                                ->visibility('public')
                                                ->maxSize(2048)
                                                ->circleCropper(),
                                        ]),
                                ]),
                        ]),
                    Wizard\Step::make('Informations professionnelles')
                        ->schema([
                           
                            Section::make('Poste et contrat')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('poste')
                                                ->label('Poste')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('matricule')
                                                ->label('Matricule')
                                                ->maxLength(50),
                                            TextInput::make('code_employe')
                                                ->label('Code employé')
                                                ->maxLength(50)
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->helperText('Généré automatiquement'),
                                            Select::make('type_contrat')
                                                ->label('Type de contrat')
                                                ->options([
                                                    'cdi' => 'CDI',
                                                    'cdd' => 'CDD',
                                                    'stage' => 'Stage',
                                                    'interim' => 'Intérim',
                                                    'consultant' => 'Consultant',
                                                ]),
                                            DatePicker::make('date_embauche')
                                                ->label('Date d\'embauche')
                                                ->displayFormat('d/m/Y'),
                                            TextInput::make('salaire_base')
                                                ->label('Salaire de base')
                                                ->numeric()
                                                ->prefix('FCFA'),
                                            Select::make('statut')
                                                ->label('Statut')
                                                ->options([
                                                    'actif' => 'Actif',
                                                    'inactif' => 'Inactif',
                                                    'suspendu' => 'Suspendu',
                                                    'conge' => 'En congé',
                                                ])
                                                ->default('actif')
                                                ->required(),
                                        ]),
                                ]),
                        ]),
                    Wizard\Step::make('QR Code et paramètres')
                        ->schema([
                            Section::make('QR Code')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('qr_code_secret')
                                                ->label('Secret QR Code')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->helperText('Généré automatiquement'),
                                            Forms\Components\DateTimePicker::make('qr_code_expires_at')
                                                ->label('Date d\'expiration')
                                                ->disabled()
                                                ->dehydrated(false),
                                            Toggle::make('qr_code_active')
                                                ->label('QR Code actif')
                                                ->default(true),
                                        ]),
                                ])
                                ->collapsible(),
                            Section::make('Métadonnées')
                                ->schema([
                                    KeyValue::make('meta_donnees')
                                        ->label('Métadonnées')
                                        ->keyLabel('Clé')
                                        ->valueLabel('Valeur')
                                        ->reorderable()
                                        ->columnSpan('full'),
                                    KeyValue::make('configuration')
                                        ->label('Configuration')
                                        ->keyLabel('Paramètre')
                                        ->valueLabel('Valeur')
                                        ->reorderable()
                                        ->columnSpan('full'),
                                ]),
                        ]),
                    Wizard\Step::make('Compte utilisateur')
                        ->schema([
                            Section::make('Création de compte utilisateur')
                                ->schema([
                                    Toggle::make('create_user')
                                        ->label('Créer un compte utilisateur')
                                        ->default(false)
                                        ->reactive(),
                                    TextInput::make('user_password')
                                        ->label('Mot de passe')
                                        ->password()
                                        ->dehydrated(fn ($state) => filled($state))
                                        ->visible(fn (callable $get) => $get('create_user'))
                                        ->helperText('Laissez vide pour générer un mot de passe aléatoire'),
                                    Toggle::make('send_credentials')
                                        ->label('Envoyer les identifiants par email')
                                        ->default(false)
                                        ->visible(fn (callable $get) => $get('create_user')),
                                ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular(),
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('prenom')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('code_employe')
                    ->label('Code employé')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('poste')
                    ->label('Poste')
                    ->searchable(),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->visible($isSuperAdminOrSupport)
                    ->sortable(),
                Tables\Columns\TextColumn::make('filiale.nom')
                    ->label('Filiale')
                    ->sortable()
                    ->url(fn ($record) => $record->filiale_id ? static::getUrl('index', ['filiale' => $record->filiale_id]) : null)
                    ->color('primary')
                    ->icon('heroicon-o-building-office')
                    ->tooltip('Cliquer pour voir tous les employés de cette filiale'),
                Tables\Columns\TextColumn::make('departement.nom')
                    ->label('Département')
                    ->sortable()
                    ->url(fn ($record) => $record->departement_id ? static::getUrl('index', ['departement' => $record->departement_id]) : null)
                    ->color('success')
                    ->icon('heroicon-o-user-group')
                    ->tooltip('Cliquer pour voir tous les employés de ce département'),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'gray',
                        'suspendu' => 'danger',
                        'conge' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_embauche')
                    ->label('Date d\'embauche')
                    ->date('d/m/Y')
                    ->sortable(),
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
                SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->relationship('entreprise', 'nom')
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            // Réinitialiser les autres filtres via JavaScript
                            // Nous utiliserons un événement personnalisé pour cela
                        }
                        return $query;
                    })
                    ->visible(fn () => auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                SelectFilter::make('filiale_id')
                    ->label('Filiale')
                    ->relationship('filiale', 'nom', function (Builder $query) {
                        // Filtrer les filiales par entreprise si une entreprise est sélectionnée
                        $entrepriseId = request()->get('tableFilters.entreprise_id');
                        if ($entrepriseId) {
                            $query->where('entreprise_id', $entrepriseId);
                        } elseif (!auth()->user()->isSuperAdmin() && !auth()->user()->isSupport()) {
                            $query->where('entreprise_id', auth()->user()->entreprise_id);
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('departement_id')
                    ->label('Département')
                    ->relationship('departement', 'nom', function (Builder $query) {
                        // Filtrer les départements par filiale si une filiale est sélectionnée
                        $filialeId = request()->get('tableFilters.filiale_id');
                        if ($filialeId) {
                            $query->where('filiale_id', $filialeId);
                        } elseif ($entrepriseId = request()->get('tableFilters.entreprise_id')) {
                            $query->where('entreprise_id', $entrepriseId);
                        } elseif (!auth()->user()->isSuperAdmin() && !auth()->user()->isSupport()) {
                            $query->where('entreprise_id', auth()->user()->entreprise_id);
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                        'suspendu' => 'Suspendu',
                        'conge' => 'En congé',
                    ]),
                SelectFilter::make('type_contrat')
                    ->label('Type de contrat')
                    ->options([
                        'cdi' => 'CDI',
                        'cdd' => 'CDD',
                        'stage' => 'Stage',
                        'interim' => 'Intérim',
                        'consultant' => 'Consultant',
                    ]),
                Tables\Filters\Filter::make('date_embauche')
                    ->form([
                        Forms\Components\DatePicker::make('date_embauche_depuis')
                            ->label('Depuis'),
                        Forms\Components\DatePicker::make('date_embauche_jusqua')
                            ->label('Jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_embauche_depuis'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_embauche', '>=', $date),
                            )
                            ->when(
                                $data['date_embauche_jusqua'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_embauche', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('rotateQRCode')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('warning')
                    ->action(function (Employeur $record) {
                        $record->rotateQRCode();
                        $record->save();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('telechargerCarte')
                    ->label('Télécharger Carte')
                    ->icon('heroicon-o-identification')
                    ->color('success')
                    ->action(function (Employeur $record) {
                        $carteService = app(EmployeurCarteService::class);
                        return $carteService->genererCarte($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activerQRCodes')
                        ->label('Activer QR Codes')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->qr_code_active = true;
                                $record->save();
                            }
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('desactiverQRCodes')
                        ->label('Désactiver QR Codes')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->qr_code_active = false;
                                $record->save();
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('filtrerParFiliale')
                    ->label('Filtrer par filiale')
                    ->icon('heroicon-o-building-office')
                    ->form([
                        Select::make('filiale_id')
                            ->label('Filiale')
                            ->options(function () use ($isSuperAdminOrSupport) {
                                $query = \App\Models\Filiale::query();
                                
                                if (!$isSuperAdminOrSupport) {
                                    $query->where('entreprise_id', auth()->user()->entreprise_id);
                                }
                                
                                return $query->pluck('nom', 'id')->toArray();
                            })
                            ->searchable()
                            ->required()
                    ])
                    ->action(function (array $data): void {
                        $url = static::getUrl('index', ['filiale' => $data['filiale_id']]);
                        redirect()->to($url);
                    }),
                Tables\Actions\Action::make('filtrerParDepartement')
                    ->label('Filtrer par département')
                    ->icon('heroicon-o-user-group')
                    ->form([
                        Select::make('departement_id')
                            ->label('Département')
                            ->options(function () use ($isSuperAdminOrSupport) {
                                $query = \App\Models\Departement::query();
                                
                                if (!$isSuperAdminOrSupport) {
                                    $query->where('entreprise_id', auth()->user()->entreprise_id);
                                }
                                
                                return $query->pluck('nom', 'id')->toArray();
                            })
                            ->searchable()
                            ->required()
                    ])
                    ->action(function (array $data): void {
                        $url = static::getUrl('index', ['departement' => $data['departement_id']]);
                        redirect()->to($url);
                    }),
                Tables\Actions\Action::make('reinitialiserFiltres')
                    ->label('Réinitialiser les filtres')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(function (): void {
                        $url = static::getUrl('index');
                        redirect()->to($url);
                    })
                    ->visible(fn() => request()->has('filiale') || request()->has('departement') || request()->has('statut') || request()->has('type_contrat'))
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PresencesRelationManager::class,
            RelationManagers\CongesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            EmployeurStatsWidget::class,
            EmployeurDepartementWidget::class,
            EmployeeLimitWidget::class,
            EmployeurTendanceWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeurs::route('/'),
            'create' => Pages\CreateEmployeur::route('/create'),
            'view' => Pages\ViewEmployeur::route('/{record}'),
            'edit' => Pages\EditEmployeur::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
            
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, filtrer par entreprise
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
            
            // Si l'utilisateur est un Admin, il peut voir tous les employés de son entreprise
            // Sinon, s'il est un employeur, il ne voit que son propre profil
            if ($user->isEmployeur()) {
                $query->where('id', $user->employeur_id);
            }
        }
        
        // Filtrage par filiale si spécifié dans l'URL
        if (request()->has('filiale') && request()->get('filiale')) {
            $query->where('filiale_id', request()->get('filiale'));
        }
        
        // Filtrage par département si spécifié dans l'URL
        if (request()->has('departement') && request()->get('departement')) {
            $query->where('departement_id', request()->get('departement'));
        }
        
        // Filtrage par statut si spécifié dans l'URL
        if (request()->has('statut') && request()->get('statut')) {
            $query->where('statut', request()->get('statut'));
        }
        
        // Filtrage par type de contrat si spécifié dans l'URL
        if (request()->has('type_contrat') && request()->get('type_contrat')) {
            $query->where('type_contrat', request()->get('type_contrat'));
        }
        
        return $query;
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['nom', 'prenom', 'email', 'code_employe', 'matricule'];
    }
    
    public static function getModelListeners(): array
    {
        return [
            'eloquent.created: ' . Employeur::class => [self::class, 'handleEmployeurCreated'],
        ];
    }
    
    public static function handleEmployeurCreated(Employeur $record): void
    {
        $data = session()->get('employeur_form_data', []);
        
        if (isset($data['create_user']) && $data['create_user']) {
            $password = $data['user_password'] ?? Str::random(10);
            
            $user = User::create([
                'name' => $record->nom_complet ?? $record->nom . ' ' . $record->prenom,
                'email' => $record->email,
                'password' => Hash::make($password),
                'employeur_id' => $record->id,
                'entreprise_id' => $record->entreprise_id,
                'role' => 'employeur'
            ]);
            
            // Assigner le rôle employé si vous utilisez spatie/laravel-permission
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('employeur');
            }
            
            // Envoyer un email avec les identifiants si nécessaire
            if (isset($data['send_credentials']) && $data['send_credentials']) {
                // Code pour envoyer un email (à implémenter)
                // Vous pouvez utiliser Notification::route('mail', $record->email)->notify(new UserCredentialsNotification($user, $password));
            }
            
            session()->forget('employeur_form_data');
        }
    }
}
