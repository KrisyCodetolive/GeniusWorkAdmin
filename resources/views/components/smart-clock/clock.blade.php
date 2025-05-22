<!-- Horloge flottante -->
<div class="relative mb-4 lg:mb-6 animate-float mt-4">
    <div class="w-36 h-36 lg:w-44 lg:h-44 mx-auto bg-white rounded-full shadow-[0_8px_32px_0_rgba(31,38,135,0.2)] flex items-center justify-center border-[10px] lg:border-[14px] border-white/80 relative">
        <!-- Anneau lumineux -->
        <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-400/20 via-purple-400/20 to-blue-400/20 animate-glow"></div>
        <div class="text-center z-10">
            <div class="text-3xl lg:text-4xl font-bold bg-gradient-to-br from-blue-600 to-purple-600 bg-clip-text text-transparent" id="current-time">
                {{ date('H:i') }}
            </div>
            <div class="text-base lg:text-lg text-gray-400 mt-1" id="current-seconds">
                {{ date('s') }}
            </div>
        </div>
    </div>
</div>

<!-- Date -->
<div class="text-center mb-4">
    <div class="text-xl lg:text-2xl font-medium bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent" id="current-date">
        {{ date('d F Y') }}
    </div>
</div>
