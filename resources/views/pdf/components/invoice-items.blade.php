<div class="table-container">
    <table class="invoice-table">
        <thead>
            <tr>
                <th width="40%">Description</th>
                <th width="25%">Période</th>
                <th width="10%" class="text-right">Prix HT</th>
                <th width="10%" class="text-right">TVA</th>
                <th width="15%" class="text-right">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="font-semibold text-lg">{{ $facture->abonnement->planAbonnement->nom }}</div>
                    <div class="text-xs text-secondary mt-2">
                        {{ $facture->abonnement->planAbonnement->description }}
                    </div>
                </td>
                <td>
                    <div class="text-xs text-secondary italic flex items-center gap-2">
                        <span>📅</span> Du {{ $facture->date_facturation->format('d/m/Y') }} au {{ $facture->date_echeance->format('d/m/Y') }}
                    </div>
                </td>
                <td class="text-right font-medium">{{ number_format($facture->montant_ht, 2, ',', ' ') }} €</td>
                <td class="text-right font-medium">{{ $facture->taux_tva }}%</td>
                <td class="text-right font-medium">{{ number_format($facture->montant_ttc, 2, ',', ' ') }} €</td>
            </tr>
            
            @if(isset($facture->fraisUsages) && $facture->fraisUsages->count() > 0)
                @foreach($facture->fraisUsages as $frais)
                <tr>
                    <td>
                        <div class="font-semibold text-lg">{{ $frais->type_frais }}</div>
                        <div class="text-xs text-secondary mt-2">
                            {{ $frais->description }}
                        </div>
                    </td>
                    <td>
                        <div class="text-xs text-secondary italic flex items-center gap-2">
                            <span>📅</span> Du {{ $frais->periode_debut->format('d/m/Y') }} au {{ $frais->periode_fin->format('d/m/Y') }}
                        </div>
                    </td>
                    <td class="text-right font-medium">{{ number_format($frais->montant_ht, 2, ',', ' ') }} €</td>
                    <td class="text-right font-medium">{{ $frais->taux_tva }}%</td>
                    <td class="text-right font-medium">{{ number_format($frais->montant_ttc, 2, ',', ' ') }} €</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
