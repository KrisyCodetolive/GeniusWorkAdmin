@extends('mobile.pointage.layout')

@section('title', 'Scanner QR Code')

@section('content')
<div class="max-w-md mx-auto">
    <!-- Scanner Card -->
    <div class="card">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-qrcode text-blue-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Scanner QR Code</h2>
            <p class="text-gray-600">Scannez le QR code du site pour pointer</p>
        </div>
        
        <!-- Camera Section -->
        <div class="mb-6">
            <div id="cameraContainer" class="relative bg-black rounded-lg overflow-hidden" style="aspect-ratio: 1;">
                <video id="cameraVideo" class="w-full h-full object-cover" autoplay muted playsinline></video>
                
                <!-- Scanning Overlay -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-48 h-48 border-2 border-white rounded-lg relative">
                        <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-blue-500 rounded-tl-lg"></div>
                        <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-blue-500 rounded-tr-lg"></div>
                        <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-blue-500 rounded-bl-lg"></div>
                        <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-blue-500 rounded-br-lg"></div>
                        
                        <!-- Scanning Line -->
                        <div class="absolute inset-x-0 top-0 h-0.5 bg-blue-500 animate-pulse" id="scanLine"></div>
                    </div>
                </div>
                
                <!-- Camera Status -->
                <div id="cameraStatus" class="absolute inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
                    <div class="text-center text-white">
                        <div class="spinner mx-auto mb-4"></div>
                        <p>Initialisation de la caméra...</p>
                    </div>
                </div>
            </div>
            
            <!-- Camera Controls -->
            <div class="flex justify-center space-x-4 mt-4">
                <button id="toggleCameraButton" class="btn-secondary" disabled>
                    <i class="fas fa-camera mr-2"></i>
                    <span id="cameraButtonText">Démarrer</span>
                </button>
                
                <button id="switchCameraButton" class="btn-secondary hidden">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Changer
                </button>
            </div>
        </div>
        
        <!-- Manual Input Section -->
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-keyboard mr-2"></i>
                Saisie manuelle
            </h3>
            
            <form id="manualForm" class="space-y-4">
                <div>
                    <label for="qrUrl" class="block text-sm font-medium text-gray-700 mb-2">
                        URL du QR code ou lien de pointage
                    </label>
                    <input 
                        type="url" 
                        id="qrUrl" 
                        name="qrUrl" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="https://..."
                    >
                </div>
                
                <button type="submit" class="w-full btn-primary">
                    <i class="fas fa-arrow-right mr-2"></i>
                    Accéder au pointage
                </button>
            </form>
        </div>
    </div>
    
    <!-- Instructions Card -->
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
            Instructions
        </h3>
        
        <div class="space-y-3 text-sm text-gray-600">
            <div class="flex items-start">
                <i class="fas fa-circle text-blue-500 text-xs mt-2 mr-3"></i>
                <p>Pointez votre caméra vers le QR code affiché sur le site</p>
            </div>
            <div class="flex items-start">
                <i class="fas fa-circle text-blue-500 text-xs mt-2 mr-3"></i>
                <p>Assurez-vous que le QR code est bien visible et éclairé</p>
            </div>
            <div class="flex items-start">
                <i class="fas fa-circle text-blue-500 text-xs mt-2 mr-3"></i>
                <p>Le scan se fera automatiquement une fois le code détecté</p>
            </div>
            <div class="flex items-start">
                <i class="fas fa-circle text-blue-500 text-xs mt-2 mr-3"></i>
                <p>Vous pouvez aussi saisir manuellement l'URL si nécessaire</p>
            </div>
        </div>
    </div>
    
    <!-- Recent Scans -->
    <div class="card" id="recentScansCard" style="display: none;">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-history mr-2 text-purple-600"></i>
            Scans récents
        </h3>
        
        <div id="recentScansList" class="space-y-2">
            <!-- Recent scans will be populated here -->
        </div>
        
        <button onclick="clearRecentScans()" class="text-red-600 hover:text-red-800 text-sm mt-3">
            <i class="fas fa-trash mr-1"></i>
            Effacer l'historique
        </button>
    </div>
</div>

<!-- Hidden canvas for QR code processing -->
<canvas id="qrCanvas" style="display: none;"></canvas>
@endsection

