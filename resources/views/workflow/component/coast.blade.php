<div class="mb-8">
    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
        <svg class="h-6 w-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Détails de votre abonnement
    </h3>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Entête avec forfait -->
        <div class="bg-indigo-50 p-4 border-b border-indigo-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span class="text-indigo-900 font-medium">Forfait {{ $forfait }}</span>
                </div>
                <span class="text-sm text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full">Mensuel</span>
            </div>
        </div>
        
        <!-- Détails des coûts -->
        <div class="p-4 space-y-4">
            <!-- Coût fixe -->
            <div class="flex justify-between items-center py-3 border-b border-gray-100 hover:bg-gray-50 px-2 rounded transition duration-200">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span class="text-gray-700">Coût fixe ({{ $forfait }})</span>
                </div>
                <span class="font-medium text-gray-900" x-text="formatNumber(coutFixe) + ' FCFA'"></span>
            </div>
            
            <!-- Coût par utilisateur -->
            <div class="flex justify-between items-center py-3 border-b border-gray-100 hover:bg-gray-50 px-2 rounded transition duration-200">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <div class="flex flex-col">
                        <span class="text-gray-700">Coût par utilisateur</span>
                        <span class="text-xs text-gray-500 mt-1">{{ $planDetails['cout_par_employe'] ?? 100 }} FCFA par utilisateur</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-medium text-gray-900" x-text="formatNumber(coutUtilisateurs) + ' FCFA'"></div>
                    <div class="text-xs text-gray-500 mt-1">
                        <span x-text="companySize"></span> utilisateurs
                    </div>
                </div>
            </div>
            
            <!-- Total mensuel -->
            <div class="flex justify-between items-center py-4 mt-2 border-t-2 border-indigo-100">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-lg font-bold text-gray-900">Total mensuel</span>
                </div>
                <span class="text-lg font-bold text-indigo-700" x-text="formatNumber(coutTotal) + ' FCFA'"></span>
            </div>
            
           
        </div>
    </div>
</div>