<!-- Zone de notification flottante -->
<div id="notification-area" class="fixed top-6 right-6 w-80 lg:w-96 transform transition-all duration-500 translate-y-[-150%] opacity-0">
    <div class="glass-morphism rounded-2xl shadow-2xl p-4 lg:p-6">
        <div class="flex items-center space-x-4">
            <div id="notification-icon" class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl flex items-center justify-center text-white shadow-lg transform transition-transform hover:scale-105">
                <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 id="notification-title" class="text-base lg:text-lg font-medium text-gray-800"></h3>
                <p id="notification-message" class="text-gray-600 text-xs lg:text-sm mt-1"></p>
            </div>
        </div>
    </div>
</div>
