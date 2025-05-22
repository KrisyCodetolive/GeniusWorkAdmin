<x-filament::page>
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">
                Arborescence du département: {{ $record->nom }}
            </h2>
            <p class="text-gray-600 mt-1">{{ $record->description }}</p>
            
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-medium text-gray-700">Informations générales</h3>
                    <dl class="mt-2 space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Code:</dt>
                            <dd class="text-sm font-medium">{{ $record->code }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Niveau hiérarchique:</dt>
                            <dd class="text-sm font-medium">{{ $record->niveau }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Filiale:</dt>
                            <dd class="text-sm font-medium">{{ $record->filiale->nom ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Statut:</dt>
                            <dd class="text-sm font-medium">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $record->statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($record->statut) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-medium text-gray-700">Responsable</h3>
                    @if($record->responsable)
                        <div class="mt-2 flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700">
                                    {{ substr($record->responsable->nom_complet ?? 'N/A', 0, 1) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $record->responsable->nom_complet }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $record->responsable->email }}
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="mt-2 text-sm text-gray-500">Aucun responsable assigné</p>
                    @endif
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-medium text-gray-700">Statistiques</h3>
                    @php
                        $stats = $this->getStatistiques();
                    @endphp
                    <dl class="mt-2 space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Employés:</dt>
                            <dd class="text-sm font-medium">{{ $stats['nombre_employes'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Employés actifs:</dt>
                            <dd class="text-sm font-medium">{{ $stats['nombre_employes_actifs'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Sous-départements:</dt>
                            <dd class="text-sm font-medium">{{ $stats['nombre_sous_departements'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Taux d'occupation:</dt>
                            <dd class="text-sm font-medium">{{ $stats['taux_occupation'] }}%</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Structure hiérarchique</h3>
            
            @php
                $arborescence = $this->getArborescence();
                
                function renderDepartement($dept, $isRoot = false) {
                    $hasChildren = !empty($dept['sous_departements']);
                    $bgClass = $isRoot ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-200';
                    
                    echo '<div class="dept-node border ' . $bgClass . ' rounded-lg p-4 mb-2">';
                    echo '<div class="flex justify-between items-center">';
                    echo '<div class="flex items-center">';
                    
                    if ($hasChildren) {
                        echo '<button class="toggle-children mr-2 text-gray-500 hover:text-gray-700 focus:outline-none" data-open="false">';
                        echo '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">';
                        echo '<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />';
                        echo '</svg>';
                        echo '</button>';
                    } else {
                        echo '<div class="w-7"></div>';
                    }
                    
                    echo '<div>';
                    echo '<h4 class="text-sm font-medium text-gray-900">' . $dept['nom'] . ' <span class="text-xs text-gray-500">(' . $dept['code'] . ')</span></h4>';
                    echo '<p class="text-xs text-gray-500">Niveau ' . $dept['niveau'] . ' • ' . $dept['nombre_employes'] . ' employé(s)</p>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div>';
                    echo '<a href="' . route('filament.admin.resources.departements.view', $dept['id']) . '" class="text-blue-600 hover:text-blue-800 text-xs mr-2">Voir</a>';
                    echo '<a href="' . route('filament.admin.resources.departements.edit', $dept['id']) . '" class="text-green-600 hover:text-green-800 text-xs">Éditer</a>';
                    echo '</div>';
                    
                    echo '</div>';
                    
                    if ($hasChildren) {
                        echo '<div class="children ml-6 mt-2 hidden">';
                        foreach ($dept['sous_departements'] as $child) {
                            renderDepartement($child);
                        }
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
            @endphp
            
            <div class="arborescence">
                @php
                    renderDepartement($arborescence, true);
                @endphp
            </div>
            
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Chemin hiérarchique complet</h3>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">{{ $record->getCheminComplet() }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-children').forEach(function(button) {
                button.addEventListener('click', function() {
                    const isOpen = this.getAttribute('data-open') === 'true';
                    const childrenContainer = this.closest('.dept-node').querySelector('.children');
                    
                    if (isOpen) {
                        childrenContainer.classList.add('hidden');
                        this.setAttribute('data-open', 'false');
                        this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>';
                    } else {
                        childrenContainer.classList.remove('hidden');
                        this.setAttribute('data-open', 'true');
                        this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>';
                    }
                });
            });
        });
    </script>
</x-filament::page>
