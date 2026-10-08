<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\AvecEntreprise;
use App\Filament\Resources\DemandeDepenseResource\Actions\CircuitActions;
use App\Filament\Resources\DemandeDepenseResource\Pages;
use App\Filament\Resources\DemandeDepenseResource\RelationManagers;
use App\Models\CategorieDepense;
use App\Models\DemandeDepense;
use App\Models\Departement;
use App\Models\Employeur;
use App\Models\Filiale;
use App\Models\JustificatifDepense;
use App\Models\ParametreDepense;
use App\Models\User;
use App\Scopes\EntrepriseScope;
use App\Services\DepenseWorkflowService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DemandeDepenseResource extends Resource
{
    use AvecEntreprise;

    protected static ?string $model = DemandeDepense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Comptabilité';

    protected static ?string $navigationLabel = 'Demandes de dépense';

    protected static ?string $modelLabel = 'demande de dépense';

    protected static ?string $pluralModelLabel = 'demandes de dépense';

    protected static ?string $recordTitleAttribute = 'reference';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::aValiderPar(static::getEloquentQuery(), auth()->user())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Demandes qui attendent une décision de cet utilisateur (comptabilité ou CEO).
     */
    public static function aValiderPar(Builder $query, User $user): Builder
    {
        $statuts = [];

        if ($user->isComptable() || $user->isSuperAdmin()) {
            $statuts[] = DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE;
            $statuts[] = DemandeDepense::STATUT_JUSTIFICATION_SOUMISE;
        }

        if ($user->isSuperAdmin() || ($user->entreprise_id && ParametreDepense::pour($user->entreprise_id)->estCeo($user))) {
            $statuts[] = DemandeDepense::STATUT_EN_ATTENTE_CEO;
        }

        return $query
            ->whereIn('statut', $statuts)
            ->where('cree_par_user_id', '!=', $user->id);
    }

    /**
     * Le scope d'entreprise limite déjà à l'entreprise de l'utilisateur. Seuls la comptabilité,
     * la direction et l'administration voient toutes les demandes ; les autres ne voient que les leurs.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        $voitTout = $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin() || $user->isComptable()
            || ($user->entreprise_id && ParametreDepense::pour($user->entreprise_id)->estCeo($user));

        if (! $voitTout) {
            $query->where(function (Builder $query) use ($user) {
                $query->where('cree_par_user_id', $user->id);

                if ($user->employeur_id) {
                    $query->orWhere('demandeur_id', $user->employeur_id);
                }
            });
        }

        return $query;
    }

    /**
     * Formulaire de modification : mêmes étapes que la création, navigables librement.
     * Les justificatifs se gèrent alors dans l'onglet dédié (JustificatifsRelationManager).
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make(static::etapes(avecJustificatifs: false))
                    ->skippable()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Étapes de l'assistant de saisie d'une demande.
     *
     * @return array<Forms\Components\Wizard\Step>
     */
    public static function etapes(bool $avecJustificatifs = true): array
    {
        return array_values(array_filter([
            Forms\Components\Wizard\Step::make('Demande')
                ->description('Quoi et pour qui')
                ->icon('heroicon-o-document-text')
                ->schema([
                    static::section('Entreprise', 'Entreprise qui engage la dépense.', [
                        static::champEntreprise(),
                        Forms\Components\Placeholder::make('seuil_ceo')
                            ->label('Seuil de validation CEO')
                            ->content(fn (Forms\Get $get) => static::seuil($get) ?? 'Choisissez une entreprise'),
                    ])->visible(fn () => static::choisitEntreprise()),
                    static::section('Informations de la demande', 'Objet de la dépense et imputation comptable.', [
                        Forms\Components\TextInput::make('objet')
                            ->label('Objet')
                            ->placeholder('Ex. Achat de fournitures de bureau')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('demandeur_id')
                            ->label('Demandeur')
                            ->helperText('Employé pour le compte duquel la dépense est demandée.')
                            ->options(fn (Forms\Get $get) => Employeur::withoutGlobalScope(EntrepriseScope::class)
                                ->where('entreprise_id', static::entrepriseCourante($get))
                                ->orderBy('nom')
                                ->get()
                                ->pluck('nom_complet', 'id'))
                            ->default(fn () => auth()->user()->employeur_id)
                            ->searchable(),
                        Forms\Components\Select::make('categorie_depense_id')
                            ->label('Catégorie')
                            ->options(function (Forms\Get $get) {
                                if (! $entreprise = static::entrepriseCourante($get)) {
                                    return [];
                                }

                                ParametreDepense::pour($entreprise); // crée les catégories par défaut au premier usage

                                return CategorieDepense::withoutGlobalScope(EntrepriseScope::class)
                                    ->where('entreprise_id', $entreprise)
                                    ->actif()
                                    ->orderBy('nom')
                                    ->pluck('nom', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->createOptionForm([
                                static::section('Nouvelle catégorie', 'Elle sera aussi disponible dans « Catégories de dépense ».', [
                                    Forms\Components\TextInput::make('nom')
                                        ->label('Nom')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('code_comptable')
                                        ->label('Code comptable')
                                        ->helperText('Compte du plan comptable (ex. 6061).')
                                        ->maxLength(50),
                                ]),
                            ])
                            ->createOptionModalHeading('Nouvelle catégorie de dépense')
                            ->createOptionAction(fn (Forms\Components\Actions\Action $action) => $action
                                ->visible(fn ($livewire) => CategorieDepenseResource::canAccess() && static::entrepriseDuFormulaire($livewire)))
                            ->createOptionUsing(fn (array $data, $livewire) => CategorieDepense::create([
                                'entreprise_id' => static::entrepriseDuFormulaire($livewire),
                                'nom' => $data['nom'],
                                'code_comptable' => $data['code_comptable'] ?? null,
                            ])->getKey()),
                        Forms\Components\Select::make('departement_id')
                            ->label('Département')
                            ->options(fn (Forms\Get $get) => Departement::withoutGlobalScope(EntrepriseScope::class)
                                ->where('entreprise_id', static::entrepriseCourante($get))
                                ->orderBy('nom')
                                ->pluck('nom', 'id'))
                            ->searchable()
                            ->createOptionForm([
                                static::section('Nouveau département', 'Il sera aussi disponible dans « Départements ».', [
                                    Forms\Components\TextInput::make('nom')
                                        ->label('Nom')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('code')
                                        ->label('Code')
                                        ->helperText('Laissez vide pour le générer automatiquement.')
                                        ->maxLength(50),
                                    Forms\Components\Select::make('filiale_id')
                                        ->label('Filiale')
                                        ->options(fn ($livewire) => Filiale::withoutGlobalScope(EntrepriseScope::class)
                                            ->where('entreprise_id', static::entrepriseDuFormulaire($livewire))
                                            ->orderBy('nom')
                                            ->pluck('nom', 'id'))
                                        ->searchable(),
                                    Forms\Components\TextInput::make('description')
                                        ->label('Description')
                                        ->maxLength(255),
                                ]),
                            ])
                            ->createOptionModalHeading('Nouveau département')
                            ->createOptionAction(fn (Forms\Components\Actions\Action $action) => $action
                                ->visible(fn ($livewire) => static::peutCreerDepartement() && static::entrepriseDuFormulaire($livewire)))
                            ->createOptionUsing(fn (array $data, $livewire) => static::creerDepartement($data, static::entrepriseDuFormulaire($livewire))->getKey()),
                    ]),
                ]),

            Forms\Components\Wizard\Step::make('Montant')
                ->description('Combien et à qui')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    static::section('Montant et bénéficiaire', 'Somme à décaisser et personne ou fournisseur à payer.', [
                        Forms\Components\TextInput::make('montant')
                            ->label('Montant')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->live(onBlur: true)
                            ->suffix(fn (Forms\Get $get) => static::devise($get)),
                        Forms\Components\TextInput::make('beneficiaire')
                            ->label('Bénéficiaire')
                            ->helperText('Fournisseur ou personne qui reçoit le paiement.')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_besoin')
                            ->label('Date de besoin')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\Textarea::make('description')
                            ->label('Description / justification')
                            ->rows(1)
                            ->autosize(),
                    ]),
                ]),

            $avecJustificatifs
                ? Forms\Components\Wizard\Step::make('Justificatifs')
                    ->description('Devis, factures, reçus')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        static::section('Pièces justificatives', 'PDF ou image, 10 Mo maximum par fichier.', [
                            Forms\Components\Repeater::make('justificatifs')
                                ->relationship()
                                ->hiddenLabel()
                                ->schema(static::champsJustificatif())
                                ->columns(2)
                                ->defaultItems(0)
                                ->addActionLabel('Ajouter un justificatif')
                                ->columnSpanFull(),
                        ]),
                    ])
                : null,

            Forms\Components\Wizard\Step::make('Récapitulatif')
                ->description('Vérifier avant d\'enregistrer')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                    static::section('Récapitulatif', 'La demande est enregistrée comme brouillon. Elle part en validation quand vous cliquez sur « Soumettre ».', [
                        Forms\Components\Placeholder::make('recap_objet')
                            ->label('Objet')
                            ->content(fn (Forms\Get $get) => $get('objet') ?: '—'),
                        Forms\Components\Placeholder::make('recap_categorie')
                            ->label('Catégorie')
                            ->content(fn (Forms\Get $get) => CategorieDepense::withoutGlobalScope(EntrepriseScope::class)
                                ->find($get('categorie_depense_id'))?->nom ?? '—'),
                        Forms\Components\Placeholder::make('recap_montant')
                            ->label('Montant')
                            ->content(fn (Forms\Get $get) => number_format((float) $get('montant'), 0, ',', ' ').' '.static::devise($get)),
                        Forms\Components\Placeholder::make('recap_beneficiaire')
                            ->label('Bénéficiaire')
                            ->content(fn (Forms\Get $get) => $get('beneficiaire') ?: '—'),
                        Forms\Components\Placeholder::make('recap_circuit')
                            ->label('Circuit de validation')
                            ->content(fn (Forms\Get $get) => static::circuitPrevu($get)),
                        Forms\Components\Placeholder::make('recap_justificatifs')
                            ->label('Justificatifs')
                            ->content(function (Forms\Get $get, ?DemandeDepense $record) {
                                $nombre = $record
                                    ? $record->justificatifs()->count()
                                    : count(array_filter($get('justificatifs') ?? [], fn ($item) => filled($item['fichier'] ?? null)));

                                return match ($nombre) {
                                    0 => 'Aucun',
                                    1 => '1 fichier',
                                    default => "{$nombre} fichiers",
                                };
                            }),
                    ]),
                ]),
        ]));
    }

    /**
     * Section titrée à deux colonnes, utilisée dans chaque étape de l'assistant.
     *
     * @param  array<Forms\Components\Component>  $champs
     */
    private static function section(string $titre, string $description, array $champs): Forms\Components\Section
    {
        return Forms\Components\Section::make($titre)
            ->description($description)
            ->schema($champs)
            ->columns(2);
    }

    /**
     * Entreprise du formulaire principal, lue depuis la page : dans la fenêtre « créer une option »,
     * $get ne voit que les champs de cette fenêtre.
     */
    private static function entrepriseDuFormulaire($livewire): ?string
    {
        return data_get($livewire, 'data.entreprise_id') ?: auth()->user()->entreprise_id;
    }

    private static function peutCreerDepartement(): bool
    {
        $user = auth()->user();

        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    /**
     * Même règle de code que l'écran Départements quand une filiale est choisie ; sinon préfixe du nom
     * suivi d'un numéro libre dans l'entreprise (ex. LOG001).
     */
    private static function creerDepartement(array $data, string $entrepriseId): Departement
    {
        $code = $data['code'] ?? null;

        if (blank($code) && filled($data['filiale_id'] ?? null)) {
            $code = Departement::genererCode($data['nom'], $data['filiale_id']);
        }

        if (blank($code)) {
            $prefixe = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['nom']), 0, 3)) ?: 'DEP';
            $numero = 1;

            do {
                $code = $prefixe.str_pad((string) $numero++, 3, '0', STR_PAD_LEFT);
            } while (Departement::withoutGlobalScope(EntrepriseScope::class)
                ->withTrashed()
                ->where('entreprise_id', $entrepriseId)
                ->where('code', $code)
                ->exists());
        }

        return Departement::create([
            'entreprise_id' => $entrepriseId,
            'filiale_id' => $data['filiale_id'] ?? null,
            'nom' => $data['nom'],
            'code' => $code,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Champs d'un justificatif, partagés entre l'assistant de création et l'onglet Justificatifs.
     *
     * @return array<Forms\Components\Component>
     */
    public static function champsJustificatif(): array
    {
        return [
            Forms\Components\Select::make('type')
                ->label('Type')
                ->options(JustificatifDepense::TYPES)
                ->default('facture')
                ->required(),
            Forms\Components\FileUpload::make('fichier')
                ->label('Fichier')
                ->helperText('PDF ou image, 10 Mo maximum.')
                ->disk(DepenseWorkflowService::DISQUE)
                ->directory('depenses/justificatifs')
                ->visibility('private')
                ->storeFileNamesIn('nom_original')
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->maxSize(10240)
                ->required(),
        ];
    }

    private static function devise(Forms\Get $get): string
    {
        $entreprise = static::entrepriseCourante($get);

        return $entreprise ? ParametreDepense::pour($entreprise)->devise : 'FCFA';
    }

    private static function seuil(Forms\Get $get): ?string
    {
        if (! $entreprise = static::entrepriseCourante($get)) {
            return null;
        }

        $parametres = ParametreDepense::pour($entreprise);

        return number_format((float) $parametres->seuil_validation_ceo, 0, ',', ' ').' '.$parametres->devise;
    }

    private static function circuitPrevu(Forms\Get $get): string
    {
        $seuil = static::seuil($get);
        if (! $seuil || ! $get('montant')) {
            return '—';
        }

        $parametres = ParametreDepense::pour(static::entrepriseCourante($get));

        return (float) $get('montant') > (float) $parametres->seuil_validation_ceo
            ? "Comptabilité puis CEO (montant au-dessus du seuil de {$seuil})."
            : "Comptabilité uniquement (montant inférieur ou égal au seuil de {$seuil}).";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('objet')
                    ->label('Objet')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('demandeur')
                    ->label('Demandeur')
                    ->state(fn (DemandeDepense $record) => $record->nomDemandeur()),
                Tables\Columns\TextColumn::make('categorie.nom')
                    ->label('Catégorie')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('montant')
                    ->label('Montant')
                    ->formatStateUsing(fn ($state, DemandeDepense $record) => number_format((float) $state, 0, ',', ' ').' '.$record->devise)
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => DemandeDepense::STATUTS[$state] ?? $state)
                    ->color(fn (string $state) => DemandeDepense::COULEURS_STATUT[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->visible(fn () => static::choisitEntreprise())
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(DemandeDepense::STATUTS),
                Tables\Filters\SelectFilter::make('categorie_depense_id')
                    ->label('Catégorie')
                    ->relationship('categorie', 'nom'),
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('du')->label('Du'),
                        Forms\Components\DatePicker::make('au')->label('Au'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['du'], fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['au'], fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                // Actions du circuit en boutons : chacun ne voit que ce qu'il a le droit de faire à l'étape en cours.
                CircuitActions::soumettre(Tables\Actions\Action::class)->button(),
                CircuitActions::valider(Tables\Actions\Action::class)->button()->label('Valider'),
                CircuitActions::decaisser(Tables\Actions\Action::class)->button()->label('Payer'),
                CircuitActions::validerJustification(Tables\Actions\Action::class)->button()->label('Clôturer'),
                CircuitActions::renvoyerJustification(Tables\Actions\Action::class)->button()->label('Renvoyer justificatifs'),
                CircuitActions::renvoyer(Tables\Actions\Action::class)->button()->label('Renvoyer'),
                CircuitActions::rejeter(Tables\Actions\Action::class)->button(),
                CircuitActions::bonSortie(Tables\Actions\Action::class)->button()->label('Bon de sortie'),
                CircuitActions::annuler(Tables\Actions\Action::class)->button()->label('Annuler'),
                Tables\Actions\ViewAction::make()->iconButton()->tooltip('Voir'),
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Modifier'),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Supprimer le brouillon')
                    ->modalHeading(fn (DemandeDepense $record) => "Supprimer le brouillon {$record->reference}"),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\JustificatifsRelationManager::class,
            RelationManagers\ValidationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDemandeDepenses::route('/'),
            'create' => Pages\CreateDemandeDepense::route('/create'),
            'view' => Pages\ViewDemandeDepense::route('/{record}'),
            'edit' => Pages\EditDemandeDepense::route('/{record}/edit'),
        ];
    }
}
