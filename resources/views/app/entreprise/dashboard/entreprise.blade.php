@extends('app.layouts.app')

@section('title', 'GENIUS WORK - Espace Entreprise')

@section('sidebar')
    <!-- Dashboard Link -->
    <a href="{{ route('dashboard.entreprise') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('entreprise.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard" class="h-5 w-5 mr-3"></i>
        <span>Tableau de bord</span>
    </a>
    
    <!-- Employees Link -->
    <div x-data="{ open: {{ request()->routeIs('entreprise.employe*') ? 'true' : 'false' }} }" class="space-y-1">
        <button 
            @click="open = !open" 
            class="sidebar-link w-full flex items-center justify-between px-3 py-2 text-sm {{ request()->routeIs('entreprise.employe*') ? 'active' : '' }}"
        >
            <div class="flex items-center">
                <i data-lucide="users" class="h-5 w-5 mr-3"></i>
                <span>Employés</span>
            </div>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" class="pl-10 space-y-1">
            <a href="{{ route('entreprise.employe.index') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.employe.index') ? 'active' : '' }}">
                Liste des employés²
            </a>
            <a href="{{ route('entreprise.employe.create') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.employe.create') ? 'active' : '' }}">
                Ajouter un employé
            </a>
            <a href="{{ route('entreprise.employe.statistiques') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.employe.statistiques') ? 'active' : '' }}">
                Statistiques
            </a>
            <a href="{{ route('entreprise.employe.organigramme') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.employe.organigramme') ? 'active' : '' }}">
                Organigramme
            </a>
        </div>
    </div>
    
    <!-- Presence Management Link -->
    <div x-data="{ open: {{ request()->routeIs('entreprise.presences*') ? 'true' : 'false' }} }" class="space-y-1">
        <button 
            @click="open = !open" 
            class="sidebar-link w-full flex items-center justify-between px-3 py-2 text-sm {{ request()->routeIs('entreprise.presences*') ? 'active' : '' }}"
        >
            <div class="flex items-center">
                <i data-lucide="clock" class="h-5 w-5 mr-3"></i>
                <span>Pointages</span>
            </div>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" class="pl-10 space-y-1">
            <a href="{{ route('entreprise.presences.index') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.presences.index') ? 'active' : '' }}">
                Suivi des pointages
            </a>
            <a href="{{ route('entreprise.presences.validation') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.presences.validation') ? 'active' : '' }}">
                Validation des pointages
            </a>
            <a href="{{ route('entreprise.presences.rapport') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.presences.rapport') ? 'active' : '' }}">
                Rapports de présence
            </a>
        </div>
    </div>
    
    <!-- Leave Management Link -->
    <div x-data="{ open: {{ request()->routeIs('entreprise.conges*') ? 'true' : 'false' }} }" class="space-y-1">
        <button 
            @click="open = !open" 
            class="sidebar-link w-full flex items-center justify-between px-3 py-2 text-sm {{ request()->routeIs('entreprise.conges*') ? 'active' : '' }}"
        >
            <div class="flex items-center">
                <i data-lucide="calendar" class="h-5 w-5 mr-3"></i>
                <span>Congés</span>
            </div>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" class="pl-10 space-y-1">
            <a href="{{ route('entreprise.conges.index') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.conges.index') ? 'active' : '' }}">
                Demandes de congés
            </a>
            <a href="{{ route('entreprise.conges.validation') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.conges.validation') ? 'active' : '' }}">
                Validation des congés
            </a>
            <a href="{{ route('entreprise.conges.calendrier') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.conges.calendrier') ? 'active' : '' }}">
                Calendrier des congés
            </a>
        </div>
    </div>
    
    <!-- Departments Link -->
    <div x-data="{ open: {{ request()->routeIs('entreprise.departements*') ? 'true' : 'false' }} }" class="space-y-1">
        <button 
            @click="open = !open" 
            class="sidebar-link w-full flex items-center justify-between px-3 py-2 text-sm {{ request()->routeIs('entreprise.departements*') ? 'active' : '' }}"
        >
            <div class="flex items-center">
                <i data-lucide="briefcase" class="h-5 w-5 mr-3"></i>
                <span>Départements</span>
            </div>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" class="pl-10 space-y-1">
            <a href="{{ route('entreprise.departements.index') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.departements.index') ? 'active' : '' }}">
                Liste des départements
            </a>
            <a href="{{ route('entreprise.departements.create') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.departements.create') ? 'active' : '' }}">
                Ajouter un département
            </a>
            <a href="{{ route('entreprise.departements.hierarchie') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.departements.hierarchie') ? 'active' : '' }}">
                Hiérarchie
            </a>
            <a href="{{ route('entreprise.departements.fusion.form') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.departements.fusion.form') ? 'active' : '' }}">
                Fusion de départements
            </a>
        </div>
    </div>
    
    <!-- Sites Link -->
    <div x-data="{ open: {{ request()->routeIs('entreprise.sites*') ? 'true' : 'false' }} }" class="space-y-1">
        <button 
            @click="open = !open" 
            class="sidebar-link w-full flex items-center justify-between px-3 py-2 text-sm {{ request()->routeIs('entreprise.sites*') ? 'active' : '' }}"
        >
            <div class="flex items-center">
                <i data-lucide="map-pin" class="h-5 w-5 mr-3"></i>
                <span>Sites</span>
            </div>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" class="pl-10 space-y-1">
            <a href="{{ route('entreprise.sites.index') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.sites.index') ? 'active' : '' }}">
                Liste des sites
            </a>
            <a href="{{ route('entreprise.sites.create') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.sites.create') ? 'active' : '' }}">
                Ajouter un site
            </a>
            <a href="{{ route('entreprise.sites.carte') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.sites.carte') ? 'active' : '' }}">
                Carte des sites
            </a>
            <a href="{{ route('entreprise.sites.statistiques') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.sites.statistiques') ? 'active' : '' }}">
                Statistiques
            </a>
        </div>
    </div>
    
    <!-- Work Schedules Link -->
    <div x-data="{ open: {{ request()->routeIs('entreprise.horaires*') ? 'true' : 'false' }} }" class="space-y-1">
        <button 
            @click="open = !open" 
            class="sidebar-link w-full flex items-center justify-between px-3 py-2 text-sm {{ request()->routeIs('entreprise.horaires*') ? 'active' : '' }}"
        >
            <div class="flex items-center">
                <i data-lucide="clock" class="h-5 w-5 mr-3"></i>
                <span>Horaires de travail</span>
            </div>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" class="pl-10 space-y-1">
            <a href="{{ route('entreprise.horaires.plages.index') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.horaires.plages.index') ? 'active' : '' }}">
                Plages horaires
            </a>
            <a href="{{ route('entreprise.horaires.planning.index') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.horaires.planning.index') ? 'active' : '' }}">
                Planning
            </a>
        </div>
    </div>
    
    <!-- Reports Link -->
    <div x-data="{ open: {{ request()->routeIs('entreprise.rapports*') ? 'true' : 'false' }} }" class="space-y-1">
        <button 
            @click="open = !open" 
            class="sidebar-link w-full flex items-center justify-between px-3 py-2 text-sm {{ request()->routeIs('entreprise.rapports*') ? 'active' : '' }}"
        >
            <div class="flex items-center">
                <i data-lucide="bar-chart" class="h-5 w-5 mr-3"></i>
                <span>Rapports</span>
            </div>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" class="pl-10 space-y-1">
            <a href="{{ route('entreprise.rapports.presence') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.rapports.presence') ? 'active' : '' }}">
                Rapport de présence
            </a>
            <a href="{{ route('entreprise.rapports.conges') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.rapports.conges') ? 'active' : '' }}">
                Rapport de congés
            </a>
            <a href="{{ route('entreprise.rapports.employes') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.rapports.employes') ? 'active' : '' }}">
                Rapport d'employés
            </a>
            <a href="{{ route('entreprise.rapports.departements') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.rapports.departements') ? 'active' : '' }}">
                Rapport de départements
            </a>
            <a href="{{ route('entreprise.rapports.financier') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.rapports.financier') ? 'active' : '' }}">
                Rapport financier
            </a>
        </div>
    </div>
    
    <!-- Subscription Link -->
    <a href="{{ route('abonnements.dashboard') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('abonnements.dashboard') ? 'active' : '' }}">
        <i data-lucide="credit-card" class="h-5 w-5 mr-3"></i>
        <span>Abonnements</span>
    </a>
    
    <!-- Settings Link -->
    <div x-data="{ open: {{ request()->routeIs('entreprise.parametres*') ? 'true' : 'false' }} }" class="space-y-1">
        <button 
            @click="open = !open" 
            class="sidebar-link w-full flex items-center justify-between px-3 py-2 text-sm {{ request()->routeIs('entreprise.parametres*') ? 'active' : '' }}"
        >
            <div class="flex items-center">
                <i data-lucide="settings" class="h-5 w-5 mr-3"></i>
                <span>Paramètres</span>
            </div>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" class="pl-10 space-y-1">
            <a href="{{ route('entreprise.parametres.general') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.parametres.general') ? 'active' : '' }}">
                Général
            </a>
            <a href="{{ route('entreprise.parametres.notifications') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.parametres.notifications') ? 'active' : '' }}">
                Notifications
            </a>
            <a href="{{ route('entreprise.parametres.securite') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.parametres.securite') ? 'active' : '' }}">
                Sécurité
            </a>
            <a href="{{ route('entreprise.parametres.integrations') }}" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.parametres.integrations') ? 'active' : '' }}">
                Intégrations
            </a>
        </div>
    </div>
    
    <!-- Company Profile Link -->
    <div x-data="{ open: {{ request()->routeIs('entreprise.profil*') ? 'true' : 'false' }} }" class="space-y-1">
        <button 
            @click="open = !open" 
            class="sidebar-link w-full flex items-center justify-between px-3 py-2 text-sm {{ request()->routeIs('entreprise.profil*') ? 'active' : '' }}"
        >
            <div class="flex items-center">
                <i data-lucide="building" class="h-5 w-5 mr-3"></i>
                <span>Profil d'entreprise</span>
            </div>
            <i data-lucide="chevron-down" class="h-4 w-4 transition-transform" :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" class="pl-10 space-y-1">
            <a href="" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.profil.index') ? 'active' : '' }}">
                Informations générales
            </a>
            <a href="" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.profil.logo') ? 'active' : '' }}">
                Logo et branding
            </a>
            <a href="" class="sidebar-link block px-3 py-2 text-sm {{ request()->routeIs('entreprise.profil.documents') ? 'active' : '' }}">
                Documents légaux
            </a>
        </div>
    </div>
    
    <!-- Notifications Link -->
    <a href="{{ route('entreprise.notifications') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('entreprise.notifications*') ? 'active' : '' }}">
        <i data-lucide="bell" class="h-5 w-5 mr-3"></i>
        <span>Notifications</span>
    </a>
@endsection
