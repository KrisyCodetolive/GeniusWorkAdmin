<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParametresNotificationResource\Pages;
use App\Models\ParametresNotification;
use App\Traits\HasEntrepriseScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParametresNotificationResource extends Resource
{
    use HasEntrepriseScope;
    
    protected static ?string $model = ParametresNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'Paramètres de notification';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'entreprise.nom';
    
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $form
            ->schema([
                Forms\Components\Select::make('entreprise_id')
                    ->relationship('entreprise', 'nom')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible($isSuperAdminOrSupport),

                Forms\Components\Tabs::make('Paramètres')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Types de notifications')
                            ->schema([
                                Forms\Components\Section::make('Événements à notifier')
                                    ->schema([
                                        Forms\Components\Toggle::make('notifier_absences')
                                            ->label('Notifier les absences')
                                            ->default(true),
                                        Forms\Components\Toggle::make('notifier_retards')
                                            ->label('Notifier les retards')
                                            ->default(true),
                                        Forms\Components\Toggle::make('notifier_conges')
                                            ->label('Notifier les congés')
                                            ->default(true),
                                        Forms\Components\Toggle::make('notifier_heures_supplementaires')
                                            ->label('Notifier les heures supplémentaires')
                                            ->default(true),
                                        Forms\Components\Toggle::make('notifier_permutations')
                                            ->label('Notifier les permutations')
                                            ->default(true),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Canaux de communication')
                            ->schema([
                                Forms\Components\Section::make('Canaux actifs')
                                    ->schema([
                                        Forms\Components\Toggle::make('activer_notifications_email')
                                            ->label('Activer les notifications par email')
                                            ->default(true),
                                        Forms\Components\Toggle::make('activer_notifications_sms')
                                            ->label('Activer les notifications par SMS')
                                            ->default(false),
                                        Forms\Components\Toggle::make('activer_notifications_push')
                                            ->label('Activer les notifications push')
                                            ->default(true),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Configuration Email')
                                    ->schema([
                                        Forms\Components\KeyValue::make('configuration_email')
                                            ->label('Paramètres email')
                                            ->keyLabel('Paramètre')
                                            ->valueLabel('Valeur')
                                            ->addActionLabel('Ajouter un paramètre')
                                            ->reorderable()
                                            ->columnSpan('full'),
                                    ])
                                    ->visible(fn (callable $get) => $get('activer_notifications_email')),

                                Forms\Components\Section::make('Configuration SMS')
                                    ->schema([
                                        Forms\Components\KeyValue::make('configuration_sms')
                                            ->label('Paramètres SMS')
                                            ->keyLabel('Paramètre')
                                            ->valueLabel('Valeur')
                                            ->addActionLabel('Ajouter un paramètre')
                                            ->reorderable()
                                            ->columnSpan('full'),
                                    ])
                                    ->visible(fn (callable $get) => $get('activer_notifications_sms')),
                            ]),

                        Forms\Components\Tabs\Tab::make('Modèles de messages')
                            ->schema([
                                Forms\Components\Section::make('Modèles Email')
                                    ->schema([
                                        Forms\Components\Repeater::make('modeles_email')
                                            ->label('Modèles de messages email')
                                            ->schema([
                                                Forms\Components\Select::make('type')
                                                    ->label('Type de notification')
                                                    ->options([
                                                        'absence' => 'Absence',
                                                        'retard' => 'Retard',
                                                        'conge' => 'Congé',
                                                        'supplementaire' => 'Heures supplémentaires',
                                                        'permutation' => 'Permutation',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('sujet')
                                                    ->label('Sujet')
                                                    ->required(),
                                                Forms\Components\Textarea::make('contenu')
                                                    ->label('Contenu')
                                                    ->required()
                                                    ->helperText('Vous pouvez utiliser des variables comme {nom}, {date}, {motif}, etc.')
                                                    ->columnSpan('full'),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['type'] ?? null)
                                            ->reorderable(false)
                                            ->columnSpan('full'),
                                    ])
                                    ->visible(fn (callable $get) => $get('activer_notifications_email')),

                                Forms\Components\Section::make('Modèles SMS')
                                    ->schema([
                                        Forms\Components\Repeater::make('modeles_sms')
                                            ->label('Modèles de messages SMS')
                                            ->schema([
                                                Forms\Components\Select::make('type')
                                                    ->label('Type de notification')
                                                    ->options([
                                                        'absence' => 'Absence',
                                                        'retard' => 'Retard',
                                                        'conge' => 'Congé',
                                                        'supplementaire' => 'Heures supplémentaires',
                                                        'permutation' => 'Permutation',
                                                    ])
                                                    ->required(),
                                                Forms\Components\Textarea::make('contenu')
                                                    ->label('Contenu')
                                                    ->required()
                                                    ->helperText('Vous pouvez utiliser des variables comme {nom}, {date}, {motif}, etc.')
                                                    ->columnSpan('full'),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['type'] ?? null)
                                            ->reorderable(false)
                                            ->columnSpan('full'),
                                    ])
                                    ->visible(fn (callable $get) => $get('activer_notifications_sms')),
                            ]),

                        Forms\Components\Tabs\Tab::make('Destinataires')
                            ->schema([
                                Forms\Components\Section::make('Destinataires supplémentaires')
                                    ->schema([
                                        Forms\Components\Repeater::make('destinataires_supplementaires')
                                            ->label('Destinataires supplémentaires par type d\'événement')
                                            ->schema([
                                                Forms\Components\Select::make('type')
                                                    ->label('Type d\'événement')
                                                    ->options([
                                                        'absence' => 'Absence',
                                                        'retard' => 'Retard',
                                                        'conge' => 'Congé',
                                                        'supplementaire' => 'Heures supplémentaires',
                                                        'permutation' => 'Permutation',
                                                    ])
                                                    ->required(),
                                                Forms\Components\Repeater::make('destinataires')
                                                    ->label('Destinataires')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('email')
                                                            ->label('Email')
                                                            ->email()
                                                            ->required(),
                                                        Forms\Components\TextInput::make('nom')
                                                            ->label('Nom')
                                                            ->required(),
                                                        Forms\Components\Select::make('role')
                                                            ->label('Rôle')
                                                            ->options([
                                                                'manager' => 'Manager',
                                                                'rh' => 'Ressources Humaines',
                                                                'direction' => 'Direction',
                                                                'autre' => 'Autre',
                                                            ])
                                                            ->required(),
                                                    ])
                                                    ->columns(3)
                                                    ->columnSpan('full'),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['type'] ?? null)
                                            ->reorderable(false)
                                            ->columnSpan('full'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Règles avancées')
                            ->schema([
                                Forms\Components\Section::make('Règles de notification')
                                    ->schema([
                                        Forms\Components\Repeater::make('regles_notification')
                                            ->label('Règles de notification par type d\'événement')
                                            ->schema([
                                                Forms\Components\Select::make('type')
                                                    ->label('Type d\'événement')
                                                    ->options([
                                                        'absence' => 'Absence',
                                                        'retard' => 'Retard',
                                                        'conge' => 'Congé',
                                                        'supplementaire' => 'Heures supplémentaires',
                                                        'permutation' => 'Permutation',
                                                    ])
                                                    ->required(),
                                                Forms\Components\Select::make('condition')
                                                    ->label('Condition')
                                                    ->options([
                                                        'toujours' => 'Toujours notifier',
                                                        'duree_min' => 'Durée minimale',
                                                        'repetition' => 'Répétition',
                                                        'statut' => 'Statut spécifique',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('valeur')
                                                    ->label('Valeur')
                                                    ->required()
                                                    ->helperText('Ex: 30 (minutes), 3 (occurrences), "approuve" (statut)'),
                                                Forms\Components\Toggle::make('actif')
                                                    ->label('Règle active')
                                                    ->default(true),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['type'] ?? null)
                                            ->reorderable(false)
                                            ->columnSpan('full'),
                                    ]),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('notifier_absences')
                    ->label('Absences')
                    ->boolean(),
                Tables\Columns\IconColumn::make('notifier_retards')
                    ->label('Retards')
                    ->boolean(),
                Tables\Columns\IconColumn::make('notifier_conges')
                    ->label('Congés')
                    ->boolean(),
                Tables\Columns\IconColumn::make('activer_notifications_email')
                    ->label('Email')
                    ->boolean(),
                Tables\Columns\IconColumn::make('activer_notifications_sms')
                    ->label('SMS')
                    ->boolean(),
                Tables\Columns\IconColumn::make('activer_notifications_push')
                    ->label('Push')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('entreprise_id')
                    ->relationship('entreprise', 'nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->preload()
                    ->visible($isSuperAdminOrSupport),
                Tables\Filters\Filter::make('with_email')
                    ->label('Avec notifications email')
                    ->query(fn (Builder $query) => $query->where('activer_notifications_email', true)),
                Tables\Filters\Filter::make('with_sms')
                    ->label('Avec notifications SMS')
                    ->query(fn (Builder $query) => $query->where('activer_notifications_sms', true)),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParametresNotifications::route('/'),
            'create' => Pages\CreateParametresNotification::route('/create'),
            'view' => Pages\ViewParametresNotification::route('/{record}'),
            'edit' => Pages\EditParametresNotification::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = auth()->user();
        
        // Si l'utilisateur n'est ni SuperAdmin ni Support, filtrer par entreprise
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        return $query;
    }
    
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas SuperAdmin ou Support, utiliser son entreprise_id
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $data['entreprise_id'] = $user->entreprise_id;
        }
        
        return $data;
    }
}
