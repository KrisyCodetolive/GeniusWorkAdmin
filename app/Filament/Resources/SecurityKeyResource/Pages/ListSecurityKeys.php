<?php

namespace App\Filament\Resources\SecurityKeyResource\Pages;

use App\Filament\Resources\SecurityKeyResource;
use App\Models\SecurityKey;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class ListSecurityKeys extends ListRecords
{
    protected static string $resource = SecurityKeyResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            SecurityKeyResource\Widgets\SecurityKeyOverview::class,
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Actions\Action::make('generate_key')
                ->label('Générer une nouvelle clé')
                ->icon('heroicon-o-key')
                ->color('primary')
                ->action(function () {
                    $userId = Auth::id();
                    $keyData = SecurityKey::generateNew($userId);
                    
                    Notification::make()
                        ->success()
                        ->title('Nouvelle clé générée')
                        ->body('Une nouvelle clé de sécurité a été générée avec succès.')
                        ->send();
                    
                    return redirect(SecurityKeyResource::getUrl('index'));
                })
                ->requiresConfirmation()
                ->modalHeading('Générer une nouvelle clé')
                ->modalDescription('Êtes-vous sûr de vouloir générer une nouvelle clé de sécurité ? Toutes les clés actives seront désactivées.')
                ->modalSubmitActionLabel('Générer'),
        ];
    }
    
    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Aucune clé de sécurité trouvée';
    }
    
    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Les clés de sécurité sont utilisées pour sécuriser les QR codes de l\'application.';
    }
    
    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-key';
    }
    
    protected function getTableEmptyStateActions(): array
    {
        return [
            Tables\Actions\Action::make('create')
                ->label('Créer une clé')
                ->url(route('filament.resources.security-keys.create'))
                ->icon('heroicon-o-plus')
                ->button(),
                
            Tables\Actions\Action::make('generate')
                ->label('Générer automatiquement')
                ->action(function () {
                    $userId = Auth::id();
                    $keyData = SecurityKey::generateNew($userId);
                    
                    Notification::make()
                        ->success()
                        ->title('Nouvelle clé générée')
                        ->body('Une nouvelle clé de sécurité a été générée avec succès.')
                        ->send();
                    
                    return redirect(SecurityKeyResource::getUrl('index'));
                })
                ->icon('heroicon-o-bolt')
                ->color('gray')
                ->button(),
        ];
    }
}
