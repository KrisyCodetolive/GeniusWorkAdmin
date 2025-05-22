@extends('layouts.smart-clock')

@section('content')
<div class="h-screen w-screen flex items-center justify-center relative overflow-hidden">
    <!-- Particules d'arrière-plan -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        @for ($i = 0; $i < 20; $i++)
            <div class="absolute rounded-full bg-blue-200/20 animate-float"
                 style="
                    width: {{ rand(4, 12) }}px;
                    height: {{ rand(4, 12) }}px;
                    left: {{ rand(0, 100) }}%;
                    top: {{ rand(0, 100) }}%;
                    animation-delay: -{{ rand(0, 5000) }}ms;
                 ">
            </div>
        @endfor
    </div>

    <!-- Carte de transition -->
    <div class="w-[95vw] max-w-2xl glass-morphism rounded-[2.5rem] shadow-2xl border border-white/20 p-8 lg:p-12 relative overflow-hidden">
        <!-- Effet de brillance -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-400/5 via-transparent to-purple-400/5"></div>

        <!-- Contenu -->
        <div class="relative flex flex-col items-center text-center">
            <!-- Avatar et nom -->
            <div class="w-32 h-32 lg:w-40 lg:h-40 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center mb-6 relative">
                <span id="employee-initial" class="text-4xl lg:text-5xl font-bold text-blue-600">{{ $name[0] }}</span>
                <!-- Anneau animé -->
                <div class="absolute inset-0 rounded-full border-4 border-blue-500/30 animate-pulse"></div>
            </div>
            
            <h2 id="employee-name" class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">{{ $name }}</h2>
            <p id="action-type" class="text-lg lg:text-xl text-gray-600 mb-2">
                @if($type === 'clockin')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Enregistrement de votre entrée
                    </span>
                @elseif($type === 'clockout')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Enregistrement de votre sortie
                    </span>
                @elseif($type === 'return_clockin')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Enregistrement de votre retour
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Traitement en cours
                    </span>
                @endif
            </p>
            
            <!-- Date et heure -->
            <p class="text-sm text-gray-500 mb-2">
                <span id="current-date">{{ date('d/m/Y') }}</span> à <span id="current-time">{{ date('H:i') }}</span>
            </p>
            
            <!-- Informations supplémentaires -->
            @if(isset($info_supplementaire) && !empty($info_supplementaire))
            <div class="mb-6 px-4 py-2 bg-blue-50 rounded-lg border border-blue-100">
                <p class="text-sm text-blue-700">
                    <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $info_supplementaire }}
                </p>
            </div>
            @else
            <div class="mb-6"></div>
            @endif

            <!-- Barre de progression -->
            <div class="w-full max-w-md bg-gray-200 rounded-full h-2 mb-4 overflow-hidden">
                <div id="progress-bar" class="h-full w-0 bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-300"></div>
            </div>

            <!-- Message de statut -->
            <p id="status-message" class="text-sm text-gray-500">Traitement en cours...</p>
            
            <!-- Message de vérification -->
            <div id="check-message" class="mt-4 flex items-center justify-center text-green-500 opacity-0 transition-all duration-500">
                <div class="relative">
                    <!-- Cercle d'animation -->
                    <svg class="w-12 h-12 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" viewBox="0 0 50 50">
                        <circle class="circle" cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="2" />
                    </svg>
                    <!-- Checkmark -->
                    <svg class="w-12 h-12 relative" viewBox="0 0 50 50">
                        <path class="checkmark" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d="M 15,25 L 25,35 L 35,15" />
                    </svg>
                </div>
                <span class="text-lg font-medium ml-3">Vérification terminée</span>
            </div>
            
            <!-- Message d'erreur -->
            <div id="error-message" class="mt-4 flex items-center justify-center text-red-500 opacity-0 transition-all duration-500">
                <div class="relative">
                    <!-- Cercle d'animation -->
                    <svg class="w-12 h-12 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" viewBox="0 0 50 50">
                        <circle class="error-circle" cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="2" />
                    </svg>
                    <!-- X mark -->
                    <svg class="w-12 h-12 relative" viewBox="0 0 50 50">
                        <path class="error-mark" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d="M 15,15 L 35,35 M 35,15 L 15,35" />
                    </svg>
                </div>
                <span class="text-lg font-medium ml-3">Une erreur est survenue</span>
            </div>

            @if($message)
                <div id="voice-message" data-text="{{ $message }}" class="hidden"></div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.5; }
}

@keyframes checkmark {
    0% { stroke-dashoffset: 50; opacity: 0; }
    100% { stroke-dashoffset: 0; opacity: 1; }
}

@keyframes circle {
    0% { transform: scale(0.8); opacity: 0; }
    50% { opacity: 1; }
    100% { transform: scale(1.1); opacity: 0; }
}

@keyframes error-mark {
    0% { stroke-dashoffset: 100; opacity: 0; }
    100% { stroke-dashoffset: 0; opacity: 1; }
}

@keyframes error-circle {
    0% { transform: scale(0.8); opacity: 0; }
    25% { opacity: 1; }
    50% { transform: scale(1.1); }
    75% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0); }
    50% { transform: translateY(-10px) rotate(5deg); }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.checkmark {
    stroke-dasharray: 50;
    stroke-dashoffset: 50;
}

