<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use App\Filament\Resources\SiteResource\Widgets\SiteStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Services\QRCodeService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin() || $user->isSupport();
        
        $actions = [
            // Action principale de création avec compteur de sites restants
            Actions\CreateAction::make()
                ->label(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $sitesUtilises = \App\Models\Site::where('entreprise_id', $entrepriseId)->count();
                    $sitesMax = 10; // Limite maximale de sites
                    $sitesRestants = max(0, $sitesMax - $sitesUtilises);
                    
                    return 'Nouveau site';
                })
                ->icon('heroicon-o-building-office-2')
                ->badge(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $sitesUtilises = \App\Models\Site::where('entreprise_id', $entrepriseId)->count();
                    $sitesMax = 10; // Limite maximale de sites
                    $sitesRestants = max(0, $sitesMax - $sitesUtilises);
                    
                    return "{$sitesRestants}/{$sitesMax}";
                })
                ->badgeColor(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $sitesUtilises = \App\Models\Site::where('entreprise_id', $entrepriseId)->count();
                    $sitesMax = 10; // Limite maximale de sites
                    $sitesRestants = max(0, $sitesMax - $sitesUtilises);
                    
                    // Rouge si moins de 20% restants, orange si moins de 50%, vert sinon
                    if ($sitesRestants <= 2) {
                        return 'danger';
                    } elseif ($sitesRestants <= 5) {
                        return 'warning';
                    } else {
                        return 'success';
                    }
                })
                ->tooltip(function () {
                    $entrepriseId = auth()->user()->entreprise_id;
                    $sitesUtilises = \App\Models\Site::where('entreprise_id', $entrepriseId)->count();
                    $sitesMax = 10; // Limite maximale de sites
                    $sitesRestants = max(0, $sitesMax - $sitesUtilises);
                    
                    return "Vous pouvez encore créer $sitesRestants sites sur un total de $sitesMax";
                })
                ->visible($isAdmin),
                
            // Carte des sites
            Actions\Action::make('viewMap')
                ->label('Carte des sites')
                ->icon('heroicon-o-map')
                ->color('success')
                ->url('/admin/site-map'),
                
            // Menu déroulant pour les actions QR Code
            Actions\ActionGroup::make([
                // Action pour générer un QR code pour un site spécifique
                Actions\Action::make('generateSiteQRCode')
                    ->label('Générer QR Code pour un site')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->form([
                        Select::make('site_id')
                            ->label('Sélectionnez un site')
                            ->options(function () use ($user) {
                                $query = \App\Models\Site::query();
                                
                                if (!$user->isSuperAdmin() && !$user->isSupport()) {
                                    $query->where('entreprise_id', $user->entreprise_id);
                                }
                                
                                return $query->pluck('nom', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('expiration_hours')
                            ->label('Durée de validité (heures)')
                            ->numeric()
                            ->default(24)
                            ->minValue(1)
                            ->maxValue(168)
                            ->suffix('heures')
                            ->helperText('Entre 1 et 168 heures (7 jours maximum)')
                    ])
                    ->action(function (array $data) {
                        try {
                            $qrCodeService = app(QRCodeService::class);
                            $site = \App\Models\Site::find($data['site_id']);
                            
                            if (!$site) {
                                Notification::make()
                                    ->title('Erreur')
                                    ->body('Site non trouvé')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            $result = $qrCodeService->generateSiteQRCode(
                                $site, 
                                $data['expiration_hours'] ?? 24
                            );
                            
                            if ($result['success']) {
                                Notification::make()
                                    ->title('QR Code généré')
                                    ->body("QR Code généré avec succès pour le site: {$site->nom}")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Erreur')
                                    ->body($result['message'] ?? 'Erreur lors de la génération du QR Code')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body('Erreur lors de la génération: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                    
                // Action pour générer des QR codes pour tous les sites
                Actions\Action::make('generateAllQRCodes')
                    ->label('Générer QR Codes pour tous les sites')
                    ->icon('heroicon-o-squares-plus')
                    ->color('warning')
                    ->form([
                        TextInput::make('expiration_hours')
                            ->label('Durée de validité (heures)')
                            ->numeric()
                            ->default(24)
                            ->minValue(1)
                            ->maxValue(168)
                            ->suffix('heures')
                            ->helperText('Entre 1 et 168 heures (7 jours maximum)')
                    ])
                    ->action(function (array $data) {
                        try {
                            $qrCodeService = app(QRCodeService::class);
                            $user = auth()->user();
                            
                            $result = $qrCodeService->generateQRCodesForEntreprise(
                                $user->entreprise_id,
                                $data['expiration_hours'] ?? 24
                            );
                            
                            if ($result['success']) {
                                $totalSites = count($result['sites']);
                                $successCount = collect($result['sites'])
                                    ->where('qr_result.success', true)
                                    ->count();
                                    
                                Notification::make()
                                    ->title('QR Codes générés')
                                    ->body("QR Codes générés pour {$successCount}/{$totalSites} sites")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Erreur')
                                    ->body($result['message'] ?? 'Erreur lors de la génération des QR Codes')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body('Erreur lors de la génération: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Générer QR Codes pour tous les sites')
                    ->modalDescription('Cette action va générer des QR codes pour tous les sites actifs de votre entreprise.')
                    ->modalSubmitActionLabel('Générer tous'),
            ])
            ->label('QR Codes')
            ->icon('heroicon-o-qr-code')
            ->visible($isAdmin),
            
            // Menu déroulant pour les actions d'impression
            Actions\ActionGroup::make([
                // Action pour imprimer l'affiche QR Code
                Actions\Action::make('printQRCodePoster')
                    ->label('Imprimer une affiche QR')
                    ->icon('heroicon-o-printer')
                    ->form([
                        Select::make('site_id')
                            ->label('Sélectionnez un site')
                            ->options(function () use ($user) {
                                $query = \App\Models\Site::query();
                                
                                if (!$user->isSuperAdmin() && !$user->isSupport()) {
                                    $query->where('entreprise_id', $user->entreprise_id);
                                }
                                
                                // Filtrer seulement les sites avec QR codes valides
                                $query->whereNotNull('qr_token')
                                      ->whereNotNull('qr_generated_at')
                                      ->where('qr_generated_at', '>', now()->subHours(24));
                                
                                return $query->pluck('nom', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Seuls les sites avec QR codes valides sont affichés')
                    ])
                    ->action(function (array $data) {
                        $site = \App\Models\Site::find($data['site_id']);
                        
                        if (!$site || !$site->qr_token) {
                            Notification::make()
                                ->title('Erreur')
                                ->body('Site non trouvé ou QR code non généré')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Rediriger vers la page d'impression
                        return redirect()->route('qr-poster.print', ['site' => $site->id]);
                    }),
                    
                // Action pour imprimer toutes les affiches QR Code
                Actions\Action::make('printAllQRPosters')
                    ->label('Imprimer toutes les affiches')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function () use ($user) {
                        // Vérifier s'il y a des sites avec QR codes valides
                        $query = \App\Models\Site::whereNotNull('qr_token')
                            ->whereNotNull('qr_generated_at')
                            ->where('qr_generated_at', '>', now()->subHours(24));
                        
                        if (!$user->isSuperAdmin() && !$user->isSupport()) {
                            $query->where('entreprise_id', $user->entreprise_id);
                        }
                        
                        $sitesCount = $query->count();
                        
                        if ($sitesCount === 0) {
                            Notification::make()
                                ->title('Aucun QR code valide')
                                ->body('Aucun site n\'a de QR code valide. Générez d\'abord des QR codes.')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        // Rediriger vers la page d'impression de toutes les affiches
                        return redirect()->route('qr-poster.print-all');
                    }),
                    
                // Action pour télécharger toutes les affiches en PDF
                Actions\Action::make('downloadAllQRPosters')
                    ->label('Télécharger toutes les affiches (PDF)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () use ($user) {
                        // Vérifier s'il y a des sites avec QR codes valides
                        $query = \App\Models\Site::whereNotNull('qr_token')
                            ->whereNotNull('qr_generated_at')
                            ->where('qr_generated_at', '>', now()->subHours(24));
                        
                        if (!$user->isSuperAdmin() && !$user->isSupport()) {
                            $query->where('entreprise_id', $user->entreprise_id);
                        }
                        
                        $sitesCount = $query->count();
                        
                        if ($sitesCount === 0) {
                            Notification::make()
                                ->title('Aucun QR code valide')
                                ->body('Aucun site n\'a de QR code valide. Générez d\'abord des QR codes.')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        // Rediriger vers le téléchargement PDF
                        return redirect()->route('qr-poster.download-all-pdf');
                    }),
            ])
            ->label('Impression')
            ->icon('heroicon-o-printer')
            ->visible($isAdmin),
        ];

        // Ajouter l'action pour générer des exemples si l'utilisateur est associé à une entreprise
        // et n'est pas SuperAdmin ou Support
        if ($user->entreprise_id && !$user->isSuperAdmin() && !$user->isSupport()) {
            $actions[] = Actions\Action::make('genererExemples')
                ->label('Générer des exemples')
                ->icon('heroicon-o-building-office-2')
                ->action(function () use ($user) {
                    $entreprise = $user->entreprise;
                    
                    if (!$entreprise) {
                        \Filament\Notifications\Notification::make()
                            ->title('Erreur')
                            ->body('Vous devez être associé à une entreprise pour générer des exemples de sites.')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Générer les exemples de sites
                    $result = \Database\Seeders\SiteExempleSeeder::createForEntreprise($entreprise);
                    
                    // Notification de succès
                    \Filament\Notifications\Notification::make()
                        ->title('Exemples générés')
                        ->body(count($result['created']) . ' sites exemples ont été créés pour votre entreprise.' . 
                               ($result['existants'] > 0 ? ' ' . $result['existants'] . ' sites existaient déjà.' : ''))
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Générer des exemples de sites')
                ->modalDescription('Cette action va créer des sites exemples pour votre entreprise. Vous pourrez les modifier ou les supprimer par la suite.')
                ->modalSubmitActionLabel('Générer');
        }

        return $actions;
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            SiteStatsWidget::class,
        ];
    }
}
