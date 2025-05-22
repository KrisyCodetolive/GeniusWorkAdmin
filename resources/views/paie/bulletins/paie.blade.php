<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de Paie Moderne</title>
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
                        <p class="opacity-90">Période du <span id="date-debut">01/01/2023</span> au <span id="date-fin">31/01/2023</span></p>
                    </div>
                    <div class="bg-white text-blue-700 px-4 py-2 rounded-lg shadow">
                        <p class="font-semibold">N° <span id="numero-paie">PAIE-2023-001</span></p>
                    </div>
                </div>
            </div>
            
            <!-- Employee Info -->
            <div class="p-6 grid md:grid-cols-3 gap-6">
                <div class="animate-fade-in delay-100">
                    <h3 class="text-gray-500 uppercase text-sm font-semibold mb-2">Employé</h3>
                    <p class="font-medium" id="employee-name">Jean Dupont</p>
                    <p class="text-gray-600" id="employee-position">Développeur Fullstack</p>
                    <p class="text-gray-600 mt-1" id="employee-matricule">Matricule: MAT-123</p>
                    <p class="text-gray-600" id="employee-date-embauche">Embauche: 01/01/2020</p>
                </div>
                
                <div class="animate-fade-in delay-200">
                    <h3 class="text-gray-500 uppercase text-sm font-semibold mb-2">Entreprise</h3>
                    <p class="font-medium" id="company-name">Tech Solutions SARL</p>
                    <p class="text-gray-600" id="company-siret">RCCM: 123 456 789 00012</p>
                    <p class="text-gray-600 mt-1" id="company-address">Adresse: Abidjan, Cocody</p>
                    <p class="text-gray-600" id="company-cc">CC: 12345678</p>
                </div>
                
                <div class="animate-fade-in delay-300">
                    <h3 class="text-gray-500 uppercase text-sm font-semibold mb-2">Contrat</h3>
                    <p class="font-medium" id="contract-type">CDI</p>
                    <p class="text-gray-600" id="contract-hours">Temps plein - 35h/semaine</p>
                    <p class="text-gray-600 mt-1" id="employee-category">Catégorie: Cadre</p>
                    <p class="text-gray-600" id="employee-echelon">Echelon: 3</p>
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
                        <div class="space-y-3" id="elements-salaire-container">
                            <div class="flex justify-between">
                                <span>Salaire de base</span>
                                <span class="font-medium" id="base-salary">2,500.00 €</span>
                            </div>
                            <!-- Les éléments dynamiques seront ajoutés ici -->
                        </div>
                        
                        <div class="mt-4 pt-3 border-t border-gray-100" id="elements-indemnites-container">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Indemnités</h4>
                            <!-- Les indemnités dynamiques seront ajoutées ici -->
                        </div>
                        
                        <div class="mt-4 pt-3 border-t border-gray-100" id="elements-primes-container">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Primes</h4>
                            <!-- Les primes dynamiques seront ajoutées ici -->
                        </div>
                    </div>
                    
                    <!-- Retenues -->
                    <div>
                        <h3 class="text-lg font-medium text-red-600 mb-4 flex items-center">
                            <i class="fas fa-hand-holding-usd mr-2"></i> Retenues
                        </h3>
                        <div class="space-y-3" id="elements-retenues-container">
                            <!-- Les retenues dynamiques seront ajoutées ici -->
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
                            <p class="text-2xl font-bold text-blue-600" id="net-salary">2,100.00 €</p>
                            <p class="text-sm text-gray-500" id="net-words">Deux mille cent euros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
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
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span>Congés payés acquis</span>
                            <span class="font-medium" id="paid-leave">25 jours</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Congés pris ce mois</span>
                            <span class="font-medium" id="leave-taken">2 jours</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Solde restant</span>
                            <span class="font-medium text-green-600" id="leave-balance">23 jours</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tax Info -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden animate-fade-in delay-300">
                <div class="border-b border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-file-invoice-dollar text-purple-500 mr-3"></i> Fiscalité
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span>Salaire brut annuel</span>
                            <span class="font-medium" id="annual-gross">30,000.00 €</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Taux d'imposition</span>
                            <span class="font-medium" id="tax-rate">11%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Parts fiscales</span>
                            <span class="font-medium" id="tax-shares">1.5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary & Actions -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden animate-fade-in delay-300">
            <div class="p-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <p class="text-gray-600">Date de paiement: <span class="font-medium" id="payment-date">05/02/2023</span></p>
                        <p class="text-gray-600">Mode de paiement: <span class="font-medium" id="payment-method">Virement bancaire</span></p>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="window.print()" class="no-print bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-print mr-2"></i> Imprimer
                        </button>
                        <button id="download-btn" class="no-print bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-download mr-2"></i> Télécharger
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Print Footer -->
        <div class="print-only mt-12 pt-6 border-t border-gray-300 text-center text-sm text-gray-500">
            <p>Document généré le <span id="generation-date">05/02/2023</span></p>
            <p class="mt-1">Ce document fait foi et doit être conservé pendant 5 ans</p>
        </div>
    </div>

    <script>
        // Données réelles du bulletin de paie passées depuis le contrôleur
        const payrollData = @json($payrollData);

        // Populate the template with data
        document.addEventListener('DOMContentLoaded', function() {
            // Employee info
            document.getElementById('employee-name').textContent = payrollData.employee.name;
            document.getElementById('employee-position').textContent = payrollData.employee.position;
            document.getElementById('contract-type').textContent = payrollData.employee.contractType;
            document.getElementById('contract-hours').textContent = payrollData.employee.contractHours;
            
            // Company info
            document.getElementById('company-name').textContent = payrollData.company.name;
            document.getElementById('company-siret').textContent = `SIRET: ${payrollData.company.siret}`;
            
            // Period info
            document.getElementById('date-debut').textContent = payrollData.period.start;
            document.getElementById('date-fin').textContent = payrollData.period.end;
            document.getElementById('numero-paie').textContent = payrollData.period.number;
            
            // Earnings
            document.getElementById('base-salary').textContent = payrollData.earnings.baseSalary.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
            document.getElementById('overtime').textContent = payrollData.earnings.overtime.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
            document.getElementById('bonus').textContent = payrollData.earnings.bonus.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
            document.getElementById('benefits').textContent = payrollData.earnings.benefits.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
            
            // Deductions
            document.getElementById('social-contrib').textContent = '-' + payrollData.deductions.socialContrib.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
            document.getElementById('income-tax').textContent = '-' + payrollData.deductions.incomeTax.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
            document.getElementById('special-deduction').textContent = '-' + payrollData.deductions.specialDeduction.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
            
            // Net salary
            document.getElementById('net-salary').textContent = payrollData.net.amount.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
            document.getElementById('net-words').textContent = payrollData.net.inWords;
            
            // Leave
            document.getElementById('paid-leave').textContent = payrollData.leave.accrued + ' jours';
            document.getElementById('leave-taken').textContent = payrollData.leave.taken + ' jours';
            document.getElementById('leave-balance').textContent = payrollData.leave.balance + ' jours';
            
            // Tax
            document.getElementById('annual-gross').textContent = payrollData.tax.annualGross.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
            document.getElementById('tax-rate').textContent = payrollData.tax.rate;
            document.getElementById('tax-shares').textContent = payrollData.tax.shares;
            
            // Payment
            document.getElementById('payment-date').textContent = payrollData.period.paymentDate;
            document.getElementById('payment-method').textContent = payrollData.payment.method;
            
            // Generation date
            document.getElementById('generation-date').textContent = payrollData.generatedOn;
            
            // Download button functionality
            document.getElementById('download-btn').addEventListener('click', function() {
                alert('Fonctionnalité de téléchargement activée! Dans une application réelle, cela générerait un PDF.');
            });
        });
    </script>
</body>
</html>