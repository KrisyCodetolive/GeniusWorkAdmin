<!-- Zone de scan -->
<div class="flex flex-col items-center">
    <!-- Ajout du scan-line -->
    <div id="scan-line" class="absolute inset-x-0 top-0 h-1 bg-blue-500 opacity-0 transition-all duration-300"></div>
    
    <div class="relative w-full max-w-sm aspect-square bg-gradient-to-br from-blue-50 to-purple-50 rounded-3xl shadow-lg overflow-hidden">
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
            @for ($i = 0; $i < 5; $i++)
                <div class="absolute w-2 h-2 rounded-full bg-blue-400/50 animate-float-random"
                     style="left: {{ rand(10, 90) }}%; top: {{ rand(10, 90) }}%; animation-delay: -{{ rand(0, 3000) }}ms;">
                </div>
            @endfor
        </div>

        <!-- Input central -->
        <div class="absolute inset-0 flex items-center justify-center p-8 z-10">
            <div class="w-full bg-white/80 backdrop-blur-md rounded-2xl p-6 shadow-lg border border-blue-200/50">
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
        <div id="focus-indicator" class="absolute inset-0 border-2 border-blue-400/30 rounded-3xl transition-all duration-300"></div>
    </div>

    <!-- Message d'état -->
    <div class="mt-4 text-center text-gray-600">
        <div id="scan-status" class="text-sm font-medium">
            En attente de scan...
        </div>
        
    </div>

    <!-- Message de scan -->
    <div class="mt-4 lg:mt-6">
        <button id="scan-button" class="group px-6 lg:px-8 py-3 lg:py-4 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-base lg:text-lg font-medium rounded-full shadow-lg transform transition-all duration-300 hover:scale-105 hover:shadow-xl active:scale-95 relative overflow-hidden">
            <span class="relative z-10">Scannez votre code</span>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        </button>
    </div>
</div>

<style>
@keyframes sweep {
    0% { transform: translateY(-100%); opacity: 0.5; }
    100% { transform: translateY(100%); opacity: 0; }
}

@keyframes float-random {
    0%, 100% { transform: translate(0, 0); opacity: 0.3; }
    50% { transform: translate(10px, 10px); opacity: 0.8; }
}

.animate-sweep {
    animation: sweep 2s linear infinite;
}

.animate-float-random {
    animation: float-random 3s ease-in-out infinite;
}
</style>
