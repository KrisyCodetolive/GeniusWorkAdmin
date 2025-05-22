@extends('app.entreprise.dashboard.entreprise')

@section('title', 'Détails de l\'employeur')

@section('content')
<div class="container mx-auto px-4 py-6">
    <x-employeur.header :employeur="$employeur" />

    <!-- Première rangée -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Colonne de gauche -->
        <div class="space-y-6">
            <x-employeur.profile-card :employeur="$employeur" />
            <x-employeur.qr-code :employeur="$employeur" />
            <x-employeur.quick-actions :employeur="$employeur" />
        </div>
        
        <!-- Colonne centrale et droite -->
        <div class="md:col-span-2 space-y-6">
            <!-- Informations détaillées -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-info-circle mr-2"></i>Informations détaillées
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-employeur.personal-info :employeur="$employeur" />
                        <x-employeur.professional-info :employeur="$employeur" :typesContrat="$typesContrat" />
                    </div>
                    
                    <x-employeur.additional-info :employeur="$employeur" />
                </div>
            </div>
            
            <!-- Statistiques et Performance -->
            <x-employeur.stats :employeur="$employeur" />
            
            <!-- Structure Organisationnelle -->
            <x-employeur.org-structure :employeur="$employeur" />
        </div>
    </div>
    
    <!-- Deuxième rangée -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Documents -->
        <x-employeur.documents :employeur="$employeur" />
        
        <!-- Historique -->
        <x-employeur.history :historique="$historique" />
    </div>
</div>
@endsection