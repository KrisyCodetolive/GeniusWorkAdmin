<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->placeholder('votre.email@exemple.com')
            ->extraAttributes([
                'class' => 'fi-input',
            ])
            ->columnSpanFull();
    }

    public function getHeading(): string
    {
        return 'Réinitialisation du mot de passe';
    }

    public function getSubheading(): string
    {
        return 'Entrez votre email pour recevoir un lien de réinitialisation';
    }

    protected function onRateLimitingException(TooManyRequestsException $exception): void
    {
        Notification::make()
            ->title('Trop de tentatives')
            ->body('Veuillez patienter quelques minutes avant de réessayer.')
            ->warning()
            ->send();
    }

    protected function afterResetRequested(): void
    {
        Notification::make()
            ->title('Email envoyé')
            ->body('Si un compte existe avec cet email, vous recevrez un lien de réinitialisation.')
            ->success()
            ->send();
    }
}
