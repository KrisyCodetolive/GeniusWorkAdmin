@props(['employeur'])

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $employeur->getNomComplet() }}</h1>
        <p class="text-gray-600">{{ $employeur->poste }} - {{ $employeur->departement->nom ?? 'Département non défini' }}</p>
    </div>
    <div class="flex space-x-2">
        <a href="{{ route('admin.employeurs.edit', $employeur->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-edit mr-2"></i> Modifier
        </a>
        <a href="{{ route('admin.employeurs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-arrow-left mr-2"></i> Retour
        </a>
    </div>
</div>
