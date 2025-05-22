<x-filament-panels::page.simple>
    <x-slot name="logo">
        <div class="flex items-center space-x-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12">
            <span class="text-xl font-bold text-gray-900">Genius Work</span>
        </div>
    </x-slot>

    <div class="relative">
        {{-- Cercles décoratifs --}}
        <div class="absolute -top-12 -right-12 h-64 w-64 rounded-full bg-primary-500/10 blur-3xl"></div>
        <div class="absolute -bottom-12 -left-12 h-64 w-64 rounded-full bg-success-500/10 blur-3xl"></div>

        <div class="fi-auth-card">
            <div class="fi-auth-card-header">
                <h2 class="fi-auth-title">
                    {{ $this->getHeading() }}
                </h2>

                <p class="fi-auth-description">
                    {{ $this->getSubheading() }}
                </p>
            </div>

            <form wire:submit.prevent="authenticate" class="mt-8 grid gap-y-8">
                {{ $this->form }}

                <x-filament::button type="submit" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        {{ __('filament-panels::pages/auth/login.form.actions.authenticate.label') }}
                    </span>

                    <span wire:loading>
                        <div class="flex items-center gap-x-3">
                            <x-filament::loading-indicator class="h-5 w-5" />
                            {{ __('filament-panels::pages/auth/login.form.actions.authenticate.loading') }}
                        </div>
                    </span>
                </x-filament::button>
            </form>

            <div class="mt-8 text-center">
                <x-filament::link
                    :href="route('filament.admin.password.request')"
                    class="text-sm text-gray-600 hover:text-primary-500"
                >
                    {{ __('filament-panels::pages/auth/login.actions.request_password_reset.label') }}
                </x-filament::link>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <div class="text-center text-sm text-gray-600">
            &copy; {{ date('Y') }} Genius Work. Tous droits réservés.
        </div>
    </x-slot>
</x-filament-panels::page.simple>
