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
                    {{ $facture->date_facturation->format('d/m/Y') }}
                </div>
            </div>
            
            <div class="flex">
                <div class="text-gray-600 w-1/3">Date d'échéance:</div>
                <div class="font-medium">
                    {{ $facture->date_echeance->format('d/m/Y') }}
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
</div>
