<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration des Employés</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-radio input:checked + label {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .smooth-transition {
            transition: all  0.3s ease;
        }
        .highlight-box {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-12 max-w-4xl">
        <div class="bg-white rounded-xl shadow-md overflow-hidden smooth-transition">

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Bienvenue {{ $entreprise->nom }}</h1>
                <p class="text-gray-600">Adaptez votre plan selon les besoins de votre entreprise</p>
            </div>
    
            <!-- Header -->
            <div class="bg-blue-600 px-6 py-4">
                <h1 class="text-2xl font-bold text-white">Configuration du nombre d'employés</h1>
                <p class="text-blue-100 mt-1">Sélectionnez le nouveau nombre d'employés pour votre abonnement</p>
            </div>
            
            <!-- Main Content -->
            <div class="p-6">
                <!-- Current Plan Info -->
                <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2 text-lg"></i>
                        <span class="font-semibold text-blue-800">Votre configuration actuelle</span>
                    </div>
                    <p class="mt-2 text-gray-700">Vous avez actuellement l'abonnement <span class="font-bold">{{ $planAbonnementActuel->nom }}</span> avec un nombre maximum de<span class="font-bold"> {{ $planAbonnementActuel->nombre_employes_max }}</span> employés.</p>
                </div>
                
                <!-- Employee Count Selection -->
                <div class="mb-8">
                    <!-- Custom Input -->
                    <div class="mb-6 highlight-box p-6 bg-white rounded-xl border border-blue-100">
                        <label for="custom-employees" class="block text-lg font-medium text-gray-800 mb-3">Nombre exact d'employés</label>
                        <p class="text-sm text-gray-600 mb-4">Saisissez le nombre précis d'employés pour obtenir un calcul exact du coût de votre abonnement.</p>
                        
                        <div class="flex items-center mb-4">
                            <button type="button" onclick="decrementEmployees()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-6 rounded-l-lg text-xl transition-colors">
                                <i class="fas fa-minus"></i>
                            </button>
                            <div class="relative flex-grow">
                                <input type="number" id="custom-employees" min="{{ $planAbonnementActuel->nombre_employes_max }}" class="w-full px-4 py-4 text-lg border-y border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-center" placeholder="Ex: {{ $planAbonnementActuel->nombre_employes_max + 10 }}">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-gray-500">employés</span>
                                </div>
                            </div>
                            <button type="button" onclick="incrementEmployees()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-6 rounded-r-lg text-xl transition-colors">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <p class="text-sm text-amber-600 mb-4"><i class="fas fa-info-circle mr-1"></i> Le nombre d'employés doit être supérieur ou égal à {{ $planAbonnementActuel->nombre_employes_max }} (votre forfait actuel).</p>
                        
                        <button onclick="useCustomValue()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg smooth-transition text-lg font-medium">
                            <i class="fas fa-calculator mr-2"></i>Calculer mon abonnement
                        </button>
                    </div>
                    
                    <!-- Quick Select Buttons -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Suggestions rapides</label>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 25 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 10 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 50 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 50 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 75 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 75 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 100 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 100 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 150 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 150 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 200 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 200 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 500 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 500 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 600 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 600 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 800 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 800 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 1000 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 1000 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 1500 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 1500 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 2000 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 2000 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 2500 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 2500 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 3000 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 3000 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 3500 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 3500 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                            <button onclick="selectEmployeeCount({{ $planAbonnementActuel->nombre_employes_max + 4000 }})" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500"><span class="block text-lg font-medium text-gray-800">{{ $planAbonnementActuel->nombre_employes_max + 4000 }}</span><span class="block text-sm text-gray-500">employés</span></button>
                        </div>
                    </div>
                </div>
                    
                    <!-- Période de facturation -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Période de facturation</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="custom-radio">
                                <input type="radio" id="periode-mensuel" name="periode" value="mensuel" class="hidden" checked>
                                <label for="periode-mensuel" onclick="setPeriode('mensuel')" class="block cursor-pointer border border-gray-200 rounded-lg p-4 text-center hover:bg-gray-50 smooth-transition">
                                    <span class="block text-lg font-medium text-gray-800">Mensuel</span>
                                    <span class="block text-sm text-gray-500">Facturation chaque mois</span>
                                </label>
                            </div>
                            <div class="custom-radio">
                                <input type="radio" id="periode-annuel" name="periode" value="annuel" class="hidden">
                                <label for="periode-annuel" onclick="setPeriode('annuel')" class="block cursor-pointer border border-gray-200 rounded-lg p-4 text-center hover:bg-gray-50 smooth-transition">
                                    <span class="block text-lg font-medium text-gray-800">Annuel</span>
                                    <span class="block text-sm text-gray-500">Facturation chaque année (réduction)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Comparison Section (Initially hidden) -->
                <div id="comparison-section" class="hidden smooth-transition">
                    <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Résumé de votre changement</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Current Plan -->
                            <div class="p-4 bg-white rounded-lg border border-gray-200">
                                <h4 class="text-lg font-medium text-gray-700 mb-3">Plan actuel</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Forfait:</span>
                                        <span class="font-medium text-gray-600">{{ $planAbonnementActuel->nom }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Employés:</span>
                                        <span class="font-medium text-gray-600">{{ $nombreEmployesActuel }} employés</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Prix:</span>
                                        <span class="font-medium text-gray-600">{{ number_format($planAbonnementActuel->prix_mensuel, 0, ',', ' ') }} XOF/mois</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- New Plan -->
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h4 class="text-lg font-medium text-blue-700 mb-3">Nouveau plan</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-blue-600">Forfait:</span>
                                        <span class="font-medium text-blue-800" id="new-plan-name">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-600">Employés:</span>
                                        <span class="font-medium text-blue-800" id="new-employee-count">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-600">Prix:</span>
                                        <span class="font-medium text-blue-800" id="new-price">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Summary -->
                        <div class="p-4 bg-green-50 rounded-lg border border-green-200 mb-6">
                            <h4 class="text-lg font-medium text-green-700 mb-2">Résumé du paiement</h4>
                            <div class="flex justify-between items-center">
                                <span class="text-green-600">Montant à payer:</span>
                                <span class="text-xl font-bold text-green-700" id="amount-due">-</span>
                            </div>
                        </div>
                        
                        <!-- Form for submission -->
                        <form id="change-form" action="{{ route('abonnements.change.process', $abonnement->id) }}" method="POST">
                            @csrf
                            <input type="hidden" id="nombre_employes" name="nombre_employes" value="">
                            <input type="hidden" id="type_periode" name="type_periode" value="mensuel">
                            
                            <!-- Action Buttons -->
                            <div class="flex flex-col md:flex-row justify-center gap-4">
                                <button type="button" onclick="modifySelection()" class="bg-white hover:bg-gray-50 text-gray-800 border border-gray-300 py-3 px-6 rounded-lg smooth-transition">
                                    <i class="fas fa-edit mr-2"></i>Modifier
                                </button>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg smooth-transition">
                                    <i class="fas fa-lock mr-2"></i>Procéder au paiement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedEmployeeCount = 0;
        let selectedPeriode = 'mensuel';
        const minEmployees = {{ $planAbonnementActuel->nombre_employes_max }};
        
        function selectEmployeeCount(count) {
            selectedEmployeeCount = count;
            updateComparisonSection();
        }
        
        function setPeriode(periode) {
            selectedPeriode = periode;
            
            // Mettre à jour l'affichage visuel des boutons radio
            document.querySelectorAll('[name="periode"]').forEach(radio => {
                const label = document.querySelector(`label[for="${radio.id}"]`);
                if (radio.value === periode) {
                    label.classList.add('border-blue-500', 'bg-blue-50');
                } else {
                    label.classList.remove('border-blue-500', 'bg-blue-50');
                }
            });
            
            // Mettre à jour le champ caché du formulaire
            document.getElementById('type_periode').value = periode;
            
            // Mettre à jour la section de comparaison si elle est visible
            if (selectedEmployeeCount > 0) {
                updateComparisonSection();
            }
        }
        
        function incrementEmployees() {
            const input = document.getElementById('custom-employees');
            let value = parseInt(input.value) || minEmployees;
            value += 10; // Incrémenter de 10
            input.value = value;
        }
        
        function decrementEmployees() {
            const input = document.getElementById('custom-employees');
            let value = parseInt(input.value) || minEmployees;
            value = Math.max(minEmployees, value - 10); // Décrémenter de 10 mais pas en dessous du minimum
            input.value = value;
            
        }
        
        function useCustomValue() {
            const customInput = document.getElementById('custom-employees');
            const value = parseInt(customInput.value);
            
            if (value && value >= minEmployees) {
                selectedEmployeeCount = value;
                updateComparisonSection();
            } else if (value && value > 0 && value < minEmployees) {
                alert('Le nombre d\'employés doit être supérieur ou égal à ' + minEmployees + ' (votre forfait actuel)');
            } else {
                alert('Veuillez saisir un nombre valide');
            }
        }
        
        function updateComparisonSection() {
            if (selectedEmployeeCount > 0) {
                // Update displayed values
                document.getElementById('new-employee-count').textContent = selectedEmployeeCount + ' employés';
                
                // Déterminer le forfait et le coût fixe selon la même formule que le service PHP
                let forfait = '';
                let coutFixe = 0;
                
                if (selectedEmployeeCount >= 1 && selectedEmployeeCount <= 50) {
                    forfait = 'Starter';
                    coutFixe = 10000;
                } else if (selectedEmployeeCount > 50 && selectedEmployeeCount <= 100) {
                    forfait = 'Side Business';
                    coutFixe = 15000;
                } else if (selectedEmployeeCount > 100) {
                    forfait = 'Enterprise';
                    coutFixe = 30000;
                }
                
                // Calculer le coût des utilisateurs
                const coutUtilisateurs = selectedEmployeeCount * 100;
                
                // Calculer le coût total en fonction de la période
                let newPrice = coutFixe + coutUtilisateurs;
                
                // Appliquer une réduction pour la période annuelle (10 mois au lieu de 12)
                if (selectedPeriode === 'annuel') {
                    newPrice = newPrice * 10; // 10 mois au lieu de 12 (réduction de 2 mois)
                }
                
                // Mettre à jour l'affichage du forfait et du prix
                document.getElementById('new-plan-name').textContent = forfait;
                
                // Mettre à jour l'affichage du prix en fonction de la période
                const periodeTexte = selectedPeriode === 'mensuel' ? '/mois' : '/an';
                document.getElementById('new-price').textContent = newPrice.toLocaleString('fr-FR') + ' XOF' + periodeTexte;
                
                // Afficher le montant total à payer
                document.getElementById('amount-due').textContent = newPrice.toLocaleString('fr-FR') + ' XOF';
                
                // Show comparison section
                document.getElementById('comparison-section').classList.remove('hidden');
                
                // Scroll to comparison
                document.getElementById('comparison-section').scrollIntoView({ behavior: 'smooth' });
                
                // Highlight selected quick button
                document.querySelectorAll('.employee-quick-select').forEach(btn => {
                    const btnValue = parseInt(btn.querySelector('span:first-child').textContent);
                    if (btnValue === selectedEmployeeCount) {
                        btn.classList.add('border-blue-500', 'bg-blue-50');
                    } else {
                        btn.classList.remove('border-blue-500', 'bg-blue-50');
                    }
                });
            }
        }
        
        function modifySelection() {
            document.getElementById('comparison-section').classList.add('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Cette fonction est appelée avant la soumission du formulaire
        document.getElementById('change-form').addEventListener('submit', function(e) {
            if (selectedEmployeeCount <= 0) {
                e.preventDefault();
                alert('Veuillez sélectionner un nombre d\'employés valide');
                return false;
            }
            
            // Mettre à jour les champs cachés du formulaire
            document.getElementById('nombre_employes').value = selectedEmployeeCount;
            document.getElementById('type_periode').value = selectedPeriode;
            
            return true; // Permettre la soumission du formulaire
        });
    </script>
</body>
</html>