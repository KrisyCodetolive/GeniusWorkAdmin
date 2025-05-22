<?php

namespace App\Filament\Resources\Paie;

use App\Filament\Resources\Paie\BulletinPaieResource\Pages;
use App\Models\Paie\BulletinPaie;
use App\Models\Paie\ConfigurationPaie;
use App\Models\Paie\ElementPaie;
use App\Models\Employeur;
use App\Services\Paie\CalculPaieService;
use App\Interfaces\Paie\CalculPaieServiceInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\HtmlString;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Carbon\Carbon;

class BulletinPaieResource extends Resource
{
    protected static ?string $model = BulletinPaie::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationGroup = 'Paie';
    
    protected static ?string $navigationLabel = 'Bulletins de paie';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // Obtenir le premier jour du mois courant
        $debutMois = Carbon::now()->startOfMonth()->format('Y-m-d');
        // Obtenir le dernier jour du mois courant
        $finMois = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->description('Informations de base du bulletin de paie')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Select::make('employeur_id')
                            ->label('Employé')
                            ->options(function () {
                                return Employeur::where('entreprise_id', Auth::user()->entreprise_id)
                                    ->where('statut', 'actif')
                                    ->get()
                                    ->pluck('nom_complet', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $employe = Employeur::find($state);
                                    if ($employe) {
                                        $set('salaire_base', $employe->salaire_base ?? 0);
                                    }
                                }
                            }),
                            
                        Forms\Components\Select::make('configuration_paie_id')
                            ->label('Configuration de paie')
                            ->options(function () {
                                return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                                    ->get()
                                    ->pluck('nom', 'id');
                            })
                            ->default(function () {
                                return ConfigurationPaie::where('entreprise_id', Auth::user()->entreprise_id)
                                    ->where('est_defaut', true)
                                    ->first()?->id;
                            })
                            ->searchable()
                            ->required(),
                            
                        Forms\Components\DatePicker::make('periode_debut')
                            ->label('Début de période')
                            ->default($debutMois)
                            ->required(),
                            
                        Forms\Components\DatePicker::make('periode_fin')
                            ->label('Fin de période')
                            ->default($finMois)
                            ->required()
                            ->after('periode_debut'),
                            
