<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'employé - Invalide</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <div class="text-center mb-6">
            <svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h1 class="text-2xl font-bold text-gray-800 mt-4">Vérification Échouée</h1>
            <p class="text-gray-600 mt-2">{{ $message }}</p>
        </div>

        <div class="border-t border-gray-200 py-4 my-4">
            <p class="text-gray-600 text-center">
                Le QR code que vous avez scanné est invalide ou a expiré. Veuillez contacter votre administrateur pour obtenir un nouveau QR code.
            </p>
        </div>

        <div class="text-center text-sm text-gray-500">
            <p>Vérification tentée le {{ now()->format('d/m/Y à H:i') }}</p>
            <p class="mt-1">Powered by Genius Work</p>
        </div>
    </div>
</body>
</html>
