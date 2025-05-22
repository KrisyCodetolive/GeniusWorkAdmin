<x-filament::page>
    <x-filament::section>
        <div class="flex items-center space-x-4 mb-6">
            @if($logoUrl)
                <div class="flex-shrink-0">
                    <img src="{{ $logoUrl }}" alt="Logo de l'entreprise" class="h-20 w-auto object-contain rounded-lg shadow-sm">
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $entreprise->nom }}</h1>
                <p class="text-gray-500">{{ $entreprise->code }}</p>
                <div class="mt-1 flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $entreprise->statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($entreprise->statut) }}
                    </span>
                </div>
            </div>
        </div>

        @if($entreprise->abonnements()->where('statut', 'actif')->exists())
            @php
                $abonnement = $entreprise->abonnements()->where('statut', 'actif')->latest()->first();
                $daysLeft = now()->diffInDays($abonnement->date_fin, false);
            @endphp
            <div class="mb-6 p-4 bg-white rounded-lg border {{ $daysLeft < 7 ? 'border-red-200' : 'border-blue-200' }}">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium">Abonnement actif: {{ $abonnement->plan->nom ?? 'Plan' }}</h3>
                        <p class="text-sm text-gray-500">Expire le {{ $abonnement->date_fin->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-lg font-bold {{ $daysLeft < 7 ? 'text-red-600' : 'text-blue-600' }}">
                            {{ $daysLeft }} jours restants
                        </span>
                        <div class="mt-1">
                            <a href="{{ route('filament.admin.resources.abonnements.index') }}" class="text-sm text-primary-600 hover:text-primary-500">
                                Gérer mon abonnement
                            </a>
                        </div>
                    </div>
                </div>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                    @php
                        $percent = min(100, max(0, ($daysLeft / max(1, $abonnement->date_fin->diffInDays($abonnement->date_debut))) * 100));
                        $colorClass = $daysLeft < 7 ? 'bg-red-600' : ($daysLeft < 30 ? 'bg-yellow-400' : 'bg-green-600');
                    @endphp
                    <div class="{{ $colorClass }} h-2.5 rounded-full" style="width: {{ $percent }}%"></div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Utilisateurs</dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">{{ $entreprise->users()->count() }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Départements</dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">{{ $entreprise->departements()->count() }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Sites</dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">{{ $entreprise->sites()->count() }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>

    {{ $this->form }}
</x-filament::page>
