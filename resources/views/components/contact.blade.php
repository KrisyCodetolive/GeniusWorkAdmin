<!-- Contact Section -->
<section id="contact" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-12 text-center text-gray-800">Contactez-Nous</h2>
        
        <div class="max-w-5xl mx-auto grid md:grid-cols-2 gap-8">
            <div class="bg-gradient-to-br from-indigo-600 to-blue-500 rounded-xl p-8 text-white shadow-xl">
                <h3 class="text-2xl font-bold mb-6">Prêt à révolutionner votre gestion RH ?</h3>
                <p class="mb-8 text-white/90">
                    Pour plus d'informations ou pour demander une démonstration, n'hésitez pas à nous contacter. 
                    Notre équipe d'experts est là pour répondre à toutes vos questions.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <i data-lucide="phone" class="h-5 w-5 mr-3"></i>
                        <span>+225 07 04 750 465 / +225 27 22 252 628</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="mail" class="h-5 w-5 mr-3"></i>
                        <a href="mailto:sales@genius.ci" class="hover:underline">sales@genius.ci</a>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 rounded-xl p-8 shadow-xl">
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <i data-lucide="check-circle" class="h-5 w-5 inline-block mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif
                
                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Nom complet</label>
                        <input 
                            type="text" 
                            id="name"
                            name="name"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Votre nom complet"
                            required
                        />
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                        <input 
                            type="email" 
                            id="email"
                            name="email"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="votre@email.com"
                            required
                        />
                    </div>
                    
                    <div class="mb-4">
                        <label for="company" class="block text-gray-700 font-medium mb-2">Entreprise</label>
                        <input 
                            type="text" 
                            id="company"
                            name="company"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Nom de votre entreprise"
                        />
                    </div>
                    
                    <div class="mb-6">
                        <label for="message" class="block text-gray-700 font-medium mb-2">Message</label>
                        <textarea 
                            id="message"
                            name="message"
                            rows="4"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Comment pouvons-nous vous aider ?"
                            required
                        ></textarea>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-indigo-600 text-white font-medium py-3 px-6 rounded-lg shadow hover:bg-indigo-700 transition-colors"
                    >
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
