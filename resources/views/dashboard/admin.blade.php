@extends('layouts.app')

@section('title', 'Tableau de bord Administrateur')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $title }}</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Statistiques -->
            <div class="bg-blue-50 rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-blue-700 mb-2">Statistiques Globales</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Utilisateurs</span>
                        <span class="font-medium">{{ \App\Models\User::count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Entreprises</span>
                        <span class="font-medium">{{ \App\Models\Entreprise::count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Abonnements actifs</span>
                        <span class="font-medium">{{ \App\Models\Abonnement::where('statut', 'actif')->count() }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Dernières activités -->
            <div class="bg-green-50 rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-green-700 mb-2">Dernières Activités</h2>
                <div class="space-y-2">
                    <p class="text-gray-600">Affichage des dernières activités du système</p>
                    <!-- Liste des activités récentes -->
                    <div class="mt-4 space-y-2">
                        <div class="border-l-4 border-green-500 pl-2">
                            <p class="text-sm">Nouvel utilisateur inscrit</p>
                            <p class="text-xs text-gray-500">Il y a 2 heures</p>
                        </div>
                        <div class="border-l-4 border-green-500 pl-2">
                            <p class="text-sm">Nouvel abonnement souscrit</p>
                            <p class="text-xs text-gray-500">Il y a 5 heures</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="bg-purple-50 rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-purple-700 mb-2">Actions Rapides</h2>
                <div class="space-y-3">
                    <a href="#" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded text-center">
                        Gérer les utilisateurs
                    </a>
                    <a href="#" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded text-center">
                        Gérer les entreprises
                    </a>
                    <a href="#" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded text-center">
                        Voir les paiements
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Tableau des derniers utilisateurs -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Derniers Utilisateurs Inscrits</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left">Nom</th>
                            <th class="py-2 px-4 border-b text-left">Email</th>
                            <th class="py-2 px-4 border-b text-left">Rôle</th>
                            <th class="py-2 px-4 border-b text-left">Date d'inscription</th>
                            <th class="py-2 px-4 border-b text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Exemple de données -->
                        <tr>
                            <td class="py-2 px-4 border-b">Jean Dupont</td>
                            <td class="py-2 px-4 border-b">jean@example.com</td>
                            <td class="py-2 px-4 border-b">Entreprise</td>
                            <td class="py-2 px-4 border-b">01/03/2025</td>
                            <td class="py-2 px-4 border-b">
                                <a href="#" class="text-blue-600 hover:text-blue-800">Voir</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 px-4 border-b">Marie Martin</td>
                            <td class="py-2 px-4 border-b">marie@example.com</td>
                            <td class="py-2 px-4 border-b">Employé</td>
                            <td class="py-2 px-4 border-b">28/02/2025</td>
                            <td class="py-2 px-4 border-b">
                                <a href="#" class="text-blue-600 hover:text-blue-800">Voir</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
