@extends('layouts.app')

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboard', () => ({
            isMenuOpen: false,
            employees: [],
            showAddEmployeeModal: false,
            newEmployee: { name: '', email: '', role: 'employee' },
            
            init() {
                // Simulation de données
                this.employees = [
                    { id: 1, name: 'John Doe', email: 'john@example.com', role: 'admin', status: 'active' },
                    { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'employee', status: 'pending' }
                ];
            },
            
            toggleMenu() {
                this.isMenuOpen = !this.isMenuOpen;
            },
            
            addEmployee() {
                if(this.newEmployee.name && this.newEmployee.email) {
                    this.employees.push({
                        id: this.employees.length + 1,
                        ...this.newEmployee,
                        status: 'pending'
                    });
                    this.showAddEmployeeModal = false;
                    this.newEmployee = { name: '', email: '', role: 'employee' };
                }
            }
        }));
    });
</script>
@endpush

@section('content')
<div x-data="dashboard" class="min-h-screen bg-gray-50">
    <!-- Navigation Mobile -->
    <div class="md:hidden bg-white shadow">
        <div class="flex justify-between items-center p-4">
            <h1 class="text-xl font-bold text-indigo-600">GENIUS WORK Dashboard</h1>
            <button @click="toggleMenu" class="p-2 focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Main Layout -->
    <div class="md:flex">
        <!-- Sidebar -->
        <aside :class="{ 'block': isMenuOpen, 'hidden': !isMenuOpen }" class="bg-white w-64 min-h-screen border-r md:block">
            <div class="p-4">
                <h2 class="text-xl font-bold text-indigo-600 mb-6">GENIUS WORK</h2>
                <nav class="space-y-2">
                    <a href="#" class="flex items-center p-2 text-gray-700 bg-indigo-50 rounded-lg">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Tableau de bord
                    </a>
                    <a href="#" class="flex items-center p-2 text-gray-500 hover:bg-indigo-50 rounded-lg">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Employés
                    </a>
                    <a href="#" class="flex items-center p-2 text-gray-500 hover:bg-indigo-50 rounded-lg">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Pointage
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Inscription Status Banner -->
            @if(isset($userData) && isset($entrepriseData) && isset($abonnementData) && isset($facturationData))
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Inscription complétée avec succès</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>Votre compte a été créé et toutes les données ont été enregistrées dans notre système.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Inscription en mode démonstration</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Les données sont actuellement stockées en session. Pour une utilisation réelle, veuillez contacter l'administrateur.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Subscription Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-xl font-bold text-indigo-600 mb-2">Votre abonnement</h2>
                        <p class="text-indigo-600 font-medium">
                            @if(isset($abonnementData) && $abonnementData->planAbonnement)
                                {{ $abonnementData->planAbonnement->nom }}
                            @else
                                {{ $subscription['subscription_plan'] }}
                            @endif
                        </p>
                        <p class="text-gray-600">
                            @if(isset($entrepriseData))
                                {{ json_decode($entrepriseData->configuration, true)['nombre_employes'] ?? $company['company_size'] }} utilisateurs
                            @else
                                {{ $company['company_size'] }} utilisateurs
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-800">
                            @if(isset($abonnementData))
                                {{ number_format($abonnementData->montant) }} FCFA/mois
                            @else
                                {{ number_format($subscription['total_cost']) }} FCFA/mois
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">
                            Prochain paiement : 
                            @if(isset($abonnementData))
                                {{ $abonnementData->date_fin->format('d/m/Y') }}
                            @else
                                {{ now()->addMonth()->format('d/m/Y') }}
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Facture : 
                            @if(isset($facturationData))
                                {{ $facturationData->numero_facture }}
                            @else
                                {{ $payment['invoice_number'] }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Ajouter des employés</h3>
                        <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full">Essentiel</span>
                    </div>
                    <p class="text-gray-600 mb-4">Ajoutez vos premiers employés pour commencer à utiliser GENIUS WORK.</p>
                    <button @click="showAddEmployeeModal = true" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg transition">
                        Ajouter un employé
                    </button>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Configurer les sites</h3>
                        <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded-full">Important</span>
                    </div>
                    <p class="text-gray-600 mb-4">Définissez les emplacements de votre entreprise pour le pointage.</p>
                    <a href="#" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-center transition">
                        Configurer
                    </a>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Télécharger votre facture</h3>
                        <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Administratif</span>
                    </div>
                    <p class="text-gray-600 mb-4">Téléchargez votre facture d'abonnement pour vos archives.</p>
                    <a href="{{ route('workflow.invoice.download') }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-center transition">
                        Télécharger
                    </a>
                </div>
            </div>

            <!-- Company Information -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Informations de l'entreprise</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Nom de l'entreprise</h3>
                        <p class="text-gray-800">
                            @if(isset($entrepriseData))
                                {{ $entrepriseData->nom }}
                            @else
                                {{ $company['company_name'] }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Secteur d'activité</h3>
                        <p class="text-gray-800">
                            @if(isset($entrepriseData))
                                {{ $entrepriseData->secteur_activite }}
                            @else
                                {{ $company['industry'] }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Adresse</h3>
                        <p class="text-gray-800">
                            @if(isset($entrepriseData))
                                {{ $entrepriseData->adresse }}
                            @else
                                {{ $company['address'] }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Contact</h3>
                        <p class="text-gray-800">
                            @if(isset($entrepriseData))
                                {{ $entrepriseData->email }} | {{ $entrepriseData->telephone }}
                            @else
                                {{ $company['contact_email'] }} | {{ $company['contact_phone'] }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Admin User Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Administrateur</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Nom complet</h3>
                        <p class="text-gray-800">
                            @if(isset($userData))
                                {{ $userData->name }}
                            @else
                                {{ $user['full_name'] }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Email</h3>
                        <p class="text-gray-800">
                            @if(isset($userData))
                                {{ $userData->email }}
                            @else
                                {{ $user['email'] }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Rôle</h3>
                        <p class="text-gray-800">
                            @if(isset($userData))
                                {{ ucfirst($userData->role) }}
                            @else
                                Administrateur
                            @endif
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Statut</h3>
                        <p class="text-gray-800">
                            @if(isset($userData))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ ucfirst($userData->statut) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Actif
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Employee Modal -->
    <div x-show="showAddEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Ajouter un employé</h3>
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                    <input type="text" id="name" x-model="newEmployee.name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" x-model="newEmployee.email" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                    <select id="role" x-model="newEmployee.role" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="employee">Employé</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button @click="showAddEmployeeModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Annuler
                </button>
                <button @click="addEmployee" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                    Ajouter
                </button>
            </div>
        </div>
    </div>
</div>
@endsection