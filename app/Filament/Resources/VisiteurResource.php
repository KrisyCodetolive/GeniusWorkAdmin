<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisiteurResource\Pages;
use App\Filament\Resources\VisiteurResource\RelationManagers;
use App\Models\Visiteur;
use App\Models\Entreprise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;

class VisiteurResource extends Resource
{
    protected static ?string $model = Visiteur::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Visiteurs';
    
    protected static ?string $modelLabel = 'Visiteur';
    
    protected static ?string $pluralModelLabel = 'Visiteurs';
    

    protected static bool $shouldRegisterNavigation = false;

    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du visiteur')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('entreprise_id')
                                    ->label('Entreprise')
                                    ->options(function () {
                                        $user = auth()->user();
                                        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
                                        
                                        if ($isSuperAdminOrSupport) {
                                            return Entreprise::pluck('nom', 'id');
                                        } else {
                                            // Pour les utilisateurs normaux, uniquement leur entreprise
                                            if ($user && $user->entreprise_id) {
                                                return Entreprise::where('id', $user->entreprise_id)->pluck('nom', 'id');
                                            }
                                            return [];
                                        }
                                    })
                                    ->required()
                                    ->searchable()
                                    ->default(function () {
                                        return Auth::user()->entreprise_id ?? null;
                                    })
                                    ->disabled(function () {
                                        $user = auth()->user();
                                        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
                                        
                                        return $user && !($isSuperAdminOrSupport) && $user->entreprise_id;
                                    }),
                                TextInput::make('code_visiteur')
                                    ->label('Code visiteur')
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Généré automatiquement'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nom')
                                    ->label('Nom')
                                    ->required(),
                                TextInput::make('prenom')
                                    ->label('Prénom')
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('telephone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->required()
                                    ->unique('visiteurs', 'telephone', ignoreRecord: true)
                                    ->rules([
                                        function () {
                                            return function (string $attribute, $value, \Closure $fail) {
                                                $entrepriseId = request()->get('entreprise_id');
                                                $record = \Filament\Facades\Filament::getCurrentResource()::getRecordInstance();
                                                
                                                $query = \App\Models\Visiteur::where('telephone', $value)
                                                    ->where('entreprise_id', $entrepriseId);
                                                
                                                if ($record && $record->exists) {
                                                    $query->where('id', '!=', $record->id);
                                                }
                                                
                                                if ($query->exists()) {
                                                    $fail("Ce numéro de téléphone existe déjà pour cette entreprise.");
                                                }
                                            };
                                        },
                                    ]),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
                            ]),
                    ]),
                Section::make('Informations professionnelles')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('organisation')
                                    ->label('Organisation'),
                                TextInput::make('fonction')
                                    ->label('Fonction/Poste'),
                            ]),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ]),
                Section::make('Documents')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('photo')
                                    ->label('Photo')
                                    ->image()
                                    ->directory('visiteurs/photos'),
                                FileUpload::make('piece_identite')
                                    ->label('Pièce d\'identité')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->directory('visiteurs/pieces_identite'),
                            ]),
                    ]),
                Section::make('Statut')
                    ->schema([
                        Select::make('statut')
                            ->label('Statut')
                            ->options([
                                'actif' => 'Actif',
                                'inactif' => 'Inactif',
                            ])
                            ->default('actif')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code_visiteur')
                    ->label('Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prenom')
                    ->label('Prénom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('organisation')
                    ->label('Organisation')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fonction')
                    ->label('Fonction')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visites_count')
                    ->label('Nombre de visites')
                    ->counts('visites'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('statut')
                    ->label('Statut')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn (Visiteur $record): bool => $record->statut === 'actif'),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ]),
                SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->options(Entreprise::pluck('nom', 'id'))
                    ->searchable(),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Créé depuis'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Créé jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('createVisite')
                    ->label('Nouvelle visite')
                    ->icon('heroicon-o-plus-circle')
                    ->url(fn (Visiteur $record): string => VisiteResource::getUrl('create_from_visiteur', ['visiteur_id' => $record->id]))
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activer')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each(fn (Visiteur $record) => $record->update(['statut' => 'actif'])))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('desactiver')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each(fn (Visiteur $record) => $record->update(['statut' => 'inactif'])))
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VisitesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisiteurs::route('/'),
            'create' => Pages\CreateVisiteur::route('/create'),
            'edit' => Pages\EditVisiteur::route('/{record}/edit'),
        ];
    }
}
