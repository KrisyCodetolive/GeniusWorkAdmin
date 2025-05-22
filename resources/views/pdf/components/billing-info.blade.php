<div class="grid grid-cols-2 gap-8 mt-12">
    <div>
        <div class="section-title">
            <span>👤</span> Facturé à
        </div>
        <div class="section-card">
            <div class="entity-name">{{ $facture->entreprise->nom }}</div>
            <div class="space-y-2 text-base">
                <div>{{ $facture->entreprise->adresse }}</div>
                <div>{{ $facture->entreprise->code_postal }} {{ $facture->entreprise->ville }}</div>
                <div>{{ $facture->entreprise->pays }}</div>
                @if($facture->entreprise->telephone)
                <div class="flex items-center gap-2">
                    <span>📞</span> {{ $facture->entreprise->telephone }}
                </div>
                @endif
                @if($facture->entreprise->email)
                <div class="flex items-center gap-2">
                    <span>✉️</span> {{ $facture->entreprise->email }}
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div>
        <div class="section-title">
            <span>🏢</span> Émis par
        </div>
        <div class="section-card">
            <div class="entity-name">GENIUS WORK</div>
            <div class="space-y-2 text-base">
                <div>123 Rue de l'Innovation</div>
                <div>75000 Paris</div>
                <div>France</div>
                <div class="flex items-center gap-2">
                    <span>📞</span> +33 1 23 45 67 89
                </div>
                <div class="flex items-center gap-2">
                    <span>✉️</span> contact@GENIUS WORK.com
                </div>
            </div>
        </div>
    </div>
</div>
