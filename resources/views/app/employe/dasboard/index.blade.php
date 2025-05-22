@extends('app.layouts.employe')

@section('header', 'Tableau de bord')

@section('content')
    <!-- Welcome Alert -->
    <div class="mb-6">
        <x-app.components.alert type="info" dismissible>
            <div>
                <h3 class="font-medium">Bienvenue sur votre tableau de bord GENIUS WORK!</h3>
                <p class="mt-1 text-sm">Consultez vos pointages, demandes de congés et gérez votre profil facilement.</p>
            </div>
        </x-app.components.alert>
    </div>
    
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-app.components.stat-card
            title="Heures travaillées ce mois"
            value="152h 30m"
            icon="clock"
            trend="vs mois dernier"
            trendValue="+3.2%"
            trendUp="true"
        />
        
        <x-app.components.stat-card
            title="Jours de congés restants"
            value="12"
            icon="calendar"
            color="green"
        />
        
        <x-app.components.stat-card
            title="Retards ce mois"
            value="2"
            icon="alert-circle"
            color="yellow"
            trend="vs mois dernier"
            trendValue="-50%"
            trendUp="true"
        />
    </div>
    
    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity -->
        <div class="lg:col-span-2">
            <x-app.components.card title="Activité récente" icon="activity" class="h-full">
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                            <i data-lucide="log-in" class="h-5 w-5 text-green-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Pointage d'entrée</p>
                            <p class="text-sm text-gray-500">Aujourd'hui à 08:02</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                            <i data-lucide="log-out" class="h-5 w-5 text-red-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Pointage de sortie</p>
                            <p class="text-sm text-gray-500">Hier à 17:30</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                            <i data-lucide="calendar" class="h-5 w-5 text-indigo-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Demande de congé approuvée</p>
                            <p class="text-sm text-gray-500">Il y a 2 jours</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                            <i data-lucide="log-in" class="h-5 w-5 text-green-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Pointage d'entrée</p>
                            <p class="text-sm text-gray-500">Il y a 2 jours à 08:15</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                            <i data-lucide="log-out" class="h-5 w-5 text-red-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Pointage de sortie</p>
                            <p class="text-sm text-gray-500">Il y a 3 jours à 17:45</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <x-app.components.button type="link" href="{{ route('employe.presences') }}">
                        Voir toute l'activité
                    </x-app.components.button>
                </div>
            </x-app.components.card>
        </div>
        
        <!-- Quick Actions & Upcoming -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <x-app.components.card title="Actions rapides" icon="zap" class="h-auto">
                <div class="space-y-3">
                    <x-app.components.button type="primary" icon="log-in" class="w-full justify-start">
                        Pointer mon entrée
                    </x-app.components.button>
                    
                    <x-app.components.button type="outline-primary" icon="log-out" class="w-full justify-start">
                        Pointer ma sortie
                    </x-app.components.button>
                    
                    <x-app.components.button type="outline" icon="calendar" class="w-full justify-start">
                        Demander un congé
                    </x-app.components.button>
                </div>
            </x-app.components.card>
            
            <!-- Upcoming -->
            <x-app.components.card title="À venir" icon="calendar" class="h-auto">
                <div class="space-y-4">
                    <div class="flex items-center p-3 bg-indigo-50 rounded-lg">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                            <i data-lucide="calendar" class="h-5 w-5 text-indigo-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Congé annuel</p>
                            <p class="text-sm text-gray-500">Du 15 au 20 mars 2025</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-yellow-50 rounded-lg">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                            <i data-lucide="alert-triangle" class="h-5 w-5 text-yellow-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">Réunion d'équipe</p>
                            <p class="text-sm text-gray-500">10 mars 2025, 10:00</p>
                        </div>
                    </div>
                </div>
            </x-app.components.card>
        </div>
    </div>
@endsection
