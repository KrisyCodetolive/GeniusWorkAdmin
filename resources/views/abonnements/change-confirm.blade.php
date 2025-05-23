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
                    <p class="mt-2 text-gray-700">Vous avez actuellement <span class="font-bold">75</span> employés configurés.</p>
                </div>
                
                <!-- Employee Count Selection -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Sélectionnez le nombre d'employés</h2>
                    
                    <!-- Quick Select Buttons -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                        <button onclick="selectEmployeeCount(50)" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="block text-lg font-medium text-gray-800">50</span>
                            <span class="block text-sm text-gray-500">employés</span>
                        </button>
                        <button onclick="selectEmployeeCount(20)" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="block text-lg font-medium text-gray-800">20</span>
                            <span class="block text-sm text-gray-500">employés</span>
                        </button>
                        <button onclick="selectEmployeeCount(100)" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="block text-lg font-medium text-gray-800">100</span>
                            <span class="block text-sm text-gray-500">employés</span>
                        </button>
                        <button onclick="selectEmployeeCount(250)" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="block text-lg font-medium text-gray-800">250</span>
                            <span class="block text-sm text-gray-500">employés</span>
                        </button>
                        <button onclick="selectEmployeeCount(500)" class="employee-quick-select bg-white border border-gray-200 rounded-lg py-3 px-4 text-center hover:bg-gray-50 smooth-transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="block text-lg font-medium text-gray-800">500</span>
                            <span class="block text-sm text-gray-500">employés</span>
                        </button>
                    </div>
                    
                    <!-- Custom Input -->
                    <div class="mb-6">
                        <label for="custom-employees" class="block text-sm font-medium text-gray-700 mb-2">Ou saisissez un nombre personnalisé</label>
                        <div class="relative">
                            <input type="number" id="custom-employees" min="1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: 150">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500">employés</span>
                            </div>
                        </div>
                        <button onclick="useCustomValue()" class="mt-3 w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg smooth-transition">
                            <i class="fas fa-check mr-2"></i>Utiliser cette valeur
                        </button>
                    </div>
                </div>
                
                <!-- Comparison Section (Initially hidden) -->
                <div id="comparison-section" class="hidden smooth-transition">
                    <div class="bg-gray-50 p-6 rounded-xl highlight-box">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">Résumé de votre demande</h2>
                        
                        <!-- Comparison Table -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <h3 class="font-semibold text-gray-700 mb-3">Ancien plan</h3>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-users text-gray-400 mr-2"></i>
                                    <span class="text-gray-600">75 employés</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-euro-sign text-gray-400 mr-2"></i>
                                    <span class="text-gray-600">375€/mois</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-center">
                                <i class="fas fa-arrow-right text-gray-400 text-2xl"></i>
                            </div>
                            
                            <div class="bg-white p-4 rounded-lg border border-blue-200 border-2">
                                <h3 class="font-semibold text-blue-700 mb-3">Nouveau plan</h3>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-users text-blue-500 mr-2"></i>
                                    <span class="text-gray-800 font-medium" id="new-employee-count">100 employés</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-euro-sign text-blue-500 mr-2"></i>
                                    <span class="text-gray-800 font-medium" id="new-price">500€/mois</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Amount to Pay -->
                        <div class="bg-blue-50 p-4 rounded-lg mb-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-blue-800">Montant à payer</h4>
                                    <p class="text-sm text-blue-600">Différence pour le reste du mois</p>
                                </div>
                                <span class="text-2xl font-bold text-blue-800" id="amount-due">+125€</span>
                            </div>
                        </div>
                        
                        <!-- Payment Info -->
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle text-yellow-500 mt-1 mr-2"></i>
                                <div>
                                    <p class="text-sm text-yellow-800">
                                        Après confirmation, vous serez redirigé vers notre passerelle de paiement sécurisée pour finaliser la transaction.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-col md:flex-row justify-center gap-4">
                            <button onclick="modifySelection()" class="bg-white hover:bg-gray-50 text-gray-800 border border-gray-300 py-3 px-6 rounded-lg smooth-transition">
                                <i class="fas fa-edit mr-2"></i>Modifier
                            </button>
                            <button onclick="proceedToPayment()" class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg smooth-transition">
                                <i class="fas fa-lock mr-2"></i>Procéder au paiement
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedEmployeeCount = 0;
        
        function selectEmployeeCount(count) {
            selectedEmployeeCount = count;
            updateComparisonSection();
        }
        
        function useCustomValue() {
            const customInput = document.getElementById('custom-employees');
            const value = parseInt(customInput.value);
            
            if (value && value > 0) {
                selectedEmployeeCount = value;
                updateComparisonSection();
            } else {
                alert('Veuillez saisir un nombre valide');
            }
        }
        
        function updateComparisonSection() {
            if (selectedEmployeeCount > 0) {
                // Update displayed values
                document.getElementById('new-employee-count').textContent = selectedEmployeeCount + ' employés';
                
                // Calculate price (5€ per employee)
                const newPrice = selectedEmployeeCount * 5;
                document.getElementById('new-price').textContent = newPrice + '€/mois';
                
                // Calculate difference (current is 75 employees at 375€)
                const difference = newPrice - 375;
                document.getElementById('amount-due').textContent = (difference > 0 ? '+' : '') + difference + '€';
                
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
        
        function proceedToPayment() {
            alert('Redirection vers la passerelle de paiement pour ' + selectedEmployeeCount + ' employés');
            // In a real implementation, this would submit a form or redirect
        }
    </script>
</body>
</html>