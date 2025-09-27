@extends('mobile.pointage.layout')

@section('title', 'Pointage réussi')

@section('content')
<div class="max-w-md mx-auto">
    <!-- Success Card -->
    <div class="card text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check text-green-600 text-3xl"></i>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            @if($type === 'sortie')
                Sortie enregistrée !
            @else
                Entrée enregistrée !
            @endif
        </h2>
        
        <p class="text-gray-600 mb-6">
            Votre pointage a été enregistré avec succès.
        </p>
        
        <!-- Employee Info -->
        @if($employeur)
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-center mb-3">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div class="text-left">
                    <p class="font-semibold text-gray-800">{{ $employeur }}</p>
                    <p class="text-sm text-gray-500">{{ $site ?? 'Site' }}</p>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Pointage Details -->
        <div class="space-y-4 mb-6">
            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                <span class="text-gray-600">Type de pointage</span>
                <span class="font-semibold text-gray-800">
                    @if($type === 'sortie')
                        <i class="fas fa-sign-out-alt text-red-500 mr-1"></i>
                        Sortie
                    @else
                        <i class="fas fa-sign-in-alt text-green-500 mr-1"></i>
                        Entrée
                    @endif
                </span>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                <span class="text-gray-600">Heure</span>
                <span class="font-semibold text-gray-800">
                    <i class="fas fa-clock text-blue-500 mr-1"></i>
                    {{ now()->format('H:i') }}
                </span>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                <span class="text-gray-600">Date</span>
                <span class="font-semibold text-gray-800">
                    <i class="fas fa-calendar text-purple-500 mr-1"></i>
                    {{ now()->format('d/m/Y') }}
                </span>
            </div>
            
            @if($type === 'sortie' && $heures)
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-600">Temps travaillé</span>
                <span class="font-semibold text-green-600">
                    <i class="fas fa-hourglass-half text-green-500 mr-1"></i>
                    {{ $heures }}
                </span>
            </div>
            @endif
        </div>
        
        <!-- Success Message -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-800 font-medium">
                @if($type === 'sortie')
                    <i class="fas fa-thumbs-up mr-2"></i>
                    Bonne fin de journée ! Votre temps de travail a été comptabilisé.
                @else
                    <i class="fas fa-coffee mr-2"></i>
                    Bon travail ! Votre présence a été enregistrée.
                @endif
            </p>
        </div>
        
        <!-- Actions -->
        <div class="space-y-3">
            @if($type === 'entree')
            <button onclick="showWorkingTime()" class="w-full btn-primary">
                <i class="fas fa-chart-line mr-2"></i>
                Voir mes heures de travail
            </button>
            @endif
            
            <button onclick="showHistory()" class="w-full btn-secondary">
                <i class="fas fa-history mr-2"></i>
                Historique des pointages
            </button>
            
            <a href="/mobile/pointage/scanner" class="block w-full btn-secondary text-center">
                <i class="fas fa-qrcode mr-2"></i>
                Scanner un autre QR code
            </a>
        </div>
    </div>
    
    <!-- Working Time Modal (Hidden) -->
    <div id="workingTimeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 m-4 max-w-sm w-full">
            <div class="text-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Temps de travail aujourd'hui</h3>
            </div>
            
            <div id="workingTimeContent" class="space-y-3">
                <!-- Content will be loaded here -->
            </div>
            
            <button onclick="closeModal('workingTimeModal')" class="w-full btn-secondary mt-4">
                Fermer
            </button>
        </div>
    </div>
    
    <!-- History Modal (Hidden) -->
    <div id="historyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 m-4 max-w-md w-full max-h-96 overflow-y-auto">
            <div class="text-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Historique des pointages</h3>
            </div>
            
            <div id="historyContent" class="space-y-3">
                <!-- Content will be loaded here -->
            </div>
            
            <button onclick="closeModal('historyModal')" class="w-full btn-secondary mt-4">
                Fermer
            </button>
        </div>
    </div>
</div>

