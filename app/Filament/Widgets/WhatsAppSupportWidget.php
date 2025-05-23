<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WhatsAppSupportWidget extends Widget
{
    protected static string $view = 'filament.widgets.whats-app-support-widget';
    
    // Position du widget dans le tableau de bord (plus petit = plus haut)
    protected int $sortOrder = 2;
    
    
    // Informations de contact pour le support
    public string $supportTelephone = '+2250704750465';
    public string $supportDisponibilite = 'Lun-Ven, 9h-17h';
    public string $faqUrl = 'https://faq.geniuswork.com';
    
    public function mount(): void
    {
        // Rien à initialiser
    }
    
    public static function canView(): bool
    {
        // Afficher pour tous les utilisateurs connectés
        return auth()->check();
    }
    
    public function contacterSupport(string $telephone): void
    {
        // Formater le numéro pour WhatsApp
        $telephone = str_replace(['+', ' ', '-', '(', ')'], '', $telephone);
        
        // Rediriger vers WhatsApp
        redirect()->away("https://wa.me/{$telephone}");
    }
}
