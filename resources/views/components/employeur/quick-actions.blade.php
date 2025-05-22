@props(['employeur'])

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-bolt mr-2"></i>Actions Rapides
        </h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('admin.employeurs.edit', $employeur->id) }}" class="flex flex-col items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mb-2">
                    <i class="fas fa-edit text-blue-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Modifier</span>
            </a>
            
            @if($employeur->user_id)
                <a href="{{ route('admin.users.edit', $employeur->user_id) }}" class="flex flex-col items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center mb-2">
                        <i class="fas fa-user-cog text-purple-600"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Compte</span>
                </a>
            @else
                <form action="{{ route('admin.employeurs.create-user', $employeur->id) }}" method="POST" class="contents">
                    @csrf
                    <button type="submit" class="flex flex-col items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center mb-2">
                            <i class="fas fa-user-plus text-green-600"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Créer compte</span>
                    </button>
                </form>
            @endif
            
            <a href="{{ route('admin.documents.create', ['employeur_id' => $employeur->id]) }}" class="flex flex-col items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center mb-2">
                    <i class="fas fa-file-upload text-yellow-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Document</span>
            </a>
            
            <a href="{{ route('admin.employeurs.print-badge', $employeur->id) }}" class="flex flex-col items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center mb-2">
                    <i class="fas fa-id-card text-indigo-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Badge</span>
            </a>
        </div>
    </div>
</div>