<!-- Auto-redirect message -->
<div class="max-w-md mx-auto mt-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
        <p class="text-blue-800 text-sm">
            <i class="fas fa-info-circle mr-1"></i>
            Cette page se fermera automatiquement dans <span id="countdown">30</span> secondes
        </p>
        <button onclick="clearInterval(countdownInterval)" class="text-blue-600 hover:text-blue-800 text-sm mt-2">
            Annuler la fermeture automatique
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const employeurId = getEmployeurIdFromStorage(); // We'll need to store this during auth
    
    // Countdown for auto-close
    let countdown = 30;
    const countdownElement = document.getElementById('countdown');
    
    window.countdownInterval = setInterval(() => {
        countdown--;
        countdownElement.textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(window.countdownInterval);
            window.close();
        }
    }, 1000);
    
    // Show working time modal
    window.showWorkingTime = async function() {
        if (!employeurId) {
            utils.showToast('Impossible de récupérer les informations employé', 'error');
            return;
        }
        
        try {
            utils.showLoading();
            
            const today = new Date();
            const startOfDay = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            const endOfDay = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59);
            
            const response = await api.post('/mobile/pointage/working-hours', {
                employeur_id: employeurId,
                date_debut: startOfDay.toISOString(),
                date_fin: endOfDay.toISOString()
            });
            
            if (!response.success) {
                throw new Error(response.message || 'Erreur lors du calcul des heures');
            }
            
            const data = response.data;
            const content = document.getElementById('workingTimeContent');
            
            content.innerHTML = `
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-gray-600">Heures travaillées</span>
                        <span class="font-semibold text-green-600">${data.total_heures}h</span>
                    </div>
                    
                    ${data.heures_supplementaires > 0 ? `
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-gray-600">Heures supplémentaires</span>
                        <span class="font-semibold text-orange-600">${data.heures_supplementaires}h</span>
                    </div>
                    ` : ''}
                    
                    ${data.minutes_retard > 0 ? `
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <span class="text-gray-600">Retard</span>
                        <span class="font-semibold text-red-600">${data.minutes_retard} min</span>
                    </div>
                    ` : ''}
                    
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">Jours travaillés</span>
                        <span class="font-semibold text-blue-600">${data.jours_travailles}</span>
                    </div>
                </div>
            `;
            
            document.getElementById('workingTimeModal').classList.remove('hidden');
            
        } catch (error) {
            console.error('Working time error:', error);
            utils.showToast(error.message || 'Erreur lors du calcul des heures', 'error');
        } finally {
            utils.hideLoading();
        }
    };
    
    // Show history modal
    window.showHistory = async function() {
        if (!employeurId) {
            utils.showToast('Impossible de récupérer l\'historique', 'error');
            return;
        }
        
        try {
            utils.showLoading();
            
            const response = await api.post('/mobile/pointage/history', {
                employeur_id: employeurId,
                limit: 10
            });
            
            if (!response.success) {
                throw new Error(response.message || 'Erreur lors de la récupération de l\'historique');
            }
            
            const presences = response.data.presences;
            const content = document.getElementById('historyContent');
            
            if (presences.length === 0) {
                content.innerHTML = `
                    <div class="text-center py-6">
                        <i class="fas fa-inbox text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">Aucun pointage trouvé</p>
                    </div>
                `;
            } else {
                content.innerHTML = presences.map(presence => `
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium text-gray-800">${presence.site.nom}</p>
                                <p class="text-sm text-gray-500">${utils.formatDate(presence.date_entree)}</p>
                            </div>
                            <span class="status-${presence.statut === 'present' ? 'success' : presence.statut === 'retard' ? 'warning' : 'error'}">
                                ${presence.statut}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Entrée:</span>
                                <span class="font-medium">${utils.formatTime(presence.date_entree)}</span>
                            </div>
                            ${presence.date_sortie ? `
                            <div>
                                <span class="text-gray-500">Sortie:</span>
                                <span class="font-medium">${utils.formatTime(presence.date_sortie)}</span>
                            </div>
                            ` : '<div class="text-orange-600 font-medium">En cours...</div>'}
                        </div>
                        
                        ${presence.minutes_travaillees ? `
                        <div class="mt-2 text-sm">
                            <span class="text-gray-500">Temps travaillé:</span>
                            <span class="font-medium text-green-600">${utils.formatDuration(presence.minutes_travaillees)}</span>
                        </div>
                        ` : ''}
                    </div>
                `).join('');
            }
            
            document.getElementById('historyModal').classList.remove('hidden');
            
        } catch (error) {
            console.error('History error:', error);
            utils.showToast(error.message || 'Erreur lors de la récupération de l\'historique', 'error');
        } finally {
            utils.hideLoading();
        }
    };
    
    // Close modal function
    window.closeModal = function(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    };
    
    // Get employeur ID from localStorage (should be set during authentication)
    function getEmployeurIdFromStorage() {
        try {
            const employeurData = localStorage.getItem('currentEmployeur');
            if (employeurData) {
                const employeur = JSON.parse(employeurData);
                return employeur.id;
            }
        } catch (error) {
            console.error('Error getting employeur from storage:', error);
        }
        return null;
    }
});
</script>
@endpush