                        Forms\Components\DatePicker::make('date_paiement')
                            ->label('Date de paiement')
                            ->default(Carbon::now()->format('Y-m-d'))
                            ->required(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Éléments de rémunération')
                    ->description('Salaire de base et options de calcul')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\TextInput::make('salaire_base')
                            ->label('Salaire de base')
                            ->numeric()
                            ->required()
                            ->default(0),
                            
                        Forms\Components\Toggle::make('calcul_auto')
                            ->label('Calcul automatique')
                            ->helperText('Activer pour calculer automatiquement les éléments du bulletin')
                            ->default(true),
                            
                        Forms\Components\Placeholder::make('note')
                            ->label('')
                            ->content(new HtmlString('<div class="text-sm text-gray-500">Les éléments de rémunération seront calculés automatiquement lors de la génération du bulletin de paie.</div>')),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Indemnités')
                    ->description('Ajoutez les indemnités versées à l\'employé')
                    ->icon('heroicon-o-plus-circle')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Repeater::make('indemnites')
                            ->label(false)
                            ->schema([
                                Forms\Components\TextInput::make('libelle')
                                    ->label('Libellé')
                                    ->required(),
                                    
                                Forms\Components\Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'pourcentage' => 'Pourcentage du salaire de base',
                                        'montant_fixe' => 'Montant fixe',
                                    ])
                                    ->required()
                                    ->default('montant_fixe'),
                                    
                                Forms\Components\TextInput::make('taux')
                                    ->label('Taux (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'pourcentage'),
                                    
                                Forms\Components\TextInput::make('montant')
                                    ->label('Montant (FCFA)')
                                    ->numeric()
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'montant_fixe'),
                                    
                                Forms\Components\Toggle::make('imposable')
                                    ->label('Imposable')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                            ->collapsible()
                            ->defaultItems(0),
                    ]),
                    
                Forms\Components\Section::make('Primes')
                    ->description('Ajoutez les primes versées à l\'employé')
                    ->icon('heroicon-o-star')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Repeater::make('primes')
                            ->label(false)
                            ->schema([
                                Forms\Components\TextInput::make('libelle')
                                    ->label('Libellé')
                                    ->required(),
                                    
                                Forms\Components\Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'pourcentage' => 'Pourcentage du salaire de base',
                                        'montant_fixe' => 'Montant fixe',
                                    ])
                                    ->required()
                                    ->default('montant_fixe'),
                                    
                                Forms\Components\TextInput::make('taux')
                                    ->label('Taux (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'pourcentage'),
                                    
                                Forms\Components\TextInput::make('montant')
                                    ->label('Montant (FCFA)')
                                    ->numeric()
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'montant_fixe'),
                                    
                                Forms\Components\Toggle::make('imposable')
                                    ->label('Imposable')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                            ->collapsible()
                            ->defaultItems(0),
                    ]),
                    
                Forms\Components\Section::make('Retenues supplémentaires')
                    ->description('Ajoutez les retenues supplémentaires')
                    ->icon('heroicon-o-minus-circle')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Repeater::make('retenues')
                            ->label(false)
                            ->schema([
                                Forms\Components\TextInput::make('libelle')
                                    ->label('Libellé')
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('montant')
                                    ->label('Montant (FCFA)')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['libelle'] ?? null)
                            ->collapsible()
                            ->defaultItems(0),
                    ]),
                    
                Forms\Components\Section::make('Résultats calculés')
                    ->description('Montants calculés automatiquement')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('salaire_brut')
                                    ->label('Salaire brut')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('total_indemnites')
                                    ->label('Total indemnités')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('total_primes')
                                    ->label('Total primes')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('cnps_employe')
                                    ->label('CNPS employé')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('igr')
                                    ->label('IGR')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('total_retenues')
                                    ->label('Total retenues')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('salaire_net')
                                    ->label('Salaire net')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                                    
                                Forms\Components\TextInput::make('charges_patronales')
                                    ->label('Charges patronales')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('FCFA'),
                            ]),
                            
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('calculer')
                                ->label('Calculer le bulletin')
                                ->icon('heroicon-o-calculator')
                                ->iconPosition(IconPosition::After)
                                ->action(function (Forms\Get $get, Forms\Set $set, array $state) {
                                    // Récupérer le service de calcul de paie
                                    $calculPaieService = App::make(CalculPaieServiceInterface::class);
                                    
                                    // Récupérer l'employé et la configuration
                                    $employeur = Employeur::find($get('employeur_id'));
                                    $configuration = ConfigurationPaie::find($get('configuration_paie_id'));
                                    
                                    if (!$employeur || !$configuration) {
                                        return;
                                    }
                                    
                                    // Préparer les éléments supplémentaires
                                    $elementsSupplementaires = [
                                        'salaire_base' => $get('salaire_base'),
                                        'indemnites' => $get('indemnites') ?? [],
                                        'primes' => $get('primes') ?? [],
                                        'retenues' => $get('retenues') ?? [],
                                    ];
                                    
                                    // Calculer le salaire brut
                                    $salaireBrut = $calculPaieService->calculerSalaireBrut($employeur, $configuration, $elementsSupplementaires);
                                    $set('salaire_brut', $salaireBrut);
                                    
                                    // Calculer les retenues
                                    $retenues = $calculPaieService->calculerRetenues($employeur, $configuration, $salaireBrut, $elementsSupplementaires);
                                    $set('cnps_employe', $retenues['cnps_employe']);
                                    $set('igr', $retenues['igr']);
                                    $set('total_retenues', $retenues['total']);
                                    
                                    // Calculer le salaire net
                                    $salaireNet = $calculPaieService->calculerSalaireNet($salaireBrut, $retenues['total']);
                                    $set('salaire_net', $salaireNet);
                                    
                                    // Calculer les charges patronales
                                    $chargesPatronales = $calculPaieService->calculerChargesPatronales($employeur, $configuration, $salaireBrut);
                                    $set('charges_patronales', $chargesPatronales['total']);
                                    
                                    // Calculer les totaux des indemnités et primes
                                    $set('total_indemnites', $calculPaieService->calculerTotalIndemnites($elementsSupplementaires['indemnites'], $get('salaire_base')));
                                    $set('total_primes', $calculPaieService->calculerTotalPrimes($elementsSupplementaires['primes'], $get('salaire_base')));
                                })
                                ->color('primary')
                                ->size('lg'),
                        ])
                        ->alignment('center'),
                    ])
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc') // Tri par défaut : du plus récent au plus ancien
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Référence copiée')
                    ->icon('heroicon-o-document-text'),
                    
                Tables\Columns\TextColumn::make('employeur.nom_complet')
                    ->label('Employé')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                    
                Tables\Columns\TextColumn::make('periode_debut')
                    ->label('Début période')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                    
                Tables\Columns\TextColumn::make('periode_fin')
                    ->label('Fin période')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                    
                Tables\Columns\TextColumn::make('salaire_brut')
                    ->label('Salaire brut')
                    ->money('XOF')
                    ->sortable()
                    ->alignRight()
                    ->icon('heroicon-o-currency-dollar'),
                    
                Tables\Columns\TextColumn::make('salaire_net')
                    ->label('Salaire net')
                    ->money('XOF')
                    ->sortable()
                    ->alignRight()
                    ->weight('bold')
                    ->icon('heroicon-o-banknotes'),
                    
                Tables\Columns\BadgeColumn::make('statut')
                    ->label('Statut')
                    ->searchable()
                    ->sortable()
                    ->icons([
                        'heroicon-o-clock' => 'brouillon',
                        'heroicon-o-check' => 'validé',
                        'heroicon-o-x-mark' => 'annulé',
                    ])
                    ->colors([
                        'warning' => 'brouillon',
                        'success' => 'validé',
                        'danger' => 'annulé',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'brouillon' => 'Brouillon',
                        'validé' => 'Validé',
                        'annulé' => 'Annulé',
                    ]),
                    
                Tables\Filters\SelectFilter::make('employeur_id')
                    ->label('Employé')
                    ->options(function () {
                        return Employeur::where('entreprise_id', Auth::user()->entreprise_id)
                            ->get()
                            ->pluck('nom_complet', 'id');
                    }),
                    
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('periode_debut')
                            ->label('Début de période'),
                        Forms\Components\DatePicker::make('periode_fin')
                            ->label('Fin de période'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['periode_debut'],
                                fn (Builder $query, $date): Builder => $query->where('periode_debut', '>=', $date),
                            )
                            ->when(
                                $data['periode_fin'],
                                fn (Builder $query, $date): Builder => $query->where('periode_fin', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (BulletinPaie $record): bool => $record->statut === 'brouillon'),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->tooltip('Télécharger le PDF')
                    ->url(fn (BulletinPaie $record): string => route('paie.bulletins.pdf', $record->id))
                    ->openUrlInNewTab(),
                
                    //Visualiser le buletin 
                Tables\Actions\Action::make('visualiser')
                    ->label('Visualiser')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->tooltip('Aperçu du bulletin de paie')
                    ->url(fn (BulletinPaie $record): string => route('paie.bulletins.tailwind', $record->id))
                    ->openUrlInNewTab(),
            
                Tables\Actions\Action::make('valider')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (BulletinPaie $record): bool => $record->statut === 'brouillon')
                    ->action(fn (BulletinPaie $record) => $record->update([
                        'statut' => 'validé',
                        'valide_par' => Auth::id(),
                        'date_validation' => now(),
                    ])),
                Tables\Actions\Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (BulletinPaie $record): bool => $record->statut !== 'annulé')
                    ->form([
                        Forms\Components\Textarea::make('commentaire')
                            ->label('Motif d\'annulation')
                            ->required(),
                    ])
                    ->action(function (BulletinPaie $record, array $data) {
                        $record->update([
                            'statut' => 'annulé',
                            'commentaire' => $data['commentaire'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations générales')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('reference')
                            ->label('Référence')
                            ->weight(FontWeight::Bold),
                            
                        Infolists\Components\TextEntry::make('employeur.nom_complet')
                            ->label('Employé'),
                            
                        Infolists\Components\TextEntry::make('periode_debut')
                            ->label('Début de période')
                            ->date('d/m/Y'),
                            
                        Infolists\Components\TextEntry::make('periode_fin')
                            ->label('Fin de période')
                            ->date('d/m/Y'),
                            
                        Infolists\Components\TextEntry::make('date_paiement')
                            ->label('Date de paiement')
                            ->date('d/m/Y'),
                            
                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'brouillon' => 'warning',
                                'validé' => 'success',
                                'annulé' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3),
                    
                Infolists\Components\Section::make('Éléments de rémunération')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Infolists\Components\TextEntry::make('salaire_base')
                            ->label('Salaire de base')
                            ->money('XOF'),
                            
                        Infolists\Components\TextEntry::make('total_indemnites')
                            ->label('Total indemnités')
                            ->money('XOF'),
                            
                        Infolists\Components\TextEntry::make('total_primes')
                            ->label('Total primes')
                            ->money('XOF'),
                            
                        Infolists\Components\TextEntry::make('salaire_brut')
                            ->label('Salaire brut')
                            ->money('XOF')
                            ->weight(FontWeight::Bold),
                    ])
                    ->columns(4),
                    
                Infolists\Components\Section::make('Retenues')
                    ->icon('heroicon-o-minus-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('cnps_employe')
                            ->label('CNPS employé')
                            ->money('XOF'),
                            
                        Infolists\Components\TextEntry::make('igr')
                            ->label('IGR')
                            ->money('XOF'),
                            
                        Infolists\Components\TextEntry::make('total_retenues')
                            ->label('Total retenues')
                            ->money('XOF')
                            ->weight(FontWeight::Bold),
                    ])
                    ->columns(3),
                    
                Infolists\Components\Section::make('Salaire net')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Infolists\Components\TextEntry::make('salaire_net')
                            ->label('Salaire net à payer')
                            ->money('XOF')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('success'),
                    ]),
                    
                Infolists\Components\Section::make('Charges patronales')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Infolists\Components\TextEntry::make('cnps_employeur')
                            ->label('CNPS employeur')
                            ->money('XOF'),
                            
                        Infolists\Components\TextEntry::make('charges_patronales')
                            ->label('Total charges patronales')
                            ->money('XOF')
                            ->weight(FontWeight::Bold),
                    ])
                    ->columns(2),
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
            'index' => Pages\ListBulletinPaies::route('/'),
            'create' => Pages\CreateBulletinPaie::route('/create'),
            'edit' => Pages\EditBulletinPaie::route('/{record}/edit'),
            'view' => Pages\ViewBulletinPaie::route('/{record}'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('entreprise_id', Auth::user()->entreprise_id);
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('statut', 'brouillon')
            ->count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('statut', 'brouillon')
            ->exists() ? 'warning' : null;
    }
}
