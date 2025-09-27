@extends('mobile.pointage.layout')

@section('title', 'Erreur')

@section('content')
<div class="max-w-md mx-auto">
    <!-- Error Card -->
    <div class="card text-center">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            {{ $error ?? 'Erreur' }}
        </h2>
        
        <p class="text-gray-600 mb-6">
            {{ $message ?? 'Une erreur est survenue lors du pointage.' }}
        </p>
        
        <!-- Error Details -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-red-500 mt-1 mr-3"></i>
                <div class="text-left">
                    <p class="text-red-800 font-medium mb-2">Que faire maintenant ?</p>
                    <ul class="text-red-700 text-sm space-y-1">
                        <li>• Vérifiez votre connexion internet</li>
                        <li>• Assurez-vous d'être sur le bon site</li>
                        <li>• Demandez un nouveau QR code si nécessaire</li>
                        <li>• Contactez votre administrateur si le problème persiste</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="space-y-3">
            <button onclick="history.back()" class="w-full btn-primary">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </button>
            
            <a href="/mobile/pointage/scanner" class="block w-full btn-secondary text-center">
                <i class="fas fa-qrcode mr-2"></i>
                Scanner un nouveau QR code
            </a>
            
            <button onclick="location.reload()" class="w-full btn-secondary">
                <i class="fas fa-redo mr-2"></i>
                Réessayer
            </button>
            
            <a href="/mobile/pointage/help" class="block w-full text-blue-600 hover:text-blue-800 text-center py-2">
                <i class="fas fa-question-circle mr-2"></i>
                Besoin d'aide ?
            </a>
        </div>
    </div>
    
    <!-- Technical Details (Collapsible) -->
    <div class="mt-6">
        <button onclick="toggleTechnicalDetails()" class="w-full text-left text-gray-500 hover:text-gray-700 text-sm">
            <i class="fas fa-chevron-down mr-2" id="technicalToggleIcon"></i>
            Détails techniques
        </button>
        
        <div id="technicalDetails" class="hidden mt-3 bg-gray-100 rounded-lg p-4">
            <div class="text-sm text-gray-600 space-y-2">
                <div>
                    <span class="font-medium">Heure:</span>
                    <span>{{ now()->format('d/m/Y H:i:s') }}</span>
                </div>
                <div>
                    <span class="font-medium">Navigateur:</span>
                    <span id="browserInfo">-</span>
                </div>
                <div>
                    <span class="font-medium">URL:</span>
                    <span class="break-all">{{ request()->fullUrl() }}</span>
                </div>
                @if(isset($error))
                <div>
                    <span class="font-medium">Code d'erreur:</span>
                    <span>{{ $error }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set browser info
    document.getElementById('browserInfo').textContent = navigator.userAgent;
    
    // Toggle technical details
    window.toggleTechnicalDetails = function() {
        const details = document.getElementById('technicalDetails');
        const icon = document.getElementById('technicalToggleIcon');
        
        if (details.classList.contains('hidden')) {
            details.classList.remove('hidden');
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            details.classList.add('hidden');
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    };
});
</script>
@endpush
