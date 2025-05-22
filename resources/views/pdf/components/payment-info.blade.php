<div class="grid grid-cols-2 gap-8 mt-12">
    <div class="section-card">
        <div class="font-bold text-primary mb-3 flex items-center gap-2">
            <span>💳</span> Mode de paiement
        </div>
        <div class="text-base">
            {{ ucfirst($facture->mode_paiement) }}
            @if($facture->reference_paiement)
            <div class="mt-2 p-2 bg-white rounded-lg border">
                Référence: {{ $facture->reference_paiement }}
            </div>
            @endif
        </div>
    </div>

    <div class="section-card">
        <div class="font-bold text-primary mb-3 flex items-center gap-2">
            <span>🏦</span> Coordonnées bancaires
        </div>
        <div class="space-y-2 text-base">
            <div>Banque: GENIUS WORK Finance</div>
            <div class="p-2 bg-white rounded-lg border font-medium">
                IBAN: FR76 1234 5678 9012 3456 7890 123
            </div>
            <div class="p-2 bg-white rounded-lg border font-medium">
                BIC: GWORKFRPP
            </div>
        </div>
    </div>
</div>
