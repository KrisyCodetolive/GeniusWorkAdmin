<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresenceResource\Pages;
use App\Filament\Resources\PresenceResource\RelationManagers;
use App\Models\Presence;
use App\Models\User;
use App\Models\Site;
use App\Models\MethodePointage;
use App\Models\RaisonSortie;
use App\Models\Employeur;
use App\Traits\HasEntrepriseScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PresenceResource extends Resource
{
    use HasEntrepriseScope;
    
    protected static ?string $model = Presence::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $modelLabel = 'Présence';
    
    protected static ?string $pluralModelLabel = 'Présences';



    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        $formSchema = [
            Section::make('Informations de pointage')
                ->description('Enregistrez les détails de présence de l\'employé')
                ->icon('heroicon-o-clock')
                ->columns(3)
                ->schema([
                    Select::make('employeur_id')
                        ->label('Employé')
                        ->relationship(
                            'employeur', 
                            'nom',
                            function (Builder $query) use ($user, $isSuperAdminOrSupport) {
                                if (!$isSuperAdminOrSupport) {
                                    return $query->where('entreprise_id', $user->entreprise_id);
                                }
                                return $query;
                            }
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->prenom} {$record->nom}")
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(2)
                        ->prefixIcon('heroicon-o-user-circle'),
                    
                    DateTimePicker::make('date_heure_entree')
                        ->label('Date et heure d\'entrée')
                        ->default(now())
                        ->required()
                        ->seconds(false)
                        ->columnSpan(1)
                        ->suffixIcon('heroicon-o-arrow-right'),
                    
                    DateTimePicker::make('date_heure_sortie')
                        ->label('Date et heure de sortie')
                        ->seconds(false)
                        ->columnSpan(1)
                        ->suffixIcon('heroicon-o-arrow-left'),
                        
                    Select::make('site_id')
                        ->label('Site')
                        ->relationship(
                            'site', 
                            'nom',
                            function (Builder $query) use ($user, $isSuperAdminOrSupport) {
                                if (!$isSuperAdminOrSupport) {
                                    return $query->where('entreprise_id', $user->entreprise_id);
                                }
                                return $query;
                            }
                        )
                        ->searchable()
                        ->preload()
                        ->columnSpan(1)
                        ->prefixIcon('heroicon-o-building-office'),
                
                    Select::make('statut')
                        ->label('Statut')
                        ->options([
                            'present' => 'Présent',
                            'absent' => 'Absent',
                            'retard' => 'En retard',
                            'sortie' => 'Sorti',
                            'conge' => 'En congé',
                        ])
                        ->default('present')
                        ->required()
                        ->columnSpan(1)
                        ->prefixIcon('heroicon-o-check'),
                        
                    Textarea::make('commentaire')
                        ->label('Commentaire')
                        ->maxLength(1000)
                        ->columnSpan(3)
                        ->rows(3),
                ]),
                
            Section::make('Localisation')
                ->description('Coordonnées géographiques du pointage')
                ->icon('heroicon-o-map-pin')
                ->collapsible()
                ->columns(2)
                ->schema([
                    Grid::make(2)
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make('latitude_entree')
                                ->label('Latitude entrée')
                                ->numeric()
                                ->maxValue(90)
                                ->minValue(-90)
                                ->step(0.000001)
                                ->suffixIcon('heroicon-o-map'),
                            
                            TextInput::make('longitude_entree')
                                ->label('Longitude entrée')
                                ->numeric()
                                ->maxValue(180)
                                ->minValue(-180)
                                ->step(0.000001)
                                ->suffixIcon('heroicon-o-map'),
                        ]),
                        
                    Grid::make(2)
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make('latitude_sortie')
                                ->label('Latitude sortie')
                                ->numeric()
                                ->maxValue(90)
                                ->minValue(-90)
                                ->step(0.000001)
                                ->suffixIcon('heroicon-o-map'),
                            
                            TextInput::make('longitude_sortie')
                                ->label('Longitude sortie')
                                ->numeric()
                                ->maxValue(180)
                                ->minValue(-180)
                                ->step(0.000001)
                                ->suffixIcon('heroicon-o-map'),
                        ]),
                        
                    Grid::make(2)
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make('adresse_ip_entree')
                                ->label('Adresse IP entrée')
                                ->default(fn () => request()->ip())
                                ->suffixIcon('heroicon-o-globe-europe-africa'),
                            
                            TextInput::make('adresse_ip_sortie')
                                ->label('Adresse IP sortie')
                                ->suffixIcon('heroicon-o-globe-europe-africa'),
                        ]),
                ]),
                
            Section::make('Informations de l\'appareil')
                ->description('Détails du dispositif utilisé pour le pointage')
                ->icon('heroicon-o-device-phone-mobile')
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextInput::make('appareil_entree')
                        ->label('Appareil entrée')
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-device-tablet'),
                    
                    TextInput::make('appareil_sortie')
                        ->label('Appareil sortie')
                        ->maxLength(255)
                        ->prefixIcon('heroicon-o-device-tablet'),
                ]),
                
            Section::make('Validation')
                ->description('Approbation et vérification du pointage')
                ->icon('heroicon-o-check-badge')
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema([
                    Select::make('validateur_id')
                        ->label('Validé par')
                        ->relationship(
                            name: 'validateur',
                            titleAttribute: 'name',
                            modifyQueryUsing: function (Builder $query) use ($user, $isSuperAdminOrSupport) {
                                if (!$isSuperAdminOrSupport) {
                                    return $query->where('entreprise_id', $user->entreprise_id);
                                }
                                return $query;
                            }
                        )
                        ->searchable()
                        ->preload()
                        ->prefixIcon('heroicon-o-user-circle'),
                    
                    DateTimePicker::make('date_validation')
                        ->label('Date de validation')
                        ->seconds(false)
                        ->prefixIcon('heroicon-o-calendar'),
                        
                    Select::make('statut_validation')
                        ->label('Statut de validation')
                        ->options([
                            'en_attente' => 'En attente',
                            'approuve' => 'Approuvé',
                            'rejete' => 'Rejeté',
                        ])
                        ->default('en_attente')
                        ->columnSpan(2)
                        ->prefixIcon('heroicon-o-clipboard-document-check'),
                ]),
                
            Section::make('Calculs')
                ->description('Durées et écarts calculés automatiquement')
                ->icon('heroicon-o-calculator')
                ->collapsible()
                ->collapsed()
                ->columns(3)
                ->schema([
                    TextInput::make('duree_effective')
                        ->label('Durée effective (minutes)')
                        ->numeric()
                        ->disabled()
                        ->prefixIcon('heroicon-o-video-camera-slash'),
                    
                    TextInput::make('retard')
                        ->label('Retard (minutes)')
                        ->numeric()
                        ->disabled(),
                      //  ->prefixIcon('heroicon-o-exclamation-triangle'),
                    
                    TextInput::make('depart_anticipe')
                        ->label('Départ anticipé (minutes)')
                        ->numeric()
                        ->disabled()
                        ->prefixIcon('heroicon-o-arrow-left'),
                ]),
        ];
        
        // Ajouter un champ caché pour l'entreprise_id si l'utilisateur n'est pas SuperAdmin ou Support
        if (!$isSuperAdminOrSupport) {
            $formSchema[] = Hidden::make('entreprise_id')
                ->default($user->entreprise_id);
        } else {
            // Pour les SuperAdmin et Support, ajouter un sélecteur d'entreprise
            array_unshift($formSchema, 
                Section::make('Entreprise')
                    ->description('Sélectionnez l\'entreprise concernée')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Select::make('entreprise_id')
                            ->label('Entreprise')
                            ->options(function () {
                                return \App\Models\Entreprise::pluck('nom', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull()
                            ->prefixIcon('heroicon-o-building-office'),
                    ])
            );
        }
        
        return $form->schema($formSchema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'webpointage:qrcode_physique' => 'QR Code',
                        'mobile_app' => 'Mobile',
                        'biometric' => 'Biometric',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'webpointage:qrcode_physique' => 'heroicon-o-qr-code',
                        'mobile_app' => 'heroicon-o-device-phone-mobile',
                        'biometric' => 'heroicon-o-finger-print',
                        default => 'heroicon-o-device-computer',
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('date_heure_entree')
                    ->label('Entrée')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-arrow-right'),
                
                Tables\Columns\TextColumn::make('date_heure_sortie')
                    ->label('Sortie')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-arrow-left'),
                
                Tables\Columns\TextColumn::make('employeur.nom')
                    ->label('Employé')
                    ->formatStateUsing(fn ($record) => $record->employeur ? $record->employeur->prenom . ' ' . $record->employeur->nom : 'Non défini')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-circle'),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entree' => 'Entrée',
                        'sortie' => 'Sortie',
                        'pause_debut' => 'Pause début',
                        'pause_fin' => 'Pause fin',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entree' => 'success',
                        'sortie' => 'danger',
                        'pause_debut' => 'warning',
                        'pause_fin' => 'info',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-check'),
                
                Tables\Columns\TextColumn::make('site.nom')
                    ->label('Site')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office'),
                
                Tables\Columns\TextColumn::make('minutes_travaillees')
                    ->label('Durée')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-o-clock'),
                
                Tables\Columns\TextColumn::make('retard')
                    ->label('Retard')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '1' => 'En retard',
                        '0' => 'Ponctuel',
                        default => $state,
                    })
                    ->sortable()
                    ->icon('heroicon-o-exclamation-circle')
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'danger',
                        '0' => 'success',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('statut_validation')
                    ->label('Validation')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_attente' => 'En attente',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_attente' => 'gray',
                        'approuve' => 'success',
                        'rejete' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-clipboard-document-check'),
                
                Tables\Columns\TextColumn::make('validateur.name')
                    ->label('Validé par')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-user-circle'),
                
                Tables\Columns\TextColumn::make('date_validation')
                    ->label('Date validation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar'),
                
                Tables\Columns\TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->limit(30)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-chat-bubble-left-ellipsis'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-clock'),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-arrow-path'),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'present' => 'Présent',
                        'absent' => 'Absent',
                        'retard' => 'En retard',
                        'sortie' => 'Sorti',
                        'conge' => 'En congé',
                    ]),
                
                SelectFilter::make('statut_validation')
                    ->label('Statut de validation')
                    ->options([
                        'en_attente' => 'En attente',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                    ]),
                
                SelectFilter::make('employeur_id')
                    ->label('Employé')
                    ->relationship('employeur', 'nom', function (Builder $query) {
                        $user = auth()->user();
                        if (!$user->isSuperAdmin() && !$user->isSupport()) {
                            return $query->where('entreprise_id', $user->entreprise_id);
                        }
                        return $query;
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->prenom} {$record->nom}"),
                
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->relationship('site', 'nom', function (Builder $query) {
                        $user = auth()->user();
                        if (!$user->isSuperAdmin() && !$user->isSupport()) {
                            return $query->where('entreprise_id', $user->entreprise_id);
                        }
                        return $query;
                    }),
                
                Filter::make('date')
                    ->label('Période')
                    ->form([
                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date de début'),
                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date de fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_debut'],
                                fn (Builder $query, $date): Builder => $query->where(function (Builder $query) use ($date) {
                                    $query->whereDate('date_heure_entree', '>=', $date)
                                        ->orWhereDate('date_heure_sortie', '>=', $date);
                                }),
                            )
                            ->when(
                                $data['date_fin'],
                                fn (Builder $query, $date): Builder => $query->where(function (Builder $query) use ($date) {
                                    $query->whereDate('date_heure_entree', '<=', $date)
                                        ->orWhereDate('date_heure_sortie', '<=', $date);
                                }),
                            );
                    }),
                
                TernaryFilter::make('validation')
                    ->label('Validation')
                    ->placeholder('Tous')
                    ->trueLabel('Validés')
                    ->falseLabel('Non validés')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('validateur_id'),
                        false: fn (Builder $query) => $query->whereNull('validateur_id'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->label('')    
                ->icon('heroicon-o-pencil'),

                Tables\Actions\DeleteAction::make()
                ->label('')
                    ->icon('heroicon-o-trash'),
                Tables\Actions\Action::make('valider')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Presence $record) => $record->validateur_id === null && auth()->user()->isAdmin())
                    ->action(function (Presence $record) {
                        $record->update([
                            'validateur_id' => Auth::id(),
                            'date_validation' => Carbon::now(),
                            'statut_validation' => 'approuve'
                        ]);
                    }),
                Tables\Actions\Action::make('rejeter')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Presence $record) => $record->validateur_id === null && auth()->user()->isAdmin())
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif de rejet')
                            ->required(),
                    ])
                    ->action(function (Presence $record, array $data) {
                        $record->update([
                            'validateur_id' => Auth::id(),
                            'date_validation' => Carbon::now(),
                            'statut_validation' => 'rejete',
                            'commentaire' => $data['commentaire']
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isAdmin())
                        ->icon('heroicon-o-trash'),
                    Tables\Actions\BulkAction::make('validerMultiple')
                        ->label('Valider la sélection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn () => auth()->user()->isAdmin())
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->validateur_id === null) {
                                    $record->update([
                                        'validateur_id' => Auth::id(),
                                        'date_validation' => Carbon::now(),
                                        'statut_validation' => 'approuve'
                                    ]);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('date_heure_entree', 'desc');
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
            'index' => Pages\ListPresences::route('/'),
            'create' => Pages\CreatePresence::route('/create'),
            'edit' => Pages\EditPresence::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
               // SoftDeletingScope::class,
            ]);
            
        // Appliquer le filtre d'entreprise
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->whereHas('employeur', function (Builder $query) use ($user) {
                $query->where('entreprise_id', $user->entreprise_id);
            });
        }
        
        return $query;
    }
    
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Si l'employeur_id est défini, récupérer l'entreprise_id depuis l'employeur
        if (isset($data['employeur_id'])) {
            $employeur = Employeur::find($data['employeur_id']);
            if ($employeur) {
                $data['entreprise_id'] = $employeur->entreprise_id;
            }
        }
        
        return $data;
    }

  
}
