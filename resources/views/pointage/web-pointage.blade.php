@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-100 via-indigo-50 to-purple-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
        <div class="p-8">
            <div class="flex justify-center mb-6">
                <img class="h-20 w-auto" src="{{ asset('/assets/images/img/logo.png') }}" alt="Logo">
            </div>
            
            <h2 class="text-center text-2xl font-bold text-gray-900 mb-6">
                Pointage sur {{ $site->nom }}
            </h2>
            
            <div class="mb-6 text-center">
                <p class="text-sm text-gray-600">
                    Veuillez saisir votre identifiant pour enregistrer votre présence
                </p>
            </div>
            
            <form id="pointage-form" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                
                <div>
                    <label for="idno" class="block text-sm font-medium text-gray-700">Identifiant</label>
                    <div class="mt-1">
                        <input id="idno" name="idno" type="text" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
                
                <div class="flex items-center">
                    <input id="pause" name="pause" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="pause" class="ml-2 block text-sm text-gray-900">
                        Je prends/termine une pause
                    </label>
                </div>
                
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Valider
                    </button>
                </div>
            </form>
            
            <div id="result" class="mt-6 hidden">
                <div class="p-4 rounded-md">
                    <div class="flex">
                        <div id="result-icon" class="flex-shrink-0"></div>
                        <div class="ml-3">
                            <h3 id="result-title" class="text-sm font-medium"></h3>
                            <div id="result-message" class="mt-2 text-sm"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour obtenir la position géographique
    function getLocation(callback) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    callback({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                },
                function(error) {
                    console.error("Erreur de géolocalisation:", error);
                    callback(null);
                },
                { enableHighAccuracy: true }
            );
        } else {
            console.error("La géolocalisation n'est pas supportée par ce navigateur.");
            callback(null);
        }
    }

    // Gestion du formulaire de pointage
    const form = document.getElementById('pointage-form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const resultDiv = document.getElementById('result');
        const resultIcon = document.getElementById('result-icon');
        const resultTitle = document.getElementById('result-title');
        const resultMessage = document.getElementById('result-message');
        
        // Désactiver le bouton pendant le traitement
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Traitement...';
        
        // Obtenir la position géographique
        getLocation(function(position) {
            // Ajouter les coordonnées au formulaire si disponibles
            if (position) {
                formData.append('lat', position.lat);
                formData.append('lng', position.lng);
            }
            
            // Envoyer la requête
            fetch('{{ route("web-pointage.process") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Réactiver le bouton
                submitButton.disabled = false;
                submitButton.innerHTML = 'Valider';
                
                // Afficher le résultat
                resultDiv.classList.remove('hidden');
                
                if (data.status === 'success') {
                    resultDiv.classList.add('bg-green-50');
                    resultIcon.innerHTML = '<svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>';
                    resultTitle.classList.add('text-green-800');
                    resultTitle.textContent = 'Pointage réussi !';
                    
                    // Construire le message détaillé
                    let message = `Bonjour ${data.data.employee}, votre ${getTypeLabel(data.data.type)} a été enregistré à ${data.data.time}.`;
                    
                    if (data.data.retard) {
                        message += ` Vous êtes en retard de ${data.data.minutes_retard} minutes.`;
                    }
                    
                    if (data.data.minutes_travaillees > 0) {
                        const heures = Math.floor(data.data.minutes_travaillees / 60);
                        const minutes = data.data.minutes_travaillees % 60;
                        message += ` Vous avez travaillé ${heures}h${minutes < 10 ? '0' + minutes : minutes}.`;
                    }
                    
                    if (data.data.minutes_supplementaires > 0) {
                        const heures = Math.floor(data.data.minutes_supplementaires / 60);
                        const minutes = data.data.minutes_supplementaires % 60;
                        message += ` Dont ${heures}h${minutes < 10 ? '0' + minutes : minutes} supplémentaires.`;
                    }
                    
                    if (data.data.minutes_pause > 0) {
                        const heures = Math.floor(data.data.minutes_pause / 60);
                        const minutes = data.data.minutes_pause % 60;
                        message += ` Durée de la pause: ${heures}h${minutes < 10 ? '0' + minutes : minutes}.`;
                    }
                    
                    resultMessage.classList.add('text-green-700');
                    resultMessage.textContent = message;
                    
                    // Lire le message vocal si disponible
                    if (data.data.voice) {
                        const utterance = new SpeechSynthesisUtterance(data.data.voice);
                        utterance.lang = 'fr-FR';
                        window.speechSynthesis.speak(utterance);
                    }
                    
                    // Réinitialiser le formulaire après 5 secondes
                    setTimeout(() => {
                        form.reset();
                    }, 5000);
                } else {
                    resultDiv.classList.add('bg-red-50');
                    resultIcon.innerHTML = '<svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>';
                    resultTitle.classList.add('text-red-800');
                    resultTitle.textContent = 'Erreur';
                    resultMessage.classList.add('text-red-700');
                    resultMessage.textContent = data.message || 'Une erreur est survenue lors du traitement de votre pointage.';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                
                // Réactiver le bouton
                submitButton.disabled = false;
                submitButton.innerHTML = 'Valider';
                
                // Afficher l'erreur
                resultDiv.classList.remove('hidden');
                resultDiv.classList.add('bg-red-50');
                resultIcon.innerHTML = '<svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>';
                resultTitle.classList.add('text-red-800');
                resultTitle.textContent = 'Erreur';
                resultMessage.classList.add('text-red-700');
                resultMessage.textContent = 'Une erreur de communication est survenue. Veuillez réessayer.';
            });
        });
    });
    
    // Fonction pour obtenir le libellé du type de pointage
    function getTypeLabel(type) {
        switch (type) {
            case 'entree':
                return 'entrée';
            case 'sortie':
                return 'sortie';
            case 'pause_debut':
                return 'début de pause';
            case 'pause_fin':
                return 'fin de pause';
            default:
                return 'pointage';
        }
    }
});
</script>
@endpush
