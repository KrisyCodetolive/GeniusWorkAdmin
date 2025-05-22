<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\KeyValue;
use Illuminate\Support\Carbon;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Notifications');
    }

    public static function getNavigationBadge(): ?string
    {
        return Notification::nonLues()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canAccess(): bool
    {
        if (auth()->user()->isSuperAdmin() || auth()->user()->isSupport()) {
            return true;
        }
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Select::make('entreprise_id')
                            ->relationship('entreprise', 'nom')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Select::make('type')
                            ->options([
                                'retard' => 'Retard',
                                'absence' => 'Absence',
                                'sortie_manquante' => 'Sortie manquante',
                                'conge_approuve' => 'Congé approuvé',
                                'conge_refuse' => 'Congé refusé',
                                'rappel_presence' => 'Rappel de présence',
                                'autre' => 'Autre',
                            ])
                            ->required(),
                    ]),
                
                Section::make('Contenu de la notification')
                    ->schema([
                        Textarea::make('message')
                            ->required()
                            ->maxLength(1000),
                        
                        KeyValue::make('data')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                            ->keyPlaceholder('Entrez une clé')
                            ->valuePlaceholder('Entrez une valeur')
                            ->addable()
                            ->deletable(),
                    ]),
                
                Section::make('Configuration d\'envoi')
                    ->columns(3)
                    ->schema([
                        Select::make('canal')
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'app' => 'Application',
                                'tous' => 'Tous les canaux',
                            ])
                            ->default('app')
                            ->required(),
                        
                        Select::make('priorite')
                            ->options([
                                'basse' => 'Basse',
                                'normale' => 'Normale',
                                'haute' => 'Haute',
                                'urgente' => 'Urgente',
                            ])
                            ->default('normale')
                            ->required(),
                        
                        Select::make('statut')
                            ->options([
                                'en_attente' => 'En attente',
                                'envoye' => 'Envoyé',
                                'lu' => 'Lu',
                                'echec' => 'Échec',
                            ])
                            ->default('en_attente')
                            ->required(),
                    ]),
                
                Section::make('Statut de livraison')
                    ->columns(3)
                    ->schema([
                        Toggle::make('email_envoye')
                            ->label('Email envoyé')
                            ->default(false),
                        
                        Toggle::make('sms_envoye')
                            ->label('SMS envoyé')
                            ->default(false),
                        
                        Toggle::make('lu')
                            ->label('Notification lue')
                            ->default(false),
                    ]),
                
                Section::make('Dates')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('date_envoi')
                            ->label('Date d\'envoi')
                            ->timezone('Africa/Dakar'),
                        
                        DateTimePicker::make('date_lecture')
                            ->label('Date de lecture')
                            ->timezone('Africa/Dakar'),
                    ]),
                
                Section::make('Informations de suivi')
                    ->columns(3)
                    ->schema([
                        TextInput::make('tentatives')
                            ->label('Nombre de tentatives')
                            ->numeric()
                            ->default(0),
                        
                        DateTimePicker::make('prochaine_tentative')
                            ->label('Prochaine tentative')
                            ->timezone('Africa/Dakar'),
                        
                        Textarea::make('erreur')
                            ->label('Message d\'erreur')
                            ->columnSpan(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date de création')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('entreprise.nom')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                
                BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'retard' => 'Retard',
                        'absence' => 'Absence',
                        'sortie_manquante' => 'Sortie manquante',
                        'conge_approuve' => 'Congé approuvé',
                        'conge_refuse' => 'Congé refusé',
                        'rappel_presence' => 'Rappel de présence',
                        default => ucfirst($state),
                    })
                    ->colors(fn (string $state): string => match ($state) {
                        'retard' => 'warning',
                        'absence' => 'danger',
                        'sortie_manquante' => 'info',
                        'conge_approuve' => 'success',
                        'conge_refuse' => 'danger',
                        'rappel_presence' => 'primary',
                        default => 'secondary',
                    }),
                
                TextColumn::make('message')
                    ->limit(50)
                    ->searchable(),
                
                BadgeColumn::make('statut')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_attente' => 'En attente',
                        'envoye' => 'Envoyé',
                        'lu' => 'Lu',
                        'echec' => 'Échec',
                        default => ucfirst($state),
                    })
                    ->colors(fn (string $state): string => match ($state) {
                        'en_attente' => 'secondary',
                        'envoye' => 'primary',
                        'lu' => 'success',
                        'echec' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('canal')
                    ->label('Canal')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'app' => 'Application',
                        'tous' => 'Tous les canaux',
                        default => ucfirst($state),
                    }),
                
                IconColumn::make('lu')
                    ->label('Lu')
                    ->boolean(),
                
                TextColumn::make('date_envoi')
                    ->label('Date d\'envoi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('date_lecture')
                    ->label('Date de lecture')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'retard' => 'Retard',
                        'absence' => 'Absence',
                        'sortie_manquante' => 'Sortie manquante',
                        'conge_approuve' => 'Congé approuvé',
                        'conge_refuse' => 'Congé refusé',
                        'rappel_presence' => 'Rappel de présence',
                    ]),
                
                SelectFilter::make('statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'envoye' => 'Envoyé',
                        'lu' => 'Lu',
                        'echec' => 'Échec',
                    ]),
                
                SelectFilter::make('canal')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'app' => 'Application',
                        'tous' => 'Tous les canaux',
                    ]),
                
                SelectFilter::make('priorite')
                    ->options([
                        'basse' => 'Basse',
                        'normale' => 'Normale',
                        'haute' => 'Haute',
                        'urgente' => 'Urgente',
                    ]),
                
                SelectFilter::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('entreprise_id')
                    ->label('Entreprise')
                    ->relationship('entreprise', 'nom')
                    ->searchable()
                    ->preload(),
                
                Filter::make('lu')
                    ->label('Notifications lues')
                    ->query(fn (Builder $query): Builder => $query->lues())
                    ->toggle(),
                
                Filter::make('non_lues')
                    ->label('Notifications non lues')
                    ->query(fn (Builder $query): Builder => $query->nonLues())
                    ->toggle(),
                
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Date de début'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Date de fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('marquerCommeLue')
                    ->label('Marquer comme lue')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => !$record->estLue())
                    ->action(function (Notification $record) {
                        $record->marquerCommeLue();
                    }),
                Tables\Actions\Action::make('marquerCommeEnvoyee')
                    ->label('Marquer comme envoyée')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn ($record) => $record->statut === 'en_attente')
                    ->action(function (Notification $record) {
                        $record->marquerCommeEnvoyee();
                    }),
                Tables\Actions\Action::make('renvoyer')
                    ->label('Renvoyer')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->statut === 'echec')
                    ->action(function (Notification $record) {
                        $record->update([
                            'statut' => 'en_attente',
                            'erreur' => null,
                            'prochaine_tentative' => null
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('marquerCommeLues')
                        ->label('Marquer comme lues')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                if (!$record->estLue()) {
                                    $record->marquerCommeLue();
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('marquerCommeEnvoyees')
                        ->label('Marquer comme envoyées')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                if ($record->statut === 'en_attente') {
                                    $record->marquerCommeEnvoyee();
                                }
                            }
                        }),
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
            'view' => Pages\ViewNotification::route('/{record}'),
        ];
    }
}
