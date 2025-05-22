<div class="invoice-header">
    <!-- En-tête avec le logo et le titre -->
    <div class="bg-indigo-600 text-white p-4 rounded-t-lg mb-1">
        <div class="flex items-center">
            <div class="text-2xl font-bold mr-2">€</div>
            <div class="text-2xl font-bold">GENIUS WORK</div>
        </div>
    </div>

    <!-- Sous-titre -->
    <div class="text-gray-600 text-sm italic px-2 py-1 mb-4">
        Votre solution complète de gestion d'entreprise
    </div>

    <!-- Bloc d'informations de la facture -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="text-center text-indigo-600 text-xl font-bold mb-4">
            Facture #{{ $facture->numero_facture }}
        </div>
        
        <div class="grid grid-cols-1 gap-2">
            <div class="flex">
                <div class="text-gray-600 w-1/3">Date d'émission:</div>
                <div class="font-medium">
                    ? {{ $facture->date_facturation->format('d/m/Y') }}
                </div>
            </div>
            
            <div class="flex">
                <div class="text-gray-600 w-1/3">Date d'échéance:</div>
                <div class="font-medium">
                    ? {{ $facture->date_echeance->format('d/m/Y') }}
                </div>
            </div>
            
            <div class="flex">
                <div class="text-gray-600 w-1/3">Statut:</div>
                <div>
                    <span class="px-2 py-1 rounded-full text-xs {{ $facture->statut === 'payee' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($facture->statut) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section "Facturé à" -->
    <div class="text-indigo-600 text-sm font-medium mb-2">? FACTURE À</div>
    <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="text-indigo-600 font-bold text-lg mb-2">
            {{ $facture->client->nom ?? 'YOLEN\'S LEADER' }}
        </div>
        
        <div class="text-gray-700 mb-1">
            N° CC : {{ $facture->client->numero_client ?? '2229053H +225 07 89 60 61 20' }}
        </div>
        
        <div class="text-gray-700 mb-1">
            {{ $facture->client->pays ?? 'France' }}
        </div>
        
        <div class="text-gray-700 mb-1">
            ? {{ $facture->client->telephone ?? '+2250789606120' }}
        </div>
        
        <div class="text-gray-700">
            ?? {{ $facture->client->email ?? 'jeremie@genius.ci' }}
        </div>
    </div>

    <!-- Section "Émis par" -->
    <div class="text-indigo-600 text-sm font-medium mb-2">? ÉMIS PAR</div>
    <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="text-indigo-600 font-bold text-lg mb-2">
            GENIUS WORK
        </div>
        
        <div class="text-gray-700 mb-1">
            {{ $facture->entreprise->adresse ?? '123 Rue de l\'Innovation' }}
        </div>
        
        <div class="text-gray-700 mb-1">
            {{ $facture->entreprise->code_postal ?? '75000' }} {{ $facture->entreprise->ville ?? 'Paris' }}
        </div>
        
        <div class="text-gray-700 mb-1">
            {{ $facture->entreprise->pays ?? 'France' }}
        </div>
        
        <div class="text-gray-700 mb-1">
            ? {{ $facture->entreprise->telephone ?? '+33 1 23 45 67 89' }}
        </div>
        
        <div class="text-gray-700">
            ?? {{ $facture->entreprise->email ?? 'contact@GENIUS WORK.com' }}
        </div>
    </div>
</div>
