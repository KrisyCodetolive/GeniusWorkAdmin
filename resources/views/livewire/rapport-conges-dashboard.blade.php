<div>
    <!-- Cartes de statistiques principales -->
    @include('livewire.partials.conges.statistiques-cartes')

    <!-- Graphiques principaux -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        <!-- Graphique des congés par mois -->
        @include('livewire.partials.conges.graphique-par-mois')

        <!-- Graphique des congés par type -->
        @include('livewire.partials.conges.graphique-par-type')
    </div>

    <!-- Graphiques secondaires -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        <!-- Graphique des congés par statut -->
        @include('livewire.partials.conges.graphique-par-statut')

        <!-- Graphique des top employés -->
        @include('livewire.partials.conges.graphique-top-employes')
    </div>

    <!-- Tableau des employeurs -->
    @if(count($stats['conges_par_employeur']) > 0)
        @include('livewire.partials.conges.tableau-employeurs')
    @endif

    <!-- Scripts pour les graphiques -->
    @include('livewire.partials.conges.scripts-graphiques')
</div>
