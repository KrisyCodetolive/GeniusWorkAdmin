@php
    // Déterminer le rôle de l'utilisateur connecté
    $userRole = 'employe'; // Par défaut
    
    if (auth()->check()) {
        if (auth()->user()->hasRole('superadmin')) {
            $userRole = 'superadmin';
        } elseif (auth()->user()->hasRole('admin')) {
            $userRole = 'admin';
        } elseif (auth()->user()->hasRole('manager')) {
            $userRole = 'manager';
        }
    }
@endphp

@switch($userRole)
    @case('superadmin')
        @include('app.components.navsidebar-superadmin')
        @break
    
    @case('admin')
        @include('app.components.navsidebar-admin')
        @break
    
    @case('manager')
        @include('app.components.navsidebar-manager')
        @break
    
    @default
        @include('app.components.navsidebar-employe')
@endswitch
