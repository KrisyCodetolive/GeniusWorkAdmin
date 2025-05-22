@extends('app.layouts.app')

@section('title', 'GENIUS WORK - Administration Système')

@section('sidebar')
    <!-- Dashboard Link -->
    <a href="{{ route('superadmin.dashboard') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard" class="h-5 w-5 mr-3"></i>
        <span>Tableau de bord</span>
    </a>
    
    <!-- Companies Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Entreprises</h6>
    </div>
    
    <!-- Companies Link -->
    <a href="{{ route('superadmin.entreprises.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.entreprises*') ? 'active' : '' }}">
        <i data-lucide="building" class="h-5 w-5 mr-3"></i>
        <span>Gestion des entreprises</span>
    </a>
    
    <!-- Subscriptions Link -->
    <a href="{{ route('superadmin.abonnements.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.abonnements*') ? 'active' : '' }}">
        <i data-lucide="credit-card" class="h-5 w-5 mr-3"></i>
        <span>Abonnements</span>
    </a>
    
    <!-- Billing Link -->
    <a href="{{ route('superadmin.facturation.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.facturation*') ? 'active' : '' }}">
        <i data-lucide="receipt" class="h-5 w-5 mr-3"></i>
        <span>Facturation</span>
    </a>
    
    <!-- Users Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilisateurs</h6>
    </div>
    
    <!-- All Users Link -->
    <a href="{{ route('superadmin.utilisateurs.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.utilisateurs*') ? 'active' : '' }}">
        <i data-lucide="users" class="h-5 w-5 mr-3"></i>
        <span>Tous les utilisateurs</span>
    </a>
    
    <!-- Admins Link -->
    <a href="{{ route('superadmin.administrateurs.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.administrateurs*') ? 'active' : '' }}">
        <i data-lucide="shield" class="h-5 w-5 mr-3"></i>
        <span>Administrateurs</span>
    </a>
    
    <!-- Roles & Permissions Link -->
    <a href="{{ route('superadmin.roles.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.roles*') ? 'active' : '' }}">
        <i data-lucide="key" class="h-5 w-5 mr-3"></i>
        <span>Rôles & Permissions</span>
    </a>
    
    <!-- System Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Système</h6>
    </div>
    
    <!-- System Settings Link -->
    <a href="{{ route('superadmin.parametres.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.parametres*') ? 'active' : '' }}">
        <i data-lucide="settings" class="h-5 w-5 mr-3"></i>
        <span>Paramètres système</span>
    </a>
    
    <!-- Logs Link -->
    <a href="{{ route('superadmin.logs.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.logs*') ? 'active' : '' }}">
        <i data-lucide="file-text" class="h-5 w-5 mr-3"></i>
        <span>Journaux système</span>
    </a>
    
    <!-- Backups Link -->
    <a href="{{ route('superadmin.sauvegardes.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.sauvegardes*') ? 'active' : '' }}">
        <i data-lucide="database" class="h-5 w-5 mr-3"></i>
        <span>Sauvegardes</span>
    </a>
    
    <!-- Analytics Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Analyses</h6>
    </div>
    
    <!-- Global Stats Link -->
    <a href="{{ route('superadmin.statistiques.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.statistiques*') ? 'active' : '' }}">
        <i data-lucide="bar-chart-2" class="h-5 w-5 mr-3"></i>
        <span>Statistiques globales</span>
    </a>
    
    <!-- Usage Reports Link -->
    <a href="{{ route('superadmin.rapports.utilisation') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.rapports.utilisation*') ? 'active' : '' }}">
        <i data-lucide="activity" class="h-5 w-5 mr-3"></i>
        <span>Rapports d'utilisation</span>
    </a>
    
    <!-- Revenue Reports Link -->
    <a href="{{ route('superadmin.rapports.revenus') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('superadmin.rapports.revenus*') ? 'active' : '' }}">
        <i data-lucide="trending-up" class="h-5 w-5 mr-3"></i>
        <span>Rapports de revenus</span>
    </a>
@endsection