@push('scripts')
<!-- QR Code Scanner Library -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let video = document.getElementById('cameraVideo');
    let canvas = document.getElementById('qrCanvas');
    let context = canvas.getContext('2d');
    let currentStream = null;
    let scanning = false;
    let cameras = [];
    let currentCameraIndex = 0;
    
    // Initialize camera
    async function initCamera() {
        try {
            // Get available cameras
            const devices = await navigator.mediaDevices.enumerateDevices();
            cameras = devices.filter(device => device.kind === 'videoinput');
            
            if (cameras.length === 0) {
                throw new Error('Aucune caméra trouvée');
            }
            
            // Show switch camera button if multiple cameras
            if (cameras.length > 1) {
                document.getElementById('switchCameraButton').classList.remove('hidden');
            }
            
            await startCamera();
            
        } catch (error) {
            console.error('Camera initialization error:', error);
            showCameraError(error.message);
        }
    }
    
    // Start camera
    async function startCamera() {
        try {
            // Stop existing stream
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }
            
            const constraints = {
                video: {
                    deviceId: cameras[currentCameraIndex]?.deviceId,
                    facingMode: cameras.length > 1 ? 'environment' : 'user',
                    width: { ideal: 640 },
                    height: { ideal: 640 }
                }
            };
            
            currentStream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = currentStream;
            
            video.onloadedmetadata = () => {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                document.getElementById('cameraStatus').style.display = 'none';
                document.getElementById('toggleCameraButton').disabled = false;
                document.getElementById('cameraButtonText').textContent = 'Arrêter';
                startScanning();
            };
            
        } catch (error) {
            console.error('Camera start error:', error);
            showCameraError('Impossible d\'accéder à la caméra');
        }
    }
    
    // Show camera error
    function showCameraError(message) {
        const status = document.getElementById('cameraStatus');
        status.innerHTML = `
            <div class="text-center text-white">
                <i class="fas fa-exclamation-triangle text-red-400 text-3xl mb-3"></i>
                <p class="mb-2">Erreur caméra</p>
                <p class="text-sm text-gray-300">${message}</p>
                <button onclick="initCamera()" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
                    Réessayer
                </button>
            </div>
        `;
        status.style.display = 'flex';
    }
    
    // Start QR code scanning
    function startScanning() {
        scanning = true;
        scanQRCode();
    }
    
    // Stop scanning
    function stopScanning() {
        scanning = false;
    }
    
    // Scan QR code
    function scanQRCode() {
        if (!scanning || video.readyState !== video.HAVE_ENOUGH_DATA) {
            if (scanning) {
                requestAnimationFrame(scanQRCode);
            }
            return;
        }
        
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        
        if (code) {
            handleQRCodeDetected(code.data);
        } else {
            requestAnimationFrame(scanQRCode);
        }
    }
    
    // Handle QR code detection
    function handleQRCodeDetected(data) {
        stopScanning();
        
        // Vibrate if supported
        if (navigator.vibrate) {
            navigator.vibrate(200);
        }
        
        // Save to recent scans
        saveRecentScan(data);
        
        // Process QR code data
        processQRCode(data);
    }
    
    // Process QR code
    function processQRCode(data) {
        try {
            // Check if it's a URL
            if (data.startsWith('http://') || data.startsWith('https://')) {
                window.location.href = data;
                return;
            }
            
            // Try to parse as JSON (for embedded QR data)
            try {
                const qrData = JSON.parse(data);
                if (qrData.url) {
                    window.location.href = qrData.url;
                    return;
                }
            } catch (e) {
                // Not JSON, continue
            }
            
            // Show error for unrecognized format
            utils.showToast('Format de QR code non reconnu', 'error');
            setTimeout(() => startScanning(), 2000);
            
        } catch (error) {
            console.error('QR processing error:', error);
            utils.showToast('Erreur lors du traitement du QR code', 'error');
            setTimeout(() => startScanning(), 2000);
        }
    }
    
    // Save recent scan
    function saveRecentScan(data) {
        try {
            let recentScans = JSON.parse(localStorage.getItem('recentQRScans') || '[]');
            
            // Add new scan
            const scan = {
                data: data,
                timestamp: new Date().toISOString(),
                url: data.startsWith('http') ? data : null
            };
            
            // Remove duplicates and limit to 5
            recentScans = recentScans.filter(s => s.data !== data);
            recentScans.unshift(scan);
            recentScans = recentScans.slice(0, 5);
            
            localStorage.setItem('recentQRScans', JSON.stringify(recentScans));
            updateRecentScansDisplay();
            
        } catch (error) {
            console.error('Error saving recent scan:', error);
        }
    }
    
    // Update recent scans display
    function updateRecentScansDisplay() {
        try {
            const recentScans = JSON.parse(localStorage.getItem('recentQRScans') || '[]');
            const container = document.getElementById('recentScansList');
            const card = document.getElementById('recentScansCard');
            
            if (recentScans.length === 0) {
                card.style.display = 'none';
                return;
            }
            
            container.innerHTML = recentScans.map(scan => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">
                            ${scan.url ? new URL(scan.url).hostname : 'QR Code'}
                        </p>
                        <p class="text-xs text-gray-500">
                            ${utils.formatDate(scan.timestamp)}
                        </p>
                    </div>
                    <button onclick="processQRCode('${scan.data.replace(/'/g, "\\'")}')" 
                            class="ml-3 text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            `).join('');
            
            card.style.display = 'block';
            
        } catch (error) {
            console.error('Error updating recent scans:', error);
        }
    }
    
    // Clear recent scans
    window.clearRecentScans = function() {
        localStorage.removeItem('recentQRScans');
        document.getElementById('recentScansCard').style.display = 'none';
        utils.showToast('Historique effacé', 'success');
    };
    
    // Toggle camera
    document.getElementById('toggleCameraButton').addEventListener('click', function() {
        if (scanning) {
            stopScanning();
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }
            document.getElementById('cameraButtonText').textContent = 'Démarrer';
            document.getElementById('cameraStatus').style.display = 'flex';
            document.getElementById('cameraStatus').innerHTML = `
                <div class="text-center text-white">
                    <i class="fas fa-camera-slash text-gray-400 text-3xl mb-3"></i>
                    <p>Caméra arrêtée</p>
                </div>
            `;
        } else {
            startCamera();
        }
    });
    
    // Switch camera
    document.getElementById('switchCameraButton').addEventListener('click', function() {
        currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
        startCamera();
    });
    
    // Manual form
    document.getElementById('manualForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const url = document.getElementById('qrUrl').value.trim();
        
        if (!url) {
            utils.showToast('Veuillez saisir une URL', 'error');
            return;
        }
        
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            utils.showToast('URL invalide', 'error');
            return;
        }
        
        processQRCode(url);
    });
    
    // Initialize
    initCamera();
    updateRecentScansDisplay();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }
    });
});
</script>
@endpush
