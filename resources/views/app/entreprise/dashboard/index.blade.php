@extends('app.entreprise.dashboard.entreprise')

@section('header', 'Tableau de bord administrateur')

@section('content')


    <!-- Welcome Alert -->
    <div class="mb-6">
        <x-app.components.alert type="info" dismissible>
            <div>
                <h3 class="font-medium">Bienvenue sur le tableau de bord administrateur GENIUS WORK!</h3>
                <p class="mt-1 text-sm">Gérez les employés, suivez les présences et consultez les rapports de votre entreprise.</p>
            </div>
        </x-app.components.alert>
    </div>
    
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <x-app.components.stat-card
            title="Employés actifs"
            value="48"
            icon="users"
            trend="ce mois"
            trendValue="+3"
            trendUp="true"
        />
        
        <x-app.components.stat-card
            title="Taux de présence"
            value="94%"
            icon="check-circle"
            color="green"
            trend="vs mois dernier"
            trendValue="+2.5%"
            trendUp="true"
        />
        
        <x-app.components.stat-card
            title="Demandes de congés"
            value="5"
            icon="calendar"
            color="yellow"
            trend="en attente"
        />
        
        <x-app.components.stat-card
            title="Heures travaillées"
            value="3,842h"
            icon="clock"
            color="blue"
            trend="ce mois"
        />
    </div>
    
    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Attendance Overview -->
        <div class="lg:col-span-2">
            <x-app.components.card title="Aperçu des présences" icon="bar-chart" class="h-full">
                <div class="h-80 flex items-center justify-center bg-gray-50 rounded-lg">
                    <p class="text-gray-500">Graphique des présences (à implémenter)</p>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mt-6">
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-600">Présents</p>
                        <p class="text-xl font-semibold text-green-600">42</p>
                    </div>
                    
                    <div class="bg-red-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-600">Absents</p>
                        <p class="text-xl font-semibold text-red-600">3</p>
                    </div>
                    
                    <div class="bg-yellow-50 rounded-lg p-3 text-center">
                        <p class="text-sm text-gray-600">En congé</p>
                        <p class="text-xl font-semibold text-yellow-600">3</p>
                    </div>
                </div>
            </x-app.components.card>
        </div>
        
        <!-- Recent Activity -->
        <div>
            <x-app.components.card title="Activité récente" icon="activity" class="h-full">
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                            <i data-lucide="user-plus" class="h-5 w-5 text-green-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Nouvel employé ajouté</p>
                            <p class="text-sm text-gray-500">Aujourd'hui à 10:15</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                            <i data-lucide="calendar" class="h-5 w-5 text-indigo-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Demande de congé approuvée</p>
                            <p class="text-sm text-gray-500">Hier à 14:30</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                            <i data-lucide="alert-triangle" class="h-5 w-5 text-yellow-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Retard signalé</p>
                            <p class="text-sm text-gray-500">Il y a 2 jours</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <i data-lucide="file-text" class="h-5 w-5 text-blue-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Rapport mensuel généré</p>
                            <p class="text-sm text-gray-500">Il y a 3 jours</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <x-app.components.button type="link" href="#">
                        Voir toute l'activité
                    </x-app.components.button>
                </div>
            </x-app.components.card>
        </div>
    </div>
    
    <!-- Recent Employees & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Employees -->
        <div class="lg:col-span-2">
            <x-app.components.card title="Employés récents" icon="users" class="h-full">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poste</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'embauche</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-medium">JD</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">Jean Dupont</div>
                                            <div class="text-sm text-gray-500">jean.dupont@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Développeur Web</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01/03/2025</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-medium">MK</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">Marie Koné</div>
                                            <div class="text-sm text-gray-500">marie.kone@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Designer UX</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15/02/2025</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-medium">PD</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">Pierre Diallo</div>
                                            <div class="text-sm text-gray-500">pierre.diallo@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Chef de projet</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">En congé</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01/02/2025</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6">
                    <x-app.components.button type="link" href="{{ route('entreprise.employe.index') }}">
                        Voir tous les employés
                    </x-app.components.button>
                </div>
            </x-app.components.card>
        </div>
        
        <!-- Quick Actions -->
        <div>
            <x-app.components.card title="Actions rapides" icon="zap" class="h-full">
                <div class="space-y-3">
                    <x-app.components.button type="primary" icon="user-plus" class="w-full justify-start" href="{{ route('entreprise.employe.create') }}">
                        Ajouter un employé
                    </x-app.components.button>
                    
                    <x-app.components.button type="outline-primary" icon="file-text" class="w-full justify-start" href="{{ route('entreprise.rapports') }}">
                        Générer un rapport
                    </x-app.components.button>
                    
                    <x-app.components.button type="outline-primary" icon="calendar" class="w-full justify-start" href="{{ route('entreprise.conges.index') }}">
                        Gérer les congés
                    </x-app.components.button>
                    
                    <x-app.components.button type="outline" icon="settings" class="w-full justify-start" href="{{ route('entreprise.parametres') }}">
                        Paramètres
                    </x-app.components.button>
                </div>
                
                <div class="mt-6 pt-6 border-t">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Abonnement actif</h4>
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-indigo-800">Forfait Enterprise</span>
                            <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2 py-1 rounded">Actif</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Prochain paiement: 15/03/2025</p>
                        <div class="flex justify-end">
                            <x-app.components.button type="link" size="sm" href="{{ route('abonnements.dashboard') }}">
                                Gérer
                            </x-app.components.button>
                        </div>
                    </div>
                </div>
            </x-app.components.card>
        </div>
    </div>
@endsection
