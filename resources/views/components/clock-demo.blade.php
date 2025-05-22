<!-- Clock Demo Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-gradient-to-r from-indigo-600 to-blue-500 rounded-xl shadow-2xl overflow-hidden">
            <div class="p-8 text-center">
                <div id="current-time" class="text-white text-5xl font-bold mb-4">00:00:00</div>
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 mb-4">
                    <input 
                        type="text" 
                        placeholder="Entrez votre matricule..." 
                        class="w-full bg-white/80 backdrop-blur-sm rounded-lg px-4 py-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-white"
                    />
                </div>
                <div class="flex justify-center space-x-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-full p-3">
                        <i data-lucide="qr-code" class="h-8 w-8 text-white"></i>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-full p-3">
                        <i data-lucide="fingerprint" class="h-8 w-8 text-white"></i>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-full p-3">
                        <i data-lucide="smartphone" class="h-8 w-8 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