.glass-morphism {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
}

.checkmark.animate {
    animation: checkmark 0.8s ease-in-out forwards;
}

.circle {
    transform-origin: center;
    transform: scale(0.8);
    opacity: 0;
}

.circle.animate {
    animation: circle 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.error-mark {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
}

.error-mark.animate {
    animation: error-mark 0.8s ease-in-out forwards;
}

.error-circle {
    transform-origin: center;
    transform: scale(0.8);
    opacity: 0;
}

.error-circle.animate {
    animation: error-circle 0.8s cubic-bezier(0.4, 0, 0.6, 1) forwards;
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lecture du message vocal avec l'API Web Speech
    const voiceMessage = document.getElementById('voice-message');
    if (voiceMessage) {
        const text = voiceMessage.getAttribute('data-text');
        if (text && 'speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'fr-FR'; // Définir la langue en français
            window.speechSynthesis.speak(utterance);
        }
    }
    const requestId = '{{ $requestId }}';
    let progress = 0;
    const duration = 5000; // 5 secondes pour la barre de progression
    const interval = 50; // Mise à jour plus fluide
    const increment = (interval / duration) * 100;
    let isCompleted = false;

    // Démarrage de la barre de progression
    const progressInterval = setInterval(() => {
        const progressBar = document.getElementById('progress-bar');
        const statusMessage = document.getElementById('status-message');
        const checkMessage = document.getElementById('check-message');
        
        progress = Math.min(progress + increment, 100);
        progressBar.style.width = `${progress}%`;

        // Vérification du statut toutes les 500ms
        if (progress % 10 === 0) {
            fetch(`/gwork/webclock/check-status/${requestId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed' && progress >= 100) {
                        clearInterval(progressInterval);
                        statusMessage.textContent = "Vérification terminée";
                        checkMessage.style.opacity = '1';
                        
                        // Animer le checkmark et le cercle
                        const checkmark = document.querySelector('.checkmark');
                        const circle = document.querySelector('.circle');
                        checkmark.classList.add('animate');
                        circle.classList.add('animate');
                        
                        // Son de validation (optionnel)
                        const audio = new Audio('/assets/sounds/notification.mp3');
                        audio.play().catch(() => {}); // Ignorer si le son n'est pas disponible
                        
                        setTimeout(() => {
                            window.location.href = '/gwork/smart-clock';
                        }, 2000);
                        
                        isCompleted = true;
                    } else if (data.status === 'error') {
                        clearInterval(progressInterval);
                        statusMessage.textContent = "Une erreur est survenue";
                        
                        // Afficher le message d'erreur
                        const errorMessage = document.getElementById('error-message');
                        errorMessage.style.opacity = '1';
                        
                        // Animer le X et le cercle
                        const errorMark = document.querySelector('.error-mark');
                        const errorCircle = document.querySelector('.error-circle');
                        errorMark.classList.add('animate');
                        errorCircle.classList.add('animate');
                        
                        // Afficher le message d'erreur
                        const errorText = data.message || 'Une erreur est survenue lors du traitement de votre pointage';
                        const errorToast = document.createElement('div');
                        errorToast.className = 'fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-lg transition-all duration-500 opacity-0';
                        errorToast.innerHTML = `
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>${errorText}</span>
                            </div>
                        `;
                        document.body.appendChild(errorToast);
                        
                        // Afficher le toast avec animation
                        setTimeout(() => {
                            errorToast.classList.add('opacity-100');
                        }, 100);
                        
                        setTimeout(() => {
                            errorToast.classList.remove('opacity-100');
                            errorToast.classList.add('opacity-0');
                            setTimeout(() => {
                                errorToast.remove();
                                window.location.href = '/gwork/smart-clock';
                            }, 500);
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    clearInterval(progressInterval);
                    statusMessage.textContent = "Une erreur est survenue";
                    
                    // Afficher le message d'erreur
                    const errorMessage = document.getElementById('error-message');
                    errorMessage.style.opacity = '1';
                    
                    // Animer le X et le cercle
                    const errorMark = document.querySelector('.error-mark');
                    const errorCircle = document.querySelector('.error-circle');
                    errorMark.classList.add('animate');
                    errorCircle.classList.add('animate');
                    
                    // Afficher le toast d'erreur
                    const errorToast = document.createElement('div');
                    errorToast.className = 'fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-lg transition-all duration-500 opacity-0';
                    errorToast.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Erreur de communication avec le serveur</span>
                        </div>
                    `;
                    document.body.appendChild(errorToast);
                    
                    // Afficher le toast avec animation
                    setTimeout(() => {
                        errorToast.classList.add('opacity-100');
                    }, 100);
                    
                    setTimeout(() => {
                        errorToast.classList.remove('opacity-100');
                        errorToast.classList.add('opacity-0');
                        setTimeout(() => {
                            errorToast.remove();
                            window.location.href = '/gwork/smart-clock';
                        }, 500);
                    }, 3000);
                });
        }

        if (progress >= 100 && !isCompleted) {
            clearInterval(progressInterval);
        }
    }, interval);
});
</script>
@endpush
