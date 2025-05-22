             <!-- Informations sur l'entreprise et le site -->
<div class="w-full h-full">
    <div class="flex flex-col h-full rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-all duration-300 border border-blue-100/50">
        <!-- En-tête avec titre -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-3 text-white">
            <h3 class="text-lg font-semibold flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Informations de l'Entreprise
            </h3>
        </div>

        <!-- Contenu principal -->
        <div class="flex-1 flex flex-col overflow-y-auto custom-scrollbar">
            <!-- Informations de l'entreprise -->
            <div class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-purple-50 border-b border-blue-100/50">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-4 shadow-inner">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center flex-wrap">
                        <span class="text-sm font-medium text-gray-500 bg-blue-50 px-2 py-0.5 rounded-full mr-2">{{ $site->entreprise->nom ?? 'Entreprise' }}</span>
                        @if($site->entreprise->statut === 'actif')
                            <span class="text-green-500" title="Entreprise active">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center mt-1">
                        <span class="text-base font-semibold text-gray-800">{{ $site->nom ?? 'Site' }}</span>
                        @if($site->statut === 'actif')
                            <span class="ml-2 px-1.5 py-0.5 bg-green-100 text-green-800 text-xs rounded">Actif</span>
                        @else
                            <span class="ml-2 px-1.5 py-0.5 bg-red-100 text-red-800 text-xs rounded">{{ ucfirst($site->statut ?? 'Inactif') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Adresse du site -->
            <div class="p-4 bg-white border-b border-blue-100/50">
                <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Localisation
                </h4>
                <div class="ml-5">
                    <div class="text-gray-700">{{ $site->adresse ?? 'Adresse non spécifiée' }}</div>
                    @if($site->code_postal || $site->ville)
                        <div class="text-gray-600 flex items-center font-medium">
                            {{ $site->code_postal ?? '' }} {{ $site->ville ?? '' }}{{ $site->pays ? ', '.$site->pays : '' }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- QR Code du site -->
            <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 flex flex-col items-center justify-center flex-grow">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    QR Code d'accès
                </h4>
                
                @if($site->qr_token ?? false)
                    <div class="relative group/qr mb-2 transform transition-transform duration-300 hover:scale-105">
                        <div class="w-48 h-48 bg-white p-3 rounded-lg shadow-md overflow-hidden">
                            @php
                                try {
                                    $qrUrl = url("/pointage/web/{$site->qr_token}");
                                    $qrName = "Site-{$site->id}-QR";
                                    
                                    // Récupérer l'ID du QR code ou en créer un nouveau
                                    $geniusTools = app(\App\Services\GeniusToolsService::class);
                                    $qrCodeId = $geniusTools->getOrCreateQrCode($qrUrl, $qrName);
                                    
                                    // Récupérer les informations du QR code
                                    $qrCodeData = $geniusTools->getQrCode($qrCodeId);
                                    $qrCodeImageUrl = $qrCodeData['data']['qr_code'] ?? null;
                                } catch (\Exception $e) {
                                    $qrCodeImageUrl = null;
                                }
                            @endphp
                            
                            @if($qrCodeImageUrl)
                                <img src="{{ $qrCodeImageUrl }}" alt="QR Code du site" class="w-full h-full object-contain">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400 text-xs">
                                    QR Code non disponible
                                </div>
                            @endif
                        </div>
                        
                        <!-- Overlay au survol -->
                        <div class="absolute inset-0 bg-blue-500/80 rounded-lg opacity-0 group-hover/qr:opacity-100 flex items-center justify-center transition-opacity duration-300">
                            <a href="{{ url("/pointage/web/{$site->qr_token}") }}" target="_blank" class="text-white text-xs font-medium">
                                <svg class="w-6 h-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Voir
                            </a>
                        </div>
                    </div>
                    
                    <div class="text-xs text-gray-500">
                        <div class="flex items-center justify-center mb-1">
                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Expire: {{ $site->qr_generated_at ? \Carbon\Carbon::parse($site->qr_generated_at)->format('d/m/Y H:i') : 'N/A' }}
                        </div>
                    </div>
                    
                    <div class="text-xs text-center text-gray-600 mt-2 bg-white/50 rounded-full px-3 py-1 shadow-sm">
                        <p>Scannez ce QR code pour enregistrer votre présence</p>
                    </div>
                @else
                    <div class="text-center p-4">
                        <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">QR Code non disponible pour ce site</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>