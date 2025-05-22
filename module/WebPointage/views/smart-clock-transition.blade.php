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
            <p id="action-type" class="text-lg lg:text-xl text-gray-600 mb-8">
                @if($type === 'clockin')
                    Enregistrement de votre entrée
                @elseif($type === 'clockout')
                    Enregistrement de votre sortie
                @elseif($type === 'return_clockin')
                    Enregistrement de votre retour
                @else
                    Traitement en cours
                @endif
            </p>

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

            @if($message)
                <audio id="voice-message" src="{{ $message }}" autoplay></audio>
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
                        alert(data.error);
                        setTimeout(() => {
                            window.location.href = '/gwork/smart-clock';
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    clearInterval(progressInterval);
                    statusMessage.textContent = "Une erreur est survenue";
                    setTimeout(() => {
                        window.location.href = '/gwork/smart-clock';
                    }, 2000);
                });
        }

        if (progress >= 100 && !isCompleted) {
            clearInterval(progressInterval);
        }
    }, interval);
});
</script>
@endpush
