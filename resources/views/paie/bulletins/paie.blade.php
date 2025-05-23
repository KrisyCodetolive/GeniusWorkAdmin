<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Paie - {{ $bulletin->reference }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        
        .hover-scale:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
            body {
                background: white !important;
                color: black !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover-scale transition-all duration-300 mb-8 animate-fade-in">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold">BULLETIN DE PAIE</h1>
                        <p class="opacity-90">Période du <span id="date-debut">{{ $bulletin->periode_debut ? \Carbon\Carbon::parse($bulletin->periode_debut)->format('d/m/Y') : 'N/A' }}</span> au <span id="date-fin">{{ $bulletin->periode_fin ? \Carbon\Carbon::parse($bulletin->periode_fin)->format('d/m/Y') : 'N/A' }}</span></p>
                    </div>
                    <div class="bg-white text-blue-700 px-4 py-2 rounded-lg shadow">
                        <p class="font-semibold">N° <span id="numero-paie">{{ $bulletin->reference }}</span></p>
                    </div>
                </div>
            </div>
            
            <!-- Employee Info -->
            <div class="p-6 grid md:grid-cols-3 gap-6">
                <div class="animate-fade-in delay-100">
                    <h3 class="text-gray-500 uppercase text-sm font-semibold mb-2">Employé</h3>
                    <p class="font-medium">{{ $bulletin->employeur->nom_complet ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $bulletin->employeur->poste ?? 'N/A' }}</p>
                    <p class="text-gray-600 mt-1">Matricule: {{ $bulletin->employeur->matricule ?? 'N/A' }}</p>
                    <p class="text-gray-600">Embauche: {{ $bulletin->employeur->date_embauche ? \Carbon\Carbon::parse($bulletin->employeur->date_embauche)->format('d/m/Y') : 'N/A' }}</p>
                    <p class="text-gray-600">Naissance: {{ $bulletin->employeur->date_naissance ? \Carbon\Carbon::parse($bulletin->employeur->date_naissance)->format('d/m/Y') : 'N/A' }}</p>
                </div>
                
                <div class="animate-fade-in delay-200">
                    <h3 class="text-gray-500 uppercase text-sm font-semibold mb-2">Entreprise</h3>
                    <p class="font-medium">{{ $bulletin->employeur->entreprise->nom ?? 'N/A' }}</p>
                    <p class="text-gray-600 mt-1">Siège social: {{ $bulletin->employeur->entreprise->adresse ?? 'N/A' }}</p>
                    <p class="text-gray-600">N° CC: {{ $bulletin->employeur->entreprise->configuration['numero_contribuable'] ?? 'N/A' }}</p>
                    <p class="text-gray-600">N° CNPS: {{ $bulletin->employeur->entreprise->configuration['cnps'] ?? 'N/A' }}</p>
                    <p class="text-gray-600">Téléphone: {{ $bulletin->employeur->entreprise->telephone ?? 'N/A' }}</p>
                    <p class="text-gray-600">Email: {{ $bulletin->employeur->entreprise->email ?? 'N/A' }}</p>  
                </div>
                
                <div class="animate-fade-in delay-300">
                    <h3 class="text-gray-500 uppercase text-sm font-semibold mb-2">Contrat</h3>
                    <p class="font-medium">{{ $bulletin->employeur->type_contrat ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $bulletin->employeur->horaire ?? 'Temps plein' }}</p>
                    <p class="text-gray-600 mt-1">Catégorie: {{ $bulletin->employeur->categorie ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Salary Details -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8 animate-fade-in delay-100">
            <div class="border-b border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800">Détails de rémunération</h2>
            </div>
            
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Gains -->
                    <div>
                        <h3 class="text-lg font-medium text-green-600 mb-4 flex items-center">
                            <i class="fas fa-coins mr-2"></i> Gains
                        </h3>
                        <div class="space-y-3">
                            @foreach($elementsSalaire as $element)
                            <div class="flex justify-between">
                                <span>{{ $element->libelle }}</span>
                                <span class="font-medium">{{ number_format($element->montant, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endforeach
                        </div>
                        
                        @if(count($elementsIndemnites) > 0)
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Indemnités</h4>
                            <div class="space-y-3">
                                @foreach($elementsIndemnites as $element)
                                <div class="flex justify-between">
                                    <span>{{ $element->libelle }}</span>
                                    <span class="font-medium">{{ number_format($element->montant, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        @if(count($elementsPrimes) > 0)
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Primes</h4>
                            <div class="space-y-3">
                                @foreach($elementsPrimes as $element)
                                <div class="flex justify-between">
                                    <span>{{ $element->libelle }}</span>
                                    <span class="font-medium">{{ number_format($element->montant, 0, ',', ' ') }} FCFA</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Retenues -->
                    <div>
                        <h3 class="text-lg font-medium text-red-600 mb-4 flex items-center">
                            <i class="fas fa-hand-holding-usd mr-2"></i> Retenues
                        </h3>
                        <div class="space-y-3">
                            @foreach($elementsRetenues as $element)
                            <div class="flex justify-between">
                                <span>{{ $element->libelle }}</span>
                                <span class="font-medium text-red-600">-{{ number_format($element->montant, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Total -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium">Salaire net à payer</h3>
                            <p class="text-sm text-gray-500">Avant impôt sur le revenu</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($bulletin->salaire_net, 0, ',', ' ') }} FCFA</p>
                            <p class="text-sm text-gray-500">{{ $payrollData['net']['inWords'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charges Patronales -->
        @if(count($elementsCharges) > 0)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8 animate-fade-in delay-200">
            <div class="border-b border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-building text-indigo-600 mr-3"></i> Charges Patronales
                </h2>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($elementsCharges as $element)
                    <div class="flex justify-between">
                        <span>{{ $element->libelle }}</span>
                        <span class="font-medium">{{ number_format($element->montant, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between">
                        <span class="font-medium">Total des Charges Patronales</span>
                        <span class="font-medium text-indigo-600">{{ number_format($bulletin->charges_patronales ?? $elementsCharges->sum('montant'), 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Additional Info -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <!-- Leave Balance -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden animate-fade-in delay-200">
                <div class="border-b border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-alt text-blue-500 mr-3"></i> Congés 
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Congés payés acquis</span>
                            <span class="font-medium">{{ $payrollData['conge']['solde_acquis'] ?? 0 }} jours</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Congés pris ce mois</span>
                            <span class="font-medium">{{ $payrollData['conge']['conges_pris_periode'] ?? 0 }} jours</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Solde restant</span>
                            <span class="font-medium text-green-600">{{ $payrollData['conge']['solde_restant'] ?? 0 }} jours</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden animate-fade-in delay-200">
                <div class="border-b border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-alt text-blue-500 mr-3"></i> Présences
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Temps de travail total</span>
                            <span class="font-medium">{{ $payrollData['presence']['temps_total'] ?? '0h 0m' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Jours travaillés</span>
                            <span class="font-medium">{{ $payrollData['presence']['jours_travailles'] ?? 0 }} jours</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Temps moyen par jour</span>
                            <span class="font-medium">{{ $payrollData['presence']['temps_moyen_par_jour'] ?? '0h 0m' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Retards cumulés</span>
                            <span class="font-medium text-amber-600">{{ $payrollData['presence']['retard_total'] ?? '0h 0m' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Heures supplémentaires</span>
                            <span class="font-medium text-blue-600">{{ $payrollData['presence']['heures_supplementaires'] ?? '0h 0m' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
                   <!-- Tax Info -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8 animate-fade-in delay-300">
            <div class="border-b border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-file-invoice-dollar text-purple-500 mr-3"></i> Fiscalité
                </h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span>Salaire brut annuel</span>
                        <span class="font-medium">{{ number_format(($bulletin->salaire_brut ?? 0) * 12, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Taux d'imposition</span>
                        <span class="font-medium">{{ $bulletin->employeur->taux_imposition ?? '0%' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Parts fiscales</span>
                        <span class="font-medium">{{ $bulletin->employeur->parts_fiscales ?? 1 }}</span>
                    </div>
                    </div>
                </div>
        <!-- Informations de Paiement -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8 animate-fade-in delay-300">
            <div class="border-b border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-money-check-alt text-green-600 mr-3"></i> Informations de Paiement
                </h2>
            </div>
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Moyen de paiement -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-3">Moyen de Paiement</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <div class="w-5 h-5 border border-gray-400 rounded-sm mr-3 print:border-black"></div>
                                <span>Espèces</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-5 h-5 border border-gray-400 rounded-sm mr-3 print:border-black"></div>
                                <span>Virement Bancaire</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-5 h-5 border border-gray-400 rounded-sm mr-3 print:border-black"></div>
                                <span>Mobile Money</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statut de paiement -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-3">Statut de Paiement</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <div class="w-5 h-5 border border-gray-400 rounded-sm mr-3 print:border-black"></div>
                                <span>Payé</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-5 h-5 border border-gray-400 rounded-sm mr-3 print:border-black"></div>
                                <span>Impayé</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <p class="text-gray-600">Date de paiement: <span class="font-medium">{{ $bulletin->date_paiement ? \Carbon\Carbon::parse($bulletin->date_paiement)->format('d/m/Y') : 'N/A' }}</span></p>
                </div>
            </div>
        </div>
        
        <!-- Summary & Actions - masqué à l'impression -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden animate-fade-in delay-300 no-print">
            <div class="p-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <p class="text-gray-600">Document généré le <span class="font-medium">{{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</span></p>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-print mr-2"></i> Imprimer
                        </button>
                        <a href="{{ route('paie.bulletins.pdf', $bulletin->id) }}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-download mr-2"></i> Télécharger PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Print Footer -->
        <div class="print-only mt-12 pt-6 border-t border-gray-300 text-center text-sm text-gray-500">
            <p>Document généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</p>
            <p class="mt-1">Ce document fait foi et doit être conservé pendant 5 ans</p>
            <p class="mt-1">Document généré automatiquement par l'application <strong>Genius Work</strong> et nécessite une signature manuscrite pour être valide.</p>
        </div>
    </div>

    <script>
        // Fonction pour l'impression
        document.addEventListener('DOMContentLoaded', function() {
            // Ajout d'une confirmation avant l'impression
            document.querySelector('button[onclick="window.print()"]').addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Voulez-vous imprimer ce bulletin de paie?')) {
                    window.print();
                }
            });
        });
    </script>
</body>
</html>