<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'employé - Erreur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <div class="text-center mb-6">
            <svg class="w-16 h-16 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h1 class="text-2xl font-bold text-gray-800 mt-4">Erreur Système</h1>
            <p class="text-gray-600 mt-2">{{ $message }}</p>
        </div>

        <div class="border-t border-gray-200 py-4 my-4">
            <p class="text-gray-600 text-center">
                Une erreur est survenue lors de la vérification du QR code. Veuillez réessayer plus tard ou contacter le support technique.
            </p>
        </div>

        <div class="text-center text-sm text-gray-500">
            <p>Erreur survenue le {{ now()->format('d/m/Y à H:i') }}</p>
            <p class="mt-1">Powered by Genius Work</p>
        </div>
    </div>
</body>
</html>
