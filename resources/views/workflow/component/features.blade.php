<!-- Fonctionnalités incluses -->
<div class="mb-8">
    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
        <svg class="h-6 w-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        Fonctionnalités incluses dans tous les plans
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @php
            $features = [
                [
                    'title' => 'Gestion des présences',
                    'desc' => 'Web, Mobile, QR Code, Biométrique, illimité',
                    'icon' => "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01\" />"
                ],
                [
                    'title' => 'Application Mobile Présence',
                    'desc' => '2 mois offerts à l\'activation',
                    'icon' => "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z\" />"
                ],
                [
                    'title' => 'Gestion des visites et visiteurs',
                    'desc' => 'Enregistrement et suivi illimités',
                    'icon' => "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\" />"
                ],
                [
                    'title' => 'Système de paie & bulletins',
                    'desc' => '100 bulletins offerts, calculs automatisés',
                    'icon' => "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z\" />"
                ],
                [
                    'title' => 'Gestion multi-sites',
                    'desc' => '10 sites offerts, synchronisation centralisée',
                    'icon' => "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\" />"
                ],
                [
                    'title' => 'Gestion multi-filiales',
                    'desc' => '10 filiales offertes, administration unifiée',
                    'icon' => "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z\" />"
                ],
                [
                    'title' => 'Modules départements',
                    'desc' => 'Configuration illimitée, permissions personnalisées',
                    'icon' => "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z\" />"
                ],
                [
                    'title' => 'Gestion des employés & horaires',
                    'desc' => 'Planification avancée, suivi des performances',
                    'icon' => "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\" />"
                ],
                [
                    'title' => 'Rapports avancés',
                    'desc' => 'Analyses détaillées, exports personnalisables',
                    'icon' => "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\" />"
                ],
            ];
        @endphp
        @foreach($features as $feature)
            <div class="flex items-start bg-white border border-gray-100 rounded-xl shadow-sm p-4 hover:shadow-md transition duration-300 group hover:border-indigo-100">
                <div class="flex-shrink-0 p-2 bg-indigo-50 rounded-lg group-hover:bg-indigo-100 transition duration-300">
                    <svg class="h-6 w-6 text-indigo-600 group-hover:text-indigo-700 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $feature['icon'] !!}
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="text-base font-medium text-gray-900 group-hover:text-indigo-900 transition duration-300">{{ $feature['title'] }}</div>
                    @if($feature['desc'])
                        <div class="text-sm text-gray-500 mt-1 group-hover:text-gray-600 transition duration-300">{{ $feature['desc'] }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>