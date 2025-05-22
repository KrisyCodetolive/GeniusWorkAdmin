<div class="flex justify-end mt-8">
    <table class="summary-table">
        <tbody>
            <tr>
                <th>Total HT</th>
                <td>{{ number_format($facture->montant_ht, 2, ',', ' ') }} €</td>
            </tr>
            <tr>
                <th>TVA ({{ $facture->taux_tva }}%)</th>
                <td>{{ number_format($facture->montant_ttc - $facture->montant_ht, 2, ',', ' ') }} €</td>
            </tr>
            <tr class="total-row">
                <th>Total TTC</th>
                <td>{{ number_format($facture->montant_ttc, 2, ',', ' ') }} €</td>
            </tr>
        </tbody>
    </table>
</div>
