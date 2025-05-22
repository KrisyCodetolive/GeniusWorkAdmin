<?php

namespace App\Filament\Resources\Paie;

use App\Filament\Resources\Paie\ConfigurationPaieResource\Pages;
use App\Models\Paie\ConfigurationPaie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ConfigurationPaieResource extends Resource
{
    protected static ?string $model = ConfigurationPaie::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    
    protected static ?string $navigationGroup = 'Paie';
    
    protected static ?string $navigationLabel = 'Configurations';
    
    protected static ?int $navigationSort = 2;
    
    // Caché dans le menu de navigation mais accessible via PlageHoraireBaseResource
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label('Nom de la configuration')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000),
                            
                        Forms\Components\Toggle::make('est_defaut')
                            ->label('Configuration par défaut')
                            ->helperText('Si activé, cette configuration sera utilisée par défaut lors de la génération des bulletins de paie.'),
                    ])
                    ->columns(1),
                    
                Forms\Components\Section::make('Paramètres de base')
                    ->schema([
                        Forms\Components\TextInput::make('smig')
                            ->label('SMIG (FCFA)')
                            ->required()
                            ->numeric()
                            ->default(75000),
                            
                        Forms\Components\TextInput::make('plafond_cnps')
                            ->label('Plafond CNPS (FCFA)')
                            ->required()
                            ->numeric()
                            ->default(225000),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Taux de cotisations')
                    ->schema([
                        Forms\Components\TextInput::make('taux_cnps_employe')
                            ->label('Taux CNPS employé (%)')
                            ->required()
                            ->numeric()
                            ->default(6.3)
                            ->step(0.01),
                            
                        Forms\Components\TextInput::make('taux_cnps_employeur')
                            ->label('Taux CNPS employeur (%)')
                            ->required()
                            ->numeric()
                            ->default(7.7)
                            ->step(0.01),
                            
                        Forms\Components\TextInput::make('taux_prestations_familiales')
                            ->label('Taux prestations familiales (%)')
                            ->required()
                            ->numeric()
                            ->default(5.75)
                            ->step(0.01),
                            
                        Forms\Components\TextInput::make('taux_accident_travail')
                            ->label('Taux accident du travail (%)')
                            ->required()
                            ->numeric()
                            ->default(2.0)
                            ->step(0.01),
                            
                        Forms\Components\TextInput::make('taux_assurance_maladie')
                            ->label('Taux assurance maladie (%)')
                            ->required()
                            ->numeric()
                            ->default(0.75)
                            ->step(0.01),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Paramètres IGR')
                    ->schema([
                        Forms\Components\TextInput::make('abattement_igr')
                            ->label('Taux d\'abattement IGR (%)')
                            ->required()
                            ->numeric()
                            ->default(20.0)
                            ->step(0.01),
                            
                        Forms\Components\Repeater::make('baremes_igr')
                            ->label('Barèmes IGR')
                            ->schema([
                                Forms\Components\TextInput::make('min')
                                    ->label('Montant minimum (FCFA)')
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                                    
                                Forms\Components\TextInput::make('max')
                                    ->label('Montant maximum (FCFA)')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->placeholder('Laisser à 0 pour illimité'),
                                    
                                Forms\Components\TextInput::make('taux')
                                    ->label('Taux (%)')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->step(0.01),
                            ])
                            ->columns(3)
                            ->defaultItems(5)
                            ->reorderable(),
                    ])
                    ->columns(1),
                    
                Forms\Components\Section::make('Paramètres des indemnités')
                    ->schema([
                        Forms\Components\Repeater::make('parametres_indemnites')
                            ->label('Indemnités')
                            ->schema([
                                Forms\Components\TextInput::make('nom')
                                    ->label('Nom de l\'indemnité')
                                    ->required(),
                                    
                                Forms\Components\Select::make('type')
                                    ->label('Type de calcul')
                                    ->options([
                                        'pourcentage' => 'Pourcentage du salaire de base',
                                        'montant_fixe' => 'Montant fixe',
                                    ])
                                    ->required()
                                    ->default('pourcentage'),
                                    
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
                            ->itemLabel(fn (array $state): ?string => $state['nom'] ?? null)
                            ->defaultItems(2)
                            ->collapsible(),
                    ])
                    ->columns(1),
                    
                Forms\Components\Section::make('Paramètres des primes')
                    ->schema([
                        Forms\Components\Repeater::make('parametres_primes')
                            ->label('Primes')
                            ->schema([
                                Forms\Components\TextInput::make('nom')
                                    ->label('Nom de la prime')
                                    ->required(),
                                    
                                Forms\Components\Select::make('type')
                                    ->label('Type de calcul')
                                    ->options([
                                        'pourcentage' => 'Pourcentage du salaire de base',
                                        'montant_fixe' => 'Montant fixe',
                                    ])
                                    ->required()
                                    ->default('pourcentage'),
                                    
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
                            ->itemLabel(fn (array $state): ?string => $state['nom'] ?? null)
                            ->defaultItems(2)
                            ->collapsible(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable(),
                    
                Tables\Columns\IconColumn::make('est_defaut')
                    ->label('Par défaut')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('smig')
                    ->label('SMIG')
                    ->money('XOF'),
                    
                Tables\Columns\TextColumn::make('plafond_cnps')
                    ->label('Plafond CNPS')
                    ->money('XOF'),
                    
                Tables\Columns\TextColumn::make('taux_cnps_employe')
                    ->label('CNPS employé')
                    ->formatStateUsing(fn (string $state): string => $state . ' %'),
                    
                Tables\Columns\TextColumn::make('taux_cnps_employeur')
                    ->label('CNPS employeur')
                    ->formatStateUsing(fn (string $state): string => $state . ' %'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('set_default')
                    ->label('Définir par défaut')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (ConfigurationPaie $record): bool => !$record->est_defaut)
                    ->action(function (ConfigurationPaie $record) {
                        // Désactiver toutes les autres configurations par défaut
                        ConfigurationPaie::where('entreprise_id', $record->entreprise_id)
                            ->where('id', '!=', $record->id)
                            ->where('est_defaut', true)
                            ->update(['est_defaut' => false]);
                            
                        // Définir cette configuration comme configuration par défaut
                        $record->update(['est_defaut' => true]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => Auth::user()->can('delete', ConfigurationPaie::class)),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations générales')
                    ->schema([
                        Infolists\Components\TextEntry::make('nom')
                            ->label('Nom')
                            ->weight(FontWeight::Bold),
                            
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description'),
                            
                        Infolists\Components\IconEntry::make('est_defaut')
                            ->label('Configuration par défaut')
                            ->boolean(),
                    ])
                    ->columns(1),
                    
                Infolists\Components\Section::make('Paramètres de base')
                    ->schema([
                        Infolists\Components\TextEntry::make('smig')
                            ->label('SMIG')
                            ->money('XOF'),
                            
                        Infolists\Components\TextEntry::make('plafond_cnps')
                            ->label('Plafond CNPS')
                            ->money('XOF'),
                    ])
                    ->columns(2),
                    
                Infolists\Components\Section::make('Taux de cotisations')
                    ->schema([
                        Infolists\Components\TextEntry::make('taux_cnps_employe')
                            ->label('Taux CNPS employé')
                            ->formatStateUsing(fn (string $state): string => $state . ' %'),
                            
                        Infolists\Components\TextEntry::make('taux_cnps_employeur')
                            ->label('Taux CNPS employeur')
                            ->formatStateUsing(fn (string $state): string => $state . ' %'),
                            
                        Infolists\Components\TextEntry::make('taux_prestations_familiales')
                            ->label('Taux prestations familiales')
                            ->formatStateUsing(fn (string $state): string => $state . ' %'),
                            
                        Infolists\Components\TextEntry::make('taux_accident_travail')
                            ->label('Taux accident du travail')
                            ->formatStateUsing(fn (string $state): string => $state . ' %'),
                            
                        Infolists\Components\TextEntry::make('taux_assurance_maladie')
                            ->label('Taux assurance maladie')
                            ->formatStateUsing(fn (string $state): string => $state . ' %'),
                    ])
                    ->columns(2),
                    
                Infolists\Components\Section::make('Paramètres IGR')
                    ->schema([
                        Infolists\Components\TextEntry::make('abattement_igr')
                            ->label('Taux d\'abattement IGR')
                            ->formatStateUsing(fn (string $state): string => $state . ' %'),
                            
                        Infolists\Components\RepeatableEntry::make('baremes_igr')
                            ->label('Barèmes IGR')
                            ->schema([
                                Infolists\Components\TextEntry::make('min')
                                    ->label('Minimum')
                                    ->money('XOF'),
                                    
                                Infolists\Components\TextEntry::make('max')
                                    ->label('Maximum')
                                    ->money('XOF')
                                    ->formatStateUsing(fn ($state) => $state == 0 ? 'Illimité' : number_format($state, 0, ',', ' ') . ' XOF'),
                                    
                                Infolists\Components\TextEntry::make('taux')
                                    ->label('Taux')
                                    ->formatStateUsing(fn (string $state): string => $state . ' %'),
                            ])
                            ->columns(3),
                    ])
                    ->columns(1),
                    
                Infolists\Components\Section::make('Paramètres des indemnités')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('parametres_indemnites')
                            ->label('Indemnités')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom')
                                    ->label('Nom'),
                                    
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->formatStateUsing(fn (string $state): string => $state === 'pourcentage' ? 'Pourcentage' : 'Montant fixe'),
                                    
                                Infolists\Components\TextEntry::make('taux')
                                    ->label('Taux')
                                    ->formatStateUsing(fn ($state): string => $state ? $state . ' %' : 'N/A')
                                    ->visible(fn (array $state): bool => $state['type'] === 'pourcentage'),
                                    
                                Infolists\Components\TextEntry::make('montant')
                                    ->label('Montant')
                                    ->money('XOF')
                                    ->visible(fn (array $state): bool => $state['type'] === 'montant_fixe'),
                                    
                                Infolists\Components\IconEntry::make('imposable')
                                    ->label('Imposable')
                                    ->boolean(),
                            ])
                            ->columns(2),
                    ])
                    ->columns(1),
                    
                Infolists\Components\Section::make('Paramètres des primes')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('parametres_primes')
                            ->label('Primes')
                            ->schema([
                                Infolists\Components\TextEntry::make('nom')
                                    ->label('Nom'),
                                    
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type')
                                    ->formatStateUsing(fn (string $state): string => $state === 'pourcentage' ? 'Pourcentage' : 'Montant fixe'),
                                    
                                Infolists\Components\TextEntry::make('taux')
                                    ->label('Taux')
                                    ->formatStateUsing(fn ($state): string => $state ? $state . ' %' : 'N/A')
                                    ->visible(fn (array $state): bool => $state['type'] === 'pourcentage'),
                                    
                                Infolists\Components\TextEntry::make('montant')
                                    ->label('Montant')
                                    ->money('XOF')
                                    ->visible(fn (array $state): bool => $state['type'] === 'montant_fixe'),
                                    
                                Infolists\Components\IconEntry::make('imposable')
                                    ->label('Imposable')
                                    ->boolean(),
                            ])
                            ->columns(2),
                    ])
                    ->columns(1),
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
            'index' => Pages\ListConfigurationPaies::route('/'),
            'create' => Pages\CreateConfigurationPaie::route('/create'),
            'edit' => Pages\EditConfigurationPaie::route('/{record}/edit'),
            'view' => Pages\ViewConfigurationPaie::route('/{record}'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('entreprise_id', Auth::user()->entreprise_id);
    }
}
