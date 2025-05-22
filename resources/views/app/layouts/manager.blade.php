@extends('app.layouts.app')

@section('title', 'GENIUS WORK - Espace Manager')

@section('sidebar')
    <!-- Dashboard Link -->
    <a href="{{ route('manager.dashboard') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard" class="h-5 w-5 mr-3"></i>
        <span>Tableau de bord</span>
    </a>
    
    <!-- Team Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Mon équipe</h6>
    </div>
    
    <!-- Team Members Link -->
    <a href="{{ route('manager.equipe.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.equipe*') ? 'active' : '' }}">
        <i data-lucide="users" class="h-5 w-5 mr-3"></i>
        <span>Membres de l'équipe</span>
    </a>
    
    <!-- Department Link -->
    <a href="{{ route('manager.departement.show') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.departement*') ? 'active' : '' }}">
        <i data-lucide="network" class="h-5 w-5 mr-3"></i>
        <span>Mon département</span>
    </a>
    
    <!-- Attendance Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Présence</h6>
    </div>
    
    <!-- Team Attendance Link -->
    <a href="{{ route('manager.presences.equipe') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.presences.equipe*') ? 'active' : '' }}">
        <i data-lucide="clock" class="h-5 w-5 mr-3"></i>
        <span>Pointages de l'équipe</span>
    </a>
    
    <!-- Attendance Map Link -->
    <a href="{{ route('manager.presences.carte') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.presences.carte*') ? 'active' : '' }}">
        <i data-lucide="map" class="h-5 w-5 mr-3"></i>
        <span>Carte des présences</span>
    </a>
    
    <!-- Schedule Link -->
    <a href="{{ route('manager.horaires.index') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.horaires*') ? 'active' : '' }}">
        <i data-lucide="calendar" class="h-5 w-5 mr-3"></i>
        <span>Planning d'équipe</span>
    </a>
    
    <!-- Approvals Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Validations</h6>
    </div>
    
    <!-- Leave Requests Link -->
    <a href="{{ route('manager.conges.validation') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.conges.validation*') ? 'active' : '' }}">
        <i data-lucide="calendar-off" class="h-5 w-5 mr-3"></i>
        <span>Demandes de congés</span>
        @if(isset($pendingLeaveRequests) && $pendingLeaveRequests > 0)
            <span class="ml-auto bg-amber-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                {{ $pendingLeaveRequests }}
            </span>
        @endif
    </a>
    
    <!-- Schedule Swap Requests Link -->
    <a href="{{ route('manager.permutations.validation') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.permutations.validation*') ? 'active' : '' }}">
        <i data-lucide="repeat" class="h-5 w-5 mr-3"></i>
        <span>Permutations d'horaires</span>
        @if(isset($pendingSwapRequests) && $pendingSwapRequests > 0)
            <span class="ml-auto bg-amber-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                {{ $pendingSwapRequests }}
            </span>
        @endif
    </a>
    
    <!-- Overtime Requests Link -->
    <a href="{{ route('manager.heures-supplementaires.validation') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.heures-supplementaires.validation*') ? 'active' : '' }}">
        <i data-lucide="clock-plus" class="h-5 w-5 mr-3"></i>
        <span>Heures supplémentaires</span>
        @if(isset($pendingOvertimeRequests) && $pendingOvertimeRequests > 0)
            <span class="ml-auto bg-amber-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                {{ $pendingOvertimeRequests }}
            </span>
        @endif
    </a>
    
    <!-- Reports Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rapports</h6>
    </div>
    
    <!-- Attendance Reports Link -->
    <a href="{{ route('manager.rapports.presences') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.rapports.presences*') ? 'active' : '' }}">
        <i data-lucide="bar-chart-2" class="h-5 w-5 mr-3"></i>
        <span>Rapports de présence</span>
    </a>
    
    <!-- Performance Reports Link -->
    <a href="{{ route('manager.rapports.performance') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.rapports.performance*') ? 'active' : '' }}">
        <i data-lucide="trending-up" class="h-5 w-5 mr-3"></i>
        <span>Performance d'équipe</span>
    </a>
    
    <!-- Personal Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Personnel</h6>
    </div>
    
    <!-- My Attendance Link -->
    <a href="{{ route('manager.presences.personnel') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.presences.personnel*') ? 'active' : '' }}">
        <i data-lucide="user-check" class="h-5 w-5 mr-3"></i>
        <span>Mes pointages</span>
    </a>
    
    <!-- My Leave Link -->
    <a href="{{ route('manager.conges.personnel') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.conges.personnel*') ? 'active' : '' }}">
        <i data-lucide="user-minus" class="h-5 w-5 mr-3"></i>
        <span>Mes congés</span>
    </a>
    
    <!-- Notifications Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notifications</h6>
    </div>
    
    <!-- Notifications Link -->
    <a href="{{ route('manager.notifications') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.notifications*') ? 'active' : '' }}">
        <i data-lucide="bell" class="h-5 w-5 mr-3"></i>
        <span>Mes notifications</span>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="ml-auto bg-red-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </a>
    
    <!-- Account Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Compte</h6>
    </div>
    
    <!-- Profile Link -->
    <a href="{{ route('manager.profil') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.profil*') ? 'active' : '' }}">
        <i data-lucide="user" class="h-5 w-5 mr-3"></i>
        <span>Mon profil</span>
    </a>
    
    <!-- Settings Link -->
    <a href="{{ route('manager.parametres') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('manager.parametres*') ? 'active' : '' }}">
        <i data-lucide="settings" class="h-5 w-5 mr-3"></i>
        <span>Paramètres</span>
    </a>
@endsection
