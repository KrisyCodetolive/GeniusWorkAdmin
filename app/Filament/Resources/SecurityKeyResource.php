<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SecurityKeyResource\Pages;
use App\Models\SecurityKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\HtmlString;

class SecurityKeyResource extends Resource
{
    protected static ?string $model = SecurityKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    protected static ?string $navigationGroup = 'Administration';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $recordTitleAttribute = 'id';
    
    public static function getNavigationLabel(): string
    {
        return __('Clés de Sécurité');
    }
    
    public static function getModelLabel(): string
    {
        return __('Clé de Sécurité');
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('Clés de Sécurité');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->isSuperAdmin() || auth()->user()->isSupport();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Date d\'expiration')
                            ->required()
                            ->default(now()->addDays(7)),
                            
                        Forms\Components\TextInput::make('generated_by')
                            ->label('Généré par')
                            ->placeholder('ID de l\'utilisateur')
                            ->helperText('ID de l\'utilisateur ayant généré la clé'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('is_active')
                    ->label('')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->size('sm'),
                    
                Tables\Columns\TextColumn::make('generated_at')
                    ->label('Date de génération')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Date d\'expiration')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->expires_at->isPast() ? 'Expirée' : 'Expire dans ' . $record->expires_at->diffForHumans()),
                    
                Tables\Columns\TextColumn::make('generated_by')
                    ->label('Généré par')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Toutes les clés')
                    ->trueLabel('Clés actives uniquement')
                    ->falseLabel('Clés inactives uniquement'),
                    
                Tables\Filters\Filter::make('expires_at')
                    ->form([
                        Forms\Components\DatePicker::make('expires_from')
                            ->label('Expire après le'),
                        Forms\Components\DatePicker::make('expires_until')
                            ->label('Expire avant le'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['expires_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expires_at', '>=', $date),
                            )
                            ->when(
                                $data['expires_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expires_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_key')
                    ->label('Voir la clé')
                    ->icon('heroicon-o-eye')
                    ->color('secondary')
                    ->modalHeading('Clé de sécurité')
                    ->modalContent(function (SecurityKey $record): HtmlString {
                        try {
                            $key = Crypt::decryptString($record->key_encrypted);
                            $html = '
                                <div class="p-4 bg-gray-100 rounded-lg">
                                    <p class="text-sm text-gray-500 mb-2">Clé déchiffrée :</p>
                                    <div class="flex items-center space-x-2">
                                        <code class="text-xs bg-white p-2 rounded border flex-1 overflow-x-auto">' . $key . '</code>
                                        <button 
                                            type="button" 
                                            class="copy-button inline-flex items-center justify-center rounded-md font-medium transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset p-1 text-gray-800 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-primary-600 focus:ring-offset-white"
                                            onclick="navigator.clipboard.writeText(\'' . $key . '\').then(() => { this.innerHTML = \'<svg class=\\\'w-5 h-5 text-green-500\\\' fill=\\\'none\\\' stroke=\\\'currentColor\\\' viewBox=\\\'0 0 24 24\\\'><path stroke-linecap=\\\'round\\\' stroke-linejoin=\\\'round\\\' stroke-width=\\\'2\\\' d=\\\'M5 13l4 4L19 7\\\' /></svg>\'; setTimeout(() => { this.innerHTML = \'<svg class=\\\'w-5 h-5\\\' fill=\\\'none\\\' stroke=\\\'currentColor\\\' viewBox=\\\'0 0 24 24\\\'><path stroke-linecap=\\\'round\\\' stroke-linejoin=\\\'round\\\' stroke-width=\\\'2\\\' d=\\\'M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3\\\' /></svg>\'; }, 2000); });"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500">Statut : <span class="' . ($record->is_active ? 'text-green-600 font-medium' : 'text-red-600 font-medium') . '">' . ($record->is_active ? 'Active' : 'Inactive') . '</span></p>
                                    <p class="text-sm text-gray-500 mt-1">Expire le : <span class="font-medium">' . $record->expires_at->format('d/m/Y H:i') . '</span> (' . $record->expires_at->diffForHumans() . ')</p>
                                </div>
                            ';
                            return new HtmlString($html);
                        } catch (\Exception $e) {
                            return new HtmlString('<div class="p-4 bg-red-100 text-red-700 rounded">Impossible de déchiffrer la clé : ' . $e->getMessage() . '</div>');
                        }
                    })
                    ->visible(fn (SecurityKey $record) => !$record->expires_at->isPast()),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                
                Tables\Actions\BulkAction::make('deactivate')
                    ->label('Désactiver')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Builder $query) => $query->update(['is_active' => false]))
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSecurityKeys::route('/'),
            'create' => Pages\CreateSecurityKey::route('/create'),
            'edit' => Pages\EditSecurityKey::route('/{record}/edit'),
        ];
    }
}
