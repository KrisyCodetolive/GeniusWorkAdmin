<x-filament::widget>
    <x-filament::section>
        <div>
           
            
            @if(session()->has('impersonate_origin_id'))
                <div class="bg-warning-100 border-l-4 border-warning-500 text-warning-700 p-4 mb-4" role="alert">
                    <div class="flex items-center">
                        <div class="py-1">
                            <svg class="h-6 w-6 text-warning-500 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold">Mode Impersonation</p>
                            <p class="text-sm">
                                Vous êtes connecté en tant que <strong>{{ Auth::user()->name }}</strong>
                                @if(Auth::user()->entreprise)
                                    de l'entreprise <strong>{{ Auth::user()->entreprise->nom }}</strong>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('filament.impersonate.stop') }}" class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2rem] px-3 text-sm text-white shadow focus:ring-white border-danger-600 bg-danger-600 hover:bg-danger-500 hover:border-danger-500 focus:bg-danger-700 focus:border-danger-700 focus:ring-offset-danger-700">
                            <span class="flex items-center gap-1">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                Revenir à mon compte
                            </span>
                        </a>
                    </div>
                </div>
            @else
                <form wire:submit.prevent="impersonate">
                    <div class="space-y-4">
                        <div>
                            {{ $this->form }}
                            
                        </div>
                        
                        <div class="flex justify-center mt-4">
                            <button type="submit" class="filament-button filament-button-size-md inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-primary-600 bg-primary-600 hover:bg-primary-500 hover:border-primary-500 focus:bg-primary-700 focus:border-primary-700 focus:ring-offset-primary-700">
                                <span class="flex items-center gap-1">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                    </svg>
                                    Se connecter
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </x-filament::section>
</x-filament::widget>
