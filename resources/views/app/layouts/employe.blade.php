@extends('app.layouts.app')

@section('title', 'GENIUS WORK - Espace Employé')

@section('sidebar')
    <!-- Dashboard Link -->
    <a href="{{ route('employe.dashboard') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-dashboard" class="h-5 w-5 mr-3"></i>
        <span>Tableau de bord</span>
    </a>
    
    <!-- Attendance Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Présence</h6>
    </div>
    
    <!-- Pointage Link -->
    <a href="{{ route('employe.presences') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.presences*') ? 'active' : '' }}">
        <i data-lucide="clock" class="h-5 w-5 mr-3"></i>
        <span>Mes pointages</span>
    </a>
    
    <!-- Pointage Form Link -->
    <a href="{{ route('employe.pointage.form') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.pointage.form*') ? 'active' : '' }}">
        <i data-lucide="log-in" class="h-5 w-5 mr-3"></i>
        <span>Pointer maintenant</span>
    </a>
    
    <!-- Schedule Link -->
    <a href="{{ route('employe.horaires') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.horaires*') ? 'active' : '' }}">
        <i data-lucide="calendar" class="h-5 w-5 mr-3"></i>
        <span>Mon planning</span>
    </a>
    
    <!-- Overtime Link -->
    <a href="{{ route('employe.heures-supplementaires') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.heures-supplementaires*') ? 'active' : '' }}">
        <i data-lucide="clock-plus" class="h-5 w-5 mr-3"></i>
        <span>Heures supplémentaires</span>
    </a>
    
    <!-- Requests Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Demandes</h6>
    </div>
    
    <!-- Congés Link -->
    <a href="{{ route('employe.conges') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.conges*') ? 'active' : '' }}">
        <i data-lucide="calendar-off" class="h-5 w-5 mr-3"></i>
        <span>Mes congés</span>
    </a>
    
    <!-- Schedule Swap Link -->
    <a href="{{ route('employe.permutations') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.permutations*') ? 'active' : '' }}">
        <i data-lucide="repeat" class="h-5 w-5 mr-3"></i>
        <span>Permutations d'horaires</span>
    </a>
    
    <!-- Notifications Section -->
    <div class="mt-4 mb-2 px-3">
        <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notifications</h6>
    </div>
    
    <!-- Notifications Link -->
    <a href="{{ route('employe.notifications') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.notifications*') ? 'active' : '' }}">
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
    <a href="{{ route('employe.profil') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.profil*') ? 'active' : '' }}">
        <i data-lucide="user" class="h-5 w-5 mr-3"></i>
        <span>Mon profil</span>
    </a>
    
    <!-- Settings Link -->
    <a href="{{ route('employe.parametres') }}" class="sidebar-link flex items-center px-3 py-2 text-sm {{ request()->routeIs('employe.parametres*') ? 'active' : '' }}">
        <i data-lucide="settings" class="h-5 w-5 mr-3"></i>
        <span>Paramètres</span>
    </a>
@endsection
