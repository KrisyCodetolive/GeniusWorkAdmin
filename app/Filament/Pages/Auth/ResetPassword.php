<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class ResetPassword extends BaseResetPassword
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
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

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Nouveau mot de passe')
            ->password()
            ->required()
            ->rule('min:8')
            ->rule('regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/')
            ->placeholder('••••••••')
            ->extraAttributes([
                'class' => 'fi-input',
            ])
            ->columnSpanFull()
            ->helperText('Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('password_confirmation')
            ->label('Confirmer le mot de passe')
            ->password()
            ->required()
            ->placeholder('••••••••')
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
        return 'Choisissez un nouveau mot de passe sécurisé';
    }

    protected function onRateLimitingException(TooManyRequestsException $exception): void
    {
        Notification::make()
            ->title('Trop de tentatives')
            ->body('Veuillez patienter quelques minutes avant de réessayer.')
            ->warning()
            ->send();
    }

    protected function afterResetPassword(): void
    {
        Notification::make()
            ->title('Mot de passe réinitialisé')
            ->body('Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.')
            ->success()
            ->send();
    }
}
