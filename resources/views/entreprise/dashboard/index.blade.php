@extends('layouts.app')

@section('title', 'Tableau de bord Entreprise')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $title }}</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Statistiques de l'entreprise -->
            <div class="bg-blue-50 rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-blue-700 mb-2">Statistiques de l'Entreprise</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Employés</span>
                        <span class="font-medium">{{ \App\Models\User::where('entreprise_id', Auth::user()->entreprise_id)->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Présences ce mois</span>
                        <span class="font-medium">120</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taux de présence</span>
                        <span class="font-medium">95%</span>
                    </div>
                </div>
            </div>
            
            <!-- Abonnement -->
            <div class="bg-green-50 rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-green-700 mb-2">Votre Abonnement</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Plan</span>
                        <span class="font-medium">Premium</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Statut</span>
                        <span class="font-medium text-green-600">Actif</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Prochaine facturation</span>
                        <span class="font-medium">15/04/2025</span>
                    </div>
                    <div class="mt-4">
                        <a href="#" class="text-blue-600 hover:text-blue-800">Gérer mon abonnement</a>
                    </div>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="bg-purple-50 rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-purple-700 mb-2">Actions Rapides</h2>
                <div class="space-y-3">
                    <a href="#" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded text-center">
                        Gérer les employés
                    </a>
                    <a href="#" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded text-center">
                        Valider les présences
                    </a>
                    <a href="#" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded text-center">
                        Voir les factures
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Activité récente -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Activité Récente</h2>
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <div class="p-4 border-b">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-blue-100 rounded-full p-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Pierre Durand a pointé à 8:30</p>
                            <p class="text-xs text-gray-500">Aujourd'hui à 08:30</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-b">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-green-100 rounded-full p-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Validation de 5 présences</p>
                            <p class="text-xs text-gray-500">Hier à 17:45</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-b">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-full p-2">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Nouvelle demande de congé</p>
                            <p class="text-xs text-gray-500">03/03/2025 à 14:20</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 text-center">
                <a href="#" class="text-blue-600 hover:text-blue-800">Voir toutes les activités</a>
            </div>
        </div>
    </div>
</div>
@endsection
