@props(['appareil'])

<div class="bg-white rounded-lg shadow-md p-4">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Journaux d'activité</h3>
        <div class="flex space-x-2">
            <select id="log-type-filter-{{ $appareil->id }}" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <option value="all">Tous les types</option>
                <option value="info">Information</option>
                <option value="warning">Avertissement</option>
                <option value="error">Erreur</option>
                <option value="success">Succès</option>
            </select>
            <select id="log-date-filter-{{ $appareil->id }}" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <option value="all">Toutes les dates</option>
                <option value="today">Aujourd'hui</option>
                <option value="yesterday">Hier</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
            </select>
        </div>
    </div>
    
    <div class="overflow-x-auto max-h-80 overflow-y-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                    </th>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Message
                    </th>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Utilisateur
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="device-logs-{{ $appareil->id }}">
                @if(isset($appareil->logs) && count($appareil->logs) > 0)
                    @foreach($appareil->logs as $log)
                        <tr class="log-entry {{ $log->type }}">
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs">
                                @if($log->type == 'info')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Info
                                    </span>
                                @elseif($log->type == 'warning')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Avert.
                                    </span>
                                @elseif($log->type == 'error')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Erreur
                                    </span>
                                @elseif($log->type == 'success')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Succès
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500">
                                {{ $log->message }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                {{ $log->user_id ? ($log->user->name ?? 'ID: '.$log->user_id) : 'Système' }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-sm text-center text-gray-500">
                            Aucun journal d'activité disponible
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="mt-4 pt-2 border-t border-gray-200 flex justify-between items-center">
        <div class="flex space-x-2 text-xs">
            <button onclick="refreshLogs('{{ $appareil->id }}')" class="text-blue-600 hover:text-blue-900 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                Actualiser
            </button>
            <button onclick="clearLogs('{{ $appareil->id }}')" class="text-red-600 hover:text-red-900 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Effacer
            </button>
        </div>
        
        <div class="flex space-x-2">
            <button onclick="downloadLogs('{{ $appareil->id }}')" class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Exporter
            </button>
        </div>
    </div>
</div>
