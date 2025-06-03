<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription réussie - GENIUS WORK</title>
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
        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 2;
            stroke-miterlimit: 10;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .checkmark {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: block;
            stroke-width: 2;
            stroke: #fff;
            stroke-miterlimit: 10;
            margin: 10% auto;
            box-shadow: 0 0 0 rgba(79, 70, 229, 0.4);
            animation: scale 0.3s ease-in-out 0.9s both;
        }
        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        @keyframes stroke {
            100% { stroke-dashoffset: 0; }
        }
        @keyframes scale {
            0%, 100% { transform: none; }
            50% { transform: scale3d(1.1, 1.1, 1); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Toast Notification -->

    <!-- Toast de notification -->
    @if(session('success'))
    <div id="toast-success" class="fixed top-4 right-4 z-50 flex items-center w-full max-w-md p-4 mb-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3 text-sm font-normal">{{ session('success') }}</div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-success" aria-label="Close" onclick="this.parentElement.remove();">
            <span class="sr-only">Fermer</span>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
    <script>
        // Faire disparaître le toast après 5 secondes
        setTimeout(function() {
            const toast = document.getElementById('toast-success');
            if (toast) {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(function() {
                    toast.remove();
                }, 500);
            }
        }, 5000);
    </script>
    @endif
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Success Card -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg transform transition-all duration-300 hover:shadow-2xl">
            <!-- Header with gradient background -->
            <div class="gradient-bg px-6 py-12 sm:px-20 text-center text-white">
                <div class="relative">
                    <svg class="checkmark mx-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark-circle" fill="none" cx="26" cy="26" r="25"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold mt-6 mb-4">Félicitations !</h1>
                <p class="text-lg md:text-xl opacity-90">
                    Votre inscription à GENIUS WORK a été complétée avec succès.
                </p>
            </div>

            <!-- Content -->
            <div class="px-6 py-8 sm:px-12">
                <!-- Subscription Details -->
                <div class="mb-12">
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0 bg-indigo-100 p-3 rounded-lg">
                            <i class="fas fa-id-card text-indigo-600 text-xl"></i>
                        </div>
                        <h2 class="ml-3 text-2xl font-semibold text-gray-800">Détails de votre abonnement</h2>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Entreprise</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $entreprise->nom }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Plan d'abonnement</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $abonnement->planAbonnement->nom }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Date de début</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $abonnement->date_debut->format('d/m/Y') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Date de fin</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $abonnement->date_fin->format('d/m/Y') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Nombre d'employés</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $abonnement->nombre_employes }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Statut</h3>
                                <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Actif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="mb-12">
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0 bg-indigo-100 p-3 rounded-lg">
                            <i class="fas fa-credit-card text-indigo-600 text-xl"></i>
                        </div>
                        <h2 class="ml-3 text-2xl font-semibold text-gray-800">Détails du paiement</h2>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Numéro de facture</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $facture->numero }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Date de facturation</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $facture->date_facturation->format('d/m/Y') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Méthode de paiement</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    <i class="fas fa-mobile-alt mr-1 text-indigo-600"></i> {{ $facture->methode }}
                                </p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Référence de paiement</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $facture->reference_paiement }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Montant payé</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="text-sm font-medium text-gray-500">Statut</h3>
                                <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Payé
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Action Buttons -->
                 <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                    <button onclick="genererFacturePDF()" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition transform hover:-translate-y-1">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Télécharger la facture
                    </button>
                    <a href="{{ url('/admin') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 rounded-lg font-semibold text-gray-700 uppercase tracking-widest hover:bg-gray-50 active:bg-gray-100 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition transform hover:-translate-y-1">
                        <i class="fas fa-tachometer-alt mr-2 text-indigo-600"></i>
                        Accéder au tableau de bord
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-center">
                <p class="text-gray-500 text-sm">
                    Besoin d'aide ? Contactez notre <a href="https://api.whatsapp.com/message/JQEND7QOA3QQG1" class="text-indigo-600 hover:text-indigo-800">support client</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-dismiss toast after 5 seconds
        setTimeout(function() {
            const toast = document.getElementById('toast-success');
            if (toast) {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(function() {
                    toast.remove();
                }, 500);
            }
        }, 5000);

        // Add hover effect to cards
        document.querySelectorAll('.bg-white.p-4.rounded-lg').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.classList.add('shadow-md', 'border-indigo-200');
                card.classList.remove('shadow-sm');
            });
            card.addEventListener('mouseleave', () => {
                card.classList.remove('shadow-md', 'border-indigo-200');
                card.classList.add('shadow-sm');
            });
        });

        // Fonction pour générer et télécharger la facture en PDF
        function genererFacturePDF() {
            // Afficher un message pendant la génération
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 z-50 flex items-center w-full max-w-md p-4 mb-4 text-gray-500 bg-white rounded-lg shadow-lg animate-fade-in';
            toast.innerHTML = `
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-blue-500 bg-blue-100 rounded-lg">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="ml-3 text-sm font-normal">Génération de la facture en cours...</div>
            `;
            document.body.appendChild(toast);

            // Utilisation de jsPDF et html2canvas pour générer le PDF
            const { jsPDF } = window.jspdf;
            
            const element = document.getElementById('facture-pdf');
            const opt = {
                scale: 2,
                useCORS: true,
                logging: true,
                letterRendering: true
            };

            html2canvas(element, opt).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save('facture-genius-work.pdf');
                
                // Supprimer le toast et afficher un message de succès
                toast.remove();
                
                const successToast = document.createElement('div');
                successToast.className = 'fixed top-4 right-4 z-50 flex items-center w-full max-w-md p-4 mb-4 text-gray-500 bg-white rounded-lg shadow-lg animate-fade-in';
                successToast.innerHTML = `
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="ml-3 text-sm font-normal">Facture téléchargée avec succès!</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" onclick="this.parentElement.remove();">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                document.body.appendChild(successToast);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    successToast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => successToast.remove(), 500);
                }, 5000);
            }).catch(error => {
                console.error('Erreur lors de la génération du PDF:', error);
                toast.remove();
                
                const errorToast = document.createElement('div');
                errorToast.className = 'fixed top-4 right-4 z-50 flex items-center w-full max-w-md p-4 mb-4 text-gray-500 bg-white rounded-lg shadow-lg animate-fade-in';
                errorToast.innerHTML = `
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="ml-3 text-sm font-normal">Erreur lors de la génération de la facture</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" onclick="this.parentElement.remove();">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                document.body.appendChild(errorToast);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    errorToast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => errorToast.remove(), 500);
                }, 5000);
            });
        }
    </script>
</body>
</html>