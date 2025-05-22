@props(['employeur'])

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-file-alt mr-2"></i>Documents
        </h2>
    </div>
    <div class="p-6">
        @if(isset($employeur->documents) && count($employeur->documents) > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($employeur->documents as $document)
                    <li class="py-3 flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @php
                                    $extension = pathinfo($document->nom_fichier, PATHINFO_EXTENSION);
                                    $iconClass = 'fa-file';
                                    
                                    if (in_array($extension, ['pdf'])) {
                                        $iconClass = 'fa-file-pdf';
                                    } elseif (in_array($extension, ['doc', 'docx'])) {
                                        $iconClass = 'fa-file-word';
                                    } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                        $iconClass = 'fa-file-excel';
                                    } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        $iconClass = 'fa-file-image';
                                    }
                                @endphp
                                <i class="fas {{ $iconClass }} text-gray-400 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-800">{{ $document->titre }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $document->type }} • Ajouté le {{ $document->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.documents.download', $document->id) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="{{ route('admin.documents.preview', $document->id) }}" class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-4">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <i class="fas fa-folder-open text-3xl"></i>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun document</h3>
                <p class="mt-1 text-sm text-gray-500">Commencez par ajouter un document pour cet employé.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.documents.create', ['employeur_id' => $employeur->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i>
                        Ajouter un document
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
