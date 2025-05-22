<!-- Pricing Section -->
<section id="pricing" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-12 text-center text-gray-800">Abonnements Mensuels</h2>
        
        <div class="max-w-5xl mx-auto">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-b from-blue-50 to-indigo-50 rounded-xl overflow-hidden shadow-lg transform transition-all hover:-translate-y-2 hover:shadow-xl">
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Starter</h3>
                        <div class="text-4xl font-bold text-indigo-600 mb-4">10 000 FCFA<span class="text-lg text-gray-600 font-normal">/mois</span></div>
                        <p class="text-gray-600 mb-6">Idéal pour les petites entreprises et les startups.</p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-500 mr-2"></i>
                                <span>15 utilisateurs</span>
                            </li>
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-500 mr-2"></i>
                                <span>Toutes les fonctionnalités de base</span>
                            </li>
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-500 mr-2"></i>
                                <span>Support par email</span>
                            </li>
                        </ul>
                        <form action="{{ route('pricing.workflow') }}" method="post">
                            @csrf
                        <input type="hidden" name="UserNumber" value="15">
                        <button type="submit" class="block text-center bg-indigo-600 text-white font-medium py-3 px-6 rounded-lg shadow hover:bg-indigo-700 transition-colors">
                            Commencer
                        </button>
                        </form>
                    </div>
                </div>
                
                <div class="bg-gradient-to-b from-indigo-600 to-blue-600 rounded-xl overflow-hidden shadow-xl transform transition-all hover:-translate-y-2 hover:shadow-2xl relative">
                    <div class="absolute top-0 right-0 bg-yellow-400 text-indigo-900 text-xs font-bold px-3 py-1 rounded-bl-lg">POPULAIRE</div>
                    <div class="p-8 text-white">
                        <h3 class="text-2xl font-bold mb-4">Side Business</h3>
                        <div class="text-4xl font-bold mb-4">15 000 FCFA<span class="text-lg font-normal">/mois</span></div>
                        <p class="text-white/80 mb-6">Parfait pour les entreprises en croissance.</p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-yellow-300 mr-2"></i>
                                <span>75 utilisateurs</span>
                            </li>
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-yellow-300 mr-2"></i>
                                <span>Toutes les fonctionnalités avancées</span>
                            </li>
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-yellow-300 mr-2"></i>
                                <span>Support prioritaire</span>
                            </li>
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-yellow-300 mr-2"></i>
                                <span>Rapports personnalisés</span>
                            </li>
                        </ul>
                        <form action="{{ route('pricing.workflow') }}" method="post">
                            @csrf
                        <input type="hidden" name="UserNumber" value="75">
                        <button type="submit"  class="block text-center bg-white text-indigo-600 font-medium py-3 px-6 rounded-lg shadow hover:bg-gray-100 transition-colors">
                            Commencer
                        </button>
                        </form>
                    </div>
                </div>
                
                <div class="bg-gradient-to-b from-blue-50 to-indigo-50 rounded-xl overflow-hidden shadow-lg transform transition-all hover:-translate-y-2 hover:shadow-xl">
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Entreprise</h3>
                        <div class="text-4xl font-bold text-indigo-600 mb-4">30 000 FCFA<span class="text-lg text-gray-600 font-normal">/mois</span></div>
                        <p class="text-gray-600 mb-6">Solution complète pour les grandes entreprises.</p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-500 mr-2"></i>
                                <span>+100 Utilisateurs </span>
                            </li>
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-500 mr-2"></i>
                                <span>Toutes les fonctionnalités premium</span>
                            </li>
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-500 mr-2"></i>
                                <span>Support dédié 24/7</span>
                            </li>
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-500 mr-2"></i>
                                <span>Intégrations avancées</span>
                            </li>
                        </ul>
                        <form action="{{ route('pricing.workflow') }}" method="post">
                            @csrf
                        <input type="hidden" name="UserNumber" value="100">
                        
                        <button type="submit" class="block text-center bg-indigo-600 text-white font-medium py-3 px-6 rounded-lg shadow hover:bg-indigo-700 transition-colors">
                                Commencer
                        </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="mt-12 bg-blue-50 rounded-xl p-6 shadow-md">
                <p class="text-center text-gray-700">
                    <strong>Remarque :</strong> Le coût par utilisateur supplémentaire est de <strong class="text-indigo-600">100 FCFA</strong>. 
                    Les forfaits incluent la sécurité et la disponibilité du système.
                </p>
            </div>
        </div>
    </div>
</section>
