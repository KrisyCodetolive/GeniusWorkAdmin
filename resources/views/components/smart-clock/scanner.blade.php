<!-- Zone de scan -->
<div class="w-full bg-white rounded-xl shadow-lg overflow-hidden border border-blue-100/50">
    <!-- En-tête avec dégradé -->
    <div class="p-4 bg-gradient-to-r from-blue-500 to-purple-500 text-white">
        <h3 class="text-lg font-semibold flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
            </svg>
            Identification
        </h3>
        <p class="text-xs text-blue-100 mt-1">Scannez votre badge ou saisissez votre identifiant</p>
    </div>

    <div class="p-5">
        <!-- Ajout du scan-line -->
        <div id="scan-line" class="absolute inset-x-0 top-0 h-1 bg-blue-500 opacity-0 transition-all duration-300"></div>
        
        <div class="relative w-full max-w-sm mx-auto aspect-square bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl shadow-md overflow-hidden">
            <!-- Zone d'animation du scan -->
            <div class="absolute inset-0">
                <!-- Effet de balayage -->
                <div class="absolute inset-x-0 h-40 bg-gradient-to-b from-blue-400/10 to-transparent transform -translate-y-full animate-sweep"></div>
                
                <!-- Lignes de scan -->
                <div class="absolute inset-0 flex flex-col justify-between py-8 pointer-events-none">
                    @for ($i = 0; $i < 8; $i++)
                        <div class="h-px w-full bg-gradient-to-r from-transparent via-blue-400/30 to-transparent"></div>
                    @endfor
                </div>
                
                <!-- Points lumineux animés -->
                @for ($i = 0; $i < 8; $i++)
                    <div class="absolute w-2 h-2 rounded-full bg-blue-400/50 animate-float-random"
                        style="left: {{ rand(10, 90) }}%; top: {{ rand(10, 90) }}%; animation-delay: -{{ rand(0, 3000) }}ms;">
                    </div>
                @endfor
                
                <!-- Coins du scanner -->
                <div class="absolute top-0 left-0 w-6 h-6 border-t-2 border-l-2 border-blue-400 rounded-tl-lg"></div>
                <div class="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-blue-400 rounded-tr-lg"></div>
                <div class="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-blue-400 rounded-bl-lg"></div>
                <div class="absolute bottom-0 right-0 w-6 h-6 border-b-2 border-r-2 border-blue-400 rounded-br-lg"></div>
            </div>

            <!-- Input central -->
            <div class="absolute inset-0 flex items-center justify-center p-8 z-10">
                <div class="w-full bg-white/80 backdrop-blur-md rounded-2xl p-6 shadow-lg border border-blue-200/50 transform transition-transform duration-300 hover:scale-105">
                    <input type="text" 
                        id="scanner-input" 
                        class="w-full text-center text-xl font-medium bg-transparent border-none outline-none placeholder-gray-400"
                        placeholder="Scanner votre badge"
                        autocomplete="off"
                        data-scanner="true"
                        autofocus>
                    <div class="mt-2 text-center text-sm text-gray-500">
                        ou saisir votre ID manuellement
                    </div>
                </div>
            </div>

            <!-- Bordure réactive -->
            <div id="focus-indicator" class="absolute inset-0 border-2 border-blue-400/30 rounded-2xl transition-all duration-300"></div>
        </div>

        <!-- Message d'état avec icône -->
        <div class="mt-4 text-center">
            <div id="scan-status-container" class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full transition-all duration-300">
                <span id="scan-status-icon" class="mr-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span id="scan-status" class="text-sm font-medium text-gray-600">
                    En attente de scan...
                </span>
            </div>
        </div>
        
        <!-- Bouton de scan -->
        <div class="mt-5 text-center">
            <div class="mt-4 lg:mt-6">
                <button id="scan-button" class="group px-6 lg:px-8 py-3 lg:py-4 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-base lg:text-lg font-medium rounded-full shadow-lg transform transition-all duration-300 hover:scale-105 hover:shadow-xl active:scale-95 relative overflow-hidden">
                    <span class="relative z-10">
                        Scannez votre code</span>
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes sweep {
        0% { transform: translateY(-100%); }
        100% { transform: translateY(100%); }
    }
    
    @keyframes float-random {
        0%, 100% { 
            transform: translateY(0) translateX(0);
            opacity: 0.3;
        }
        50% { 
            transform: translateY(10px) translateX(5px);
            opacity: 0.8;
        }
    }
    
    @keyframes pulse-border {
        0%, 100% { 
            border-color: rgba(96, 165, 250, 0.5);
        }
        50% { 
            border-color: rgba(96, 165, 250, 0.9);
        }
    }
    
    .animate-sweep {
        animation: sweep 2s linear infinite;
    }
    
    .animate-float-random {
        animation: float-random 3s ease-in-out infinite;
    }
    
    .animate-pulse-border {
        animation: pulse-border 2s ease-in-out infinite;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const scannerInput = document.getElementById('scanner-input');
        const focusIndicator = document.getElementById('focus-indicator');
        const scanLine = document.getElementById('scan-line');
        const scanStatus = document.getElementById('scan-status');
        const scanStatusIcon = document.getElementById('scan-status-icon');
        const scanStatusContainer = document.getElementById('scan-status-container');
        const scanButton = document.getElementById('scan-button');
        
        // Focus events
        scannerInput.addEventListener('focus', function() {
            focusIndicator.classList.add('border-blue-500', 'border-4', 'animate-pulse-border');
            focusIndicator.classList.remove('border-blue-400/30', 'border-2');
            scanLine.classList.add('opacity-100');
            
            scanStatus.textContent = 'Prêt à scanner...';
            scanStatusContainer.classList.remove('bg-gray-100');
            scanStatusContainer.classList.add('bg-blue-100');
            scanStatus.classList.add('text-blue-600');
            scanStatus.classList.remove('text-gray-600');
            
            // Change icon to ready state
            scanStatusIcon.innerHTML = `
                <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            `;
        });
        
        scannerInput.addEventListener('blur', function() {
            focusIndicator.classList.remove('border-blue-500', 'border-4', 'animate-pulse-border');
            focusIndicator.classList.add('border-blue-400/30', 'border-2');
            scanLine.classList.remove('opacity-100');
            
            scanStatus.textContent = 'En attente de scan...';
            scanStatusContainer.classList.add('bg-gray-100');
            scanStatusContainer.classList.remove('bg-blue-100');
            scanStatus.classList.remove('text-blue-600');
            scanStatus.classList.add('text-gray-600');
            
            // Reset icon to waiting state
            scanStatusIcon.innerHTML = `
                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            `;
        });
        
        // Scan button click
        scanButton.addEventListener('click', function() {
            scannerInput.focus();
            
            // Animation effect
            scanButton.classList.add('scale-95');
            setTimeout(() => {
                scanButton.classList.remove('scale-95');
            }, 150);
        });
        
        // Auto-focus on page load
        setTimeout(() => {
            scannerInput.focus();
        }, 500);
    });
</script>
