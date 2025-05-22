@props(['employeur'])

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-chart-line mr-2"></i>Statistiques et Performance
        </h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Taux de présence -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Taux de présence</h3>
                <div class="flex items-end">
                    <span class="text-2xl font-bold text-blue-600">{{ $employeur->taux_presence ?? rand(85, 98) }}%</span>
                    <span class="text-xs text-gray-500 ml-2 mb-1">30 derniers jours</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $employeur->taux_presence ?? rand(85, 98) }}%"></div>
                </div>
            </div>
            
            <!-- Projets en cours -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Projets en cours</h3>
                <div class="flex items-end">
                    <span class="text-2xl font-bold text-green-600">{{ $employeur->projets_count ?? rand(1, 5) }}</span>
                    <span class="text-xs text-gray-500 ml-2 mb-1">projets actifs</span>
                </div>
                <div class="flex items-center mt-2">
                    <span class="text-xs text-gray-600">Progression moyenne:</span>
                    <span class="text-xs font-semibold ml-1">{{ $employeur->progression_moyenne ?? rand(25, 75) }}%</span>
                </div>
            </div>
            
            <!-- Performance -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Performance</h3>
                <div class="flex items-end">
                    <span class="text-2xl font-bold text-purple-600">{{ $employeur->performance ?? rand(3, 5) }}/5</span>
                    <span class="text-xs text-gray-500 ml-2 mb-1">évaluation</span>
                </div>
                <div class="flex mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= ($employeur->performance ?? rand(3, 5)))
                            <i class="fas fa-star text-yellow-500"></i>
                        @else
                            <i class="far fa-star text-gray-300"></i>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
        
        <div class="mt-6">
            <a href="{{ route('admin.employeurs.statistiques', $employeur->id) }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                <i class="fas fa-chart-bar mr-1"></i> Voir toutes les statistiques
                <i class="fas fa-chevron-right ml-1 text-xs"></i>
            </a>
        </div>
    </div>
</div>
