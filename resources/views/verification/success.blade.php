<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'employé - Succès</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <div class="text-center mb-6">
            <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h1 class="text-2xl font-bold text-gray-800 mt-4">Vérification Réussie</h1>
            <p class="text-gray-600 mt-2">Les informations de l'employé ont été vérifiées avec succès.</p>
        </div>

        <div class="border-t border-b border-gray-200 py-4 my-4">
            <div class="flex items-center justify-center mb-4">
                @if($employeur->photo)
                    <img src="{{ Storage::disk('public')->url($employeur->photo) }}" alt="Photo" class="w-24 h-24 object-cover rounded-full border-2 border-gray-300">
                @else
                    <div class="w-24 h-24 rounded-full bg-gray-300 flex items-center justify-center text-gray-500">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Nom complet:</span>
                    <span class="font-semibold text-gray-800">{{ $employeur->nom_complet }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Matricule:</span>
                    <span class="font-semibold text-gray-800">{{ $employeur->matricule ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Poste:</span>
                    <span class="font-semibold text-gray-800">{{ $employeur->poste }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Département:</span>
                    <span class="font-semibold text-gray-800">{{ $employeur->departement->nom ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Date d'embauche:</span>
                    <span class="font-semibold text-gray-800">{{ $employeur->date_embauche ? date('d/m/Y', strtotime($employeur->date_embauche)) : 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Entreprise:</span>
                    <span class="font-semibold text-gray-800">{{ $employeur->entreprise->nom ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="text-center text-sm text-gray-500">
            <p>Vérification effectuée le {{ now()->format('d/m/Y à H:i') }}</p>
            <p class="mt-1">Powered by Genius Work</p>
        </div>
    </div>
</body>
</html>
