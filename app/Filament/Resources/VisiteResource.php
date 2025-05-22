<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisiteResource\Pages;
use App\Filament\Resources\VisiteResource\RelationManagers;
use App\Models\Visite;
use App\Models\Visiteur;
use App\Models\Site;
use App\Models\Entreprise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Collection;

class VisiteResource extends Resource
{
    protected static ?string $model = Visite::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = 'Visites';
    
    protected static ?string $modelLabel = 'Visite';
    
    protected static ?string $pluralModelLabel = 'Visites';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du visiteur')
                    ->schema([
                        Forms\Components\Select::make('visiteur_id')
                            ->label('Visiteur')
                            ->options(function () {
                                $entrepriseId = Auth::user()->entreprise_id;
                                return Visiteur::where('entreprise_id', $entrepriseId)
                                    ->get()
                                    ->pluck('nom_complet', 'id');
                            })
                            ->searchable()
                            ->required(),
                    ]),
                    
                Forms\Components\Section::make('Informations de la visite')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('site_id')
                                    ->label('Site')
                                    ->options(function () {
                                        $entrepriseId = Auth::user()->entreprise_id;
                                        return Site::where('entreprise_id', $entrepriseId)
                                            ->get()
                                            ->pluck('nom', 'id');
                                    })
                                    ->searchable()
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('motif_visite')
                                    ->label('Motif de la visite')
                                    ->required()
                                    ->maxLength(100),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('date_arrivee')
                                    ->label('Date et heure d\'arrivée')
                                    ->default(now())
                                    ->required(),
                                    
                                Forms\Components\DateTimePicker::make('date_depart')
                                    ->label('Date et heure de départ')
                                    ->visible(fn ($record) => $record && in_array($record->statut, ['terminee', 'annulee'])),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('personne_a_rencontrer')
                                    ->label('Personne à rencontrer')
                                    ->maxLength(100),
                                    
                                Forms\Components\TextInput::make('departement_a_visiter')
                                    ->label('Département à visiter')
                                    ->maxLength(100),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('badge_visiteur')
                                    ->label('Badge visiteur')
                                    ->maxLength(50),
                                    
                                Forms\Components\Select::make('statut')
                                    ->label('Statut')
                                    ->options([
                                        'en_cours' => 'En cours',
                                        'terminee' => 'Terminée',
                                        'annulee' => 'Annulée',
                                    ])
                                    ->default('en_cours')
                                    ->required(),
                            ]),
                            
                        Forms\Components\Textarea::make('commentaires')
                            ->label('Commentaires')
                            ->rows(3)
                            ->maxLength(1000),
                            
                        Forms\Components\Hidden::make('entreprise_id')
                            ->default(fn () => Auth::user()->entreprise_id),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('visiteur.nom_complet')
                    ->label('Visiteur')
                    ->searchable(['visiteurs.nom', 'visiteurs.prenom'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('visiteur.telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('site.nom')
                    ->label('Site')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('motif_visite')
                    ->label('Motif')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('date_arrivee')
                    ->label('Arrivée')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_depart')
                    ->label('Départ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('En cours'),
                Tables\Columns\TextColumn::make('personne_a_rencontrer')
                    ->label('Personne à rencontrer')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('departement_a_visiter')
                    ->label('Département')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('badge_visiteur')
                    ->label('Badge')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('statut')
                    ->label('Statut')
                    ->colors([
                        'primary' => 'en_cours',
                        'success' => 'terminee',
                        'danger' => 'annulee',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_cours' => 'En cours',
                        'terminee' => 'Terminée',
                        'annulee' => 'Annulée',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_cours' => 'En cours',
                        'terminee' => 'Terminée',
                        'annulee' => 'Annulée',
                    ]),
                SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->options(Entreprise::pluck('nom', 'id'))
                    ->searchable(),
                SelectFilter::make('site_id')
                    ->label('Site')
                    ->options(function () {
                        $entrepriseId = Auth::user()->entreprise_id ?? null;
                        if (!$entrepriseId) {
                            return Site::pluck('nom', 'id');
                        }
                        return Site::where('entreprise_id', $entrepriseId)
                            ->pluck('nom', 'id');
                    })
                    ->searchable(),
                Filter::make('date_arrivee')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_arrivee', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_arrivee', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
             
                Tables\Actions\Action::make('telecharger_ticket')
                    ->label('Télécharger ticket')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(function ($record) {
                        return route('visites.telecharger-ticket', $record);
                    })
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('terminer')
                    ->label('Terminer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut === 'en_cours')
                    ->action(function ($record) {
                        $record->update([
                            'statut' => 'terminee',
                            'date_depart' => now(),
                        ]);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->statut === 'en_cours')
                    ->action(function ($record) {
                        $record->update([
                            'statut' => 'annulee',
                        ]);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('terminer_visites')
                        ->label('Terminer les visites')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if ($record->statut === 'en_cours') {
                                    $record->update([
                                        'statut' => 'terminee',
                                        'date_depart' => now(),
                                    ]);
                                }
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('annuler_visites')
                        ->label('Annuler les visites')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if ($record->statut === 'en_cours') {
                                    $record->update([
                                        'statut' => 'annulee',
                                    ]);
                                }
                            });
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('filtrerParSite')
                    ->label('Filtrer par site')
                    ->icon('heroicon-o-building-office')
                    ->form([
                        Select::make('site_id')
                            ->label('Site')
                            ->options(function () use ($isSuperAdminOrSupport) {
                                $query = \App\Models\Site::query();
                                
                                if (!$isSuperAdminOrSupport) {
                                    $query->where('entreprise_id', auth()->user()->entreprise_id);
                                }
                                
                                return $query->pluck('nom', 'id')->toArray();
                            })
                            ->searchable()
                            ->required()
                    ])
                    ->action(function (array $data): void {
                        $url = static::getUrl('index', ['site' => $data['site_id']]);
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
                    ->visible(fn() => request()->has('site') || request()->has('departement') || request()->has('statut'))
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
            'index' => Pages\ListVisites::route('/'),
            'create' => Pages\CreateVisite::route('/create'),
            'create_from_visiteur' => Pages\CreateVisiteFromVisiteur::route('/create/visiteur/{visiteur_id}'),
            'edit' => Pages\EditVisite::route('/{record}/edit'),
            'stats' => Pages\VisiteStats::route('/stats'),
        ];
    }
    
    /**
     * Hook qui s'exécute avant la création d'une visite
     * Permet de créer un nouveau visiteur si nécessaire
     */
    public static function beforeCreate(array $data): array
    {
        // Si un nouveau visiteur est créé
        if (empty($data['visiteur_id']) && isset($data['nouveau_visiteur']) && is_array($data['nouveau_visiteur'])) {
            $nouveauVisiteur = $data['nouveau_visiteur'];
            $nouveauVisiteur['entreprise_id'] = $data['entreprise_id'] ?? auth()->user()->entreprise_id;
            
            // Utiliser le service VisiteurService pour créer le visiteur
            // Ce service gère correctement la génération des UUIDs et du code visiteur
            $visiteurService = app(\App\Services\Visite\VisiteurService::class);
            $visiteur = $visiteurService->createVisiteur($nouveauVisiteur);
            $data['visiteur_id'] = $visiteur->id;
        }

        // Supprimer les données du nouveau visiteur car elles ont été traitées
        if (isset($data['nouveau_visiteur'])) {
            unset($data['nouveau_visiteur']);
        }

        return $data;
    }
}
