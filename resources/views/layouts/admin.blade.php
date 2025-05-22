@extends('app.layouts.app')

@section('title', 'GENIUS WORK - Administration Entreprise')

@section('sidebar')
    <!-- Dashboard Link -->
    <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard" class="h-5 w-5 mr-3"></i>
        <span>Tableau de bord</span>
    </a>
    
    <!-- Employees Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Gestion des employés</h6>
    </div>
    
    <!-- Employees Link -->
    <a href="{{ route('admin.employes.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.employes*') ? 'active' : '' }}">
        <i data-lucide="users" class="h-5 w-5 mr-3"></i>
        <span>Employés</span>
    </a>
    
    <!-- Departments Link -->
    <a href="{{ route('admin.departements.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.departements*') ? 'active' : '' }}">
        <i data-lucide="network" class="h-5 w-5 mr-3"></i>
        <span>Départements</span>
    </a>
    
    <!-- Sites Link -->
    <a href="{{ route('admin.sites.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.sites*') ? 'active' : '' }}">
        <i data-lucide="map-pin" class="h-5 w-5 mr-3"></i>
        <span>Sites</span>
    </a>
    
    <!-- Attendance Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Gestion de présence</h6>
    </div>
    
    <!-- Attendance Link -->
    <a href="{{ route('admin.presences.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.presences*') ? 'active' : '' }}">
        <i data-lucide="clock" class="h-5 w-5 mr-3"></i>
        <span>Pointages</span>
    </a>
    
    <!-- Schedule Link -->
    <a href="{{ route('admin.horaires.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.horaires*') ? 'active' : '' }}">
        <i data-lucide="calendar" class="h-5 w-5 mr-3"></i>
        <span>Horaires</span>
    </a>
    
    <!-- Schedule Swap Link -->
    <a href="{{ route('admin.permutations.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.permutations*') ? 'active' : '' }}">
        <i data-lucide="repeat" class="h-5 w-5 mr-3"></i>
        <span>Permutations</span>
    </a>
    
    <!-- Leave Link -->
    <a href="{{ route('admin.conges.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.conges*') ? 'active' : '' }}">
        <i data-lucide="calendar-off" class="h-5 w-5 mr-3"></i>
        <span>Congés</span>
    </a>
    
    <!-- Overtime Link -->
    <a href="{{ route('admin.heures-supplementaires.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.heures-supplementaires*') ? 'active' : '' }}">
        <i data-lucide="clock-plus" class="h-5 w-5 mr-3"></i>
        <span>Heures supplémentaires</span>
    </a>
    
    <!-- Reports Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rapports & Analyses</h6>
    </div>
    
    <!-- Attendance Reports Link -->
    <a href="{{ route('admin.rapports.presences') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.rapports.presences*') ? 'active' : '' }}">
        <i data-lucide="bar-chart-2" class="h-5 w-5 mr-3"></i>
        <span>Rapports de présence</span>
    </a>
    
    <!-- Department Reports Link -->
    <a href="{{ route('admin.rapports.departements') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.rapports.departements*') ? 'active' : '' }}">
        <i data-lucide="pie-chart" class="h-5 w-5 mr-3"></i>
        <span>Rapports par département</span>
    </a>
    
    <!-- Settings Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Paramètres</h6>
    </div>
    
    <!-- Company Settings Link -->
    <a href="{{ route('admin.parametres.entreprise') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.parametres.entreprise*') ? 'active' : '' }}">
        <i data-lucide="building" class="h-5 w-5 mr-3"></i>
        <span>Entreprise</span>
    </a>
    
    <!-- Notification Settings Link -->
    <a href="{{ route('admin.parametres.notifications') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.parametres.notifications*') ? 'active' : '' }}">
        <i data-lucide="bell" class="h-5 w-5 mr-3"></i>
        <span>Notifications</span>
    </a>
    
    <!-- System Settings Link -->
    <a href="{{ route('admin.parametres.systeme') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('admin.parametres.systeme*') ? 'active' : '' }}">
        <i data-lucide="settings" class="h-5 w-5 mr-3"></i>
        <span>Système</span>
    </a>
@endsection
