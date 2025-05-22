@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-indigo-50/50 to-blue-50/50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12" data-aos="fade-up">
            <h1 class="text-4xl md:text-5xl font-extrabold gradient-text mb-4">
                Genius Work - Gestion de Pointage & RH
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                La solution complète pour la gestion de présence et des ressources humaines adaptée à votre entreprise.
            </p>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden card-hover" data-aos="fade-up" data-aos-delay="100">
            <!-- Hero Image -->
            <div class="relative h-80 bg-indigo-600 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-blue-500 opacity-90"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center text-white px-4" data-aos="fade-up" data-aos-delay="200">
                        <h2 class="text-3xl font-bold mb-4">Simplifiez votre gestion des présences</h2>
                        <p class="text-xl max-w-2xl">Une solution innovante qui transforme la manière dont votre entreprise gère les présences et les ressources humaines.</p>
                    </div>
                </div>
                
                <!-- Animated shapes -->
                <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20 pointer-events-none">
                    <div class="absolute top-10 left-10 w-40 h-40 rounded-full bg-white opacity-20 animate-float-slow"></div>
                    <div class="absolute bottom-10 right-10 w-60 h-60 rounded-full bg-white opacity-10 animate-float"></div>
                    <div class="absolute top-1/2 left-1/3 w-20 h-20 rounded-full bg-white opacity-20 animate-float-fast"></div>
                </div>
            </div>

            <!-- Welcome Content -->
            <div class="p-8">
                <div class="max-w-4xl mx-auto space-y-8">
                    <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                        <h3 class="text-2xl font-semibold text-gray-800 mb-2">Commencez votre parcours avec Genius Work</h3>
                        <p class="text-gray-600 mb-6">
                            En quelques étapes simples, configurez votre compte et découvrez comment Genius Work peut transformer votre gestion RH.
                        </p>
                    </div>

                    <!-- Steps Overview -->
                    <div class="grid md:grid-cols-5 gap-4 mb-8" data-aos="fade-up" data-aos-delay="400">
                        <div class="bg-indigo-50 rounded-lg p-4 text-center border-b-4 border-indigo-500 transform transition-all hover:scale-105 hover:shadow-md">
                            <div class="bg-indigo-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-indigo-700 font-bold">1</span>
                            </div>
                            <h4 class="font-medium text-indigo-800">Compte Utilisateur</h4>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center transform transition-all hover:scale-105 hover:shadow-md hover:bg-indigo-50 hover:border-b-4 hover:border-indigo-500">
                            <div class="bg-gray-200 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 transition-colors group-hover:bg-indigo-100">
                                <span class="text-gray-700 font-bold group-hover:text-indigo-700">2</span>
                            </div>
                            <h4 class="font-medium text-gray-600 group-hover:text-indigo-800">Compte Entreprise</h4>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center transform transition-all hover:scale-105 hover:shadow-md hover:bg-indigo-50 hover:border-b-4 hover:border-indigo-500">
                            <div class="bg-gray-200 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-gray-700 font-bold">3</span>
                            </div>
                            <h4 class="font-medium text-gray-600">Abonnement</h4>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center transform transition-all hover:scale-105 hover:shadow-md hover:bg-indigo-50 hover:border-b-4 hover:border-indigo-500">
                            <div class="bg-gray-200 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-gray-700 font-bold">4</span>
                            </div>
                            <h4 class="font-medium text-gray-600">Paiement</h4>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center transform transition-all hover:scale-105 hover:shadow-md hover:bg-indigo-50 hover:border-b-4 hover:border-indigo-500">
                            <div class="bg-gray-200 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="text-gray-700 font-bold">5</span>
                            </div>
                            <h4 class="font-medium text-gray-600">Tableau de Bord</h4>
                        </div>
                    </div>

                    <!-- Features Overview -->
                    <div class="grid md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-lg p-6 shadow border border-gray-100 card-hover" data-aos="fade-up" data-aos-delay="500">
                            <div class="text-indigo-600 mb-3 bg-indigo-50 p-3 rounded-lg inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-semibold mb-2">Gestion des Présences</h4>
                            <p class="text-gray-600">Suivez en temps réel les présences de vos employés avec une interface intuitive.</p>
                        </div>

                        <div class="bg-white rounded-lg p-6 shadow border border-gray-100 card-hover" data-aos="fade-up" data-aos-delay="600">
                            <div class="text-indigo-600 mb-3 bg-indigo-50 p-3 rounded-lg inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-semibold mb-2">Analyses Détaillées</h4>
                            <p class="text-gray-600">Obtenez des rapports complets et des analyses sur les performances de votre équipe.</p>
                        </div>

                        <div class="bg-white rounded-lg p-6 shadow border border-gray-100 card-hover" data-aos="fade-up" data-aos-delay="700">
                            <div class="text-indigo-600 mb-3 bg-indigo-50 p-3 rounded-lg inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-semibold mb-2">Gestion RH Complète</h4>
                            <p class="text-gray-600">Une solution complète pour toutes vos ressources humaines en un seul endroit.</p>
                        </div>
                    </div>

                    <!-- Call to Action -->
                    <div class="text-center" data-aos="fade-up" data-aos-delay="800">
                        <a href="{{ route('workflow.user') }}" class="inline-flex items-center px-8 py-4 text-lg font-medium rounded-lg text-white btn-primary hover:shadow-xl transform transition hover:-translate-y-1">
                            Créer mon compte
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }
    
    @keyframes float-slow {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
    
    @keyframes float-fast {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-30px); }
        100% { transform: translateY(0px); }
    }
    
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
    
    .animate-float-slow {
        animation: float-slow 8s ease-in-out infinite;
    }
    
    .animate-float-fast {
        animation: float-fast 4s ease-in-out infinite;
    }
</style>
@endsection
