<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Pages\Auth\ResetPassword;
use App\Filament\Pages\Profile;
use App\Http\Middleware\HasEntreprise;
use App\Http\Middleware\RedirectIfUnpaidInvoice;
use Filament\Navigation\NavigationGroup;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Illuminate\Support\HtmlString;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;
use Filament\Navigation\MenuItem;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset(RequestPasswordReset::class)
            ->emailVerification()
            ->profile()
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'danger' => Color::Rose,
            ])
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
           // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class, // Supprimé pour ne pas afficher les infos Filament en production
                //\App\Filament\Widgets\GuideUtilisationWidget::class,
                \App\Filament\Widgets\WhatsAppSupportWidget::class,
                //\App\Filament\Widgets\RessourcesExternesWidget::class,
                \App\Filament\Widgets\OnboardingWidget::class,
                \App\Filament\Widgets\ImpersonateWidget::class,
                \App\Filament\Widgets\ImpersonationStatusWidget::class,
                \App\Filament\Widgets\EntrepriseStatsWidget::class,
                \App\Filament\Widgets\SaasStatsWidget::class,
                \App\Filament\Widgets\TarificationPlansWidget::class,
                \App\Filament\Widgets\AbonnementsRevenueWidget::class,
                \App\Filament\Widgets\AbonnementsStatsWidget::class,
                \App\Filament\Widgets\FacturationsStatsWidget::class,
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Telescope')
                    ->url('/telescope')
                    ->icon('heroicon-o-cog')
                    ->visible(fn() => auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isSupport()))
            ])  
            ->spa()

            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Administration')
                    ->icon('heroicon-o-cog'),
                NavigationGroup::make()
                    ->label('Structure Organisationnelle')
                    ->icon('heroicon-o-building-office'),
                NavigationGroup::make()
                    ->label('Gestion des utilisateurs')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make()
                    ->label('Abonnements')
                    ->icon('heroicon-o-credit-card'),
                NavigationGroup::make()
                    ->label('Rapports')
                    ->icon('heroicon-o-chart-bar'),

                NavigationGroup::make()
                    ->label('Configuration')
                    ->icon('heroicon-o-adjustments-horizontal'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                RedirectIfUnpaidInvoice::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                HasEntreprise::class, // Ajout du middleware pour vérifier si l'utilisateur a une entreprise associée
            ])
            ->brandName('Genius Work')
            ->brandLogo(asset('images/logo/logo2.png'))
            ->darkMode(true) // Activer le mode sombre
            ->favicon(asset('images/logo/favicon.ico'))
            ->maxContentWidth('full')
            // ->topNavigation() // Commenté car la navigation latérale est préférée
            ->sidebarCollapsibleOnDesktop()
            ->emailVerification()
            ->plugins([
                EasyFooterPlugin::make()
                    ->withSentence(new HtmlString('&#169; ' . date('Y') . ' <strong>Genius Work</strong>'))
                    ->withLoadTime()
                    ->withFooterPosition('footer'),
                
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('Mon Profil')
                    ->setNavigationLabel('Mon Profil')
                    ->setNavigationGroup('Mon Compte')
                    ->setIcon('heroicon-o-user')
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm()
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // Les images seront stockées dans 'storage/app/public/avatars'
                        rules: 'mimes:jpeg,png,jpg|max:1024' // Accepte uniquement les fichiers JPEG et PNG avec une taille maximale de 1MB
                    )
                   
                    ->shouldShowSanctumTokens(
                        condition: fn() => auth()->user() && (auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                        permissions: ['create', 'view', 'update', 'delete']
                    ),
                    
                FilamentBackgroundsPlugin::make()
                    ->showAttribution(false) // Masquer l'attribution pour une interface plus propre
                    ->remember(3600) // Mettre en cache l'image pendant 1 heure
                    ->imageProvider(
                        MyImages::make()
                            ->directory('images/backgrounds') // Utiliser des images personnalisées dans ce répertoire
                    ),
                    
               

                ]);
    }
}
