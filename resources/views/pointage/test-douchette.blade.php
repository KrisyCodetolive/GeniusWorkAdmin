<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Pointage Douchette - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            .badge-print {
                page-break-after: always;
                display: flex !important;
                align-items: center;
                justify-content: center;
                height: 100vh;
            }
            .badge-print:last-child { page-break-after: avoid; }
        }
        .badge-card {
            width: 85mm;
            height: 54mm;
            border: 2px solid #1e40af;
            border-radius: 8px;
            padding: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            background: white;
        }
        .scan-flash {
            animation: flash 0.5s ease-in-out;
        }
        @keyframes flash {
            0%, 100% { background-color: transparent; }
            50% { background-color: #10b981; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-7xl">

        <!-- Header -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Test Pointage - Douchette</h1>
                    <p class="text-gray-500 mt-1">Site: <strong>{{ $site->nom }}</strong> | Token: <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $site->qr_token }}</code></p>
                </div>
                <div class="flex gap-2">
                    <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition no-print">
                        Imprimer les badges
                    </button>
                    <button onclick="resetPresences()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition no-print">
                        Réinitialiser
                    </button>
                    <a href="{{ url('/admin') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition no-print">
                        Retour admin
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Zone de scan (gauche) -->
            <div class="bg-white rounded-xl shadow-md p-6 no-print">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Scanner un badge</h2>

                <!-- Input pour la douchette -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zone de scan (la douchette simule un clavier)</label>
                    <input
                        type="text"
                        id="scanner-input"
                        class="w-full px-4 py-4 text-xl text-center border-2 border-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 focus:border-blue-600 outline-none"
                        placeholder="Scannez ou tapez le code..."
                        autocomplete="off"
                        autofocus>
                    <p class="text-xs text-gray-500 mt-2">La douchette envoie le code + Entrée automatiquement</p>
                </div>

                <!-- Bouton de test manuel -->
                <div class="mb-4">
                    <button onclick="processManualScan()" class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:scale-105 transition transform">
                        Traiter le scan
                    </button>
                </div>

                <!-- Résultat -->
                <div id="result-area" class="hidden p-4 rounded-lg">
                    <div id="result-icon" class="inline-flex items-center justify-center w-12 h-12 rounded-full mb-2"></div>
                    <h3 id="result-title" class="text-lg font-semibold"></h3>
                    <p id="result-message" class="text-gray-600"></p>
                    <div id="result-details" class="mt-3 text-sm text-gray-500"></div>
                </div>

                <!-- Logs de debug -->
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Logs</h3>
                    <div id="log-area" class="bg-gray-900 text-green-400 font-mono text-xs p-3 rounded-lg h-32 overflow-y-auto">
                        <div>En attente de scan...</div>
                    </div>
                </div>
            </div>

            <!-- Présences du jour (droite) -->
            <div class="bg-white rounded-xl shadow-md p-6 no-print">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Présences du jour</h2>
                    <button onclick="loadPresences()" class="text-blue-600 hover:text-blue-800 text-sm">
                        Rafraîchir
                    </button>
                </div>
                <div id="presences-list" class="space-y-2">
                    <div class="text-center text-gray-400 py-8">Aucune présence enregistrée</div>
                </div>
            </div>
        </div>

        <!-- Badges imprimables -->
        <div class="bg-white rounded-xl shadow-md p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 no-print">Badges QR Code à imprimer</h2>
            <p class="text-sm text-gray-500 mb-4 no-print">Imprimez ces badges, puis scannez-les avec la douchette. Le QR code contient le <code>qr_code_secret</code> de l'employeur.</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($badges as $badge)
                <div class="badge-print badge-card mx-auto">
                    <div class="text-center">
                        <div class="text-xs font-bold text-blue-900">{{ $badge['employeur']->prenom }} {{ $badge['employeur']->nom }}</div>
                        <div class="text-[10px] text-gray-500">{{ $badge['employeur']->code_employe }}</div>
                    </div>
                    <div class="my-1">{!! $badge['qr_code'] !!}</div>
                    <div class="text-[8px] text-gray-400 text-center break-all">{{ $badge['secret'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Liste des secrets pour test rapide -->
        <div class="bg-white rounded-xl shadow-md p-6 mt-6 no-print">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Codes de test rapide</h2>
            <p class="text-sm text-gray-500 mb-4">Cliquez sur un code pour simuler un scan, ou copiez-le dans la zone de scan.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($badges as $badge)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-blue-50 transition cursor-pointer"
                     onclick="simulateScan('{{ $badge['secret'] }}')">
                    <div>
                        <div class="font-medium text-gray-800">{{ $badge['employeur']->prenom }} {{ $badge['employeur']->nom }}</div>
                        <div class="text-xs text-gray-500">{{ $badge['employeur']->code_employe }}</div>
                    </div>
                    <div class="text-xs font-mono text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ $badge['secret'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const siteId = '{{ $site->id }}';
        let scanning = true;

        // --- Gestion de la douchette ---
        const scannerInput = document.getElementById('scanner-input');
        let scanBuffer = '';
        let lastKeyTime = Date.now();
        const SCAN_TIMEOUT = 50; // ms entre touches pour détecter la douchette

        // Maintien du focus
        document.addEventListener('click', (e) => {
            if (e.target !== scannerInput && !e.target.closest('button')) {
                scannerInput.focus();
            }
        });

        // Détection douchette : frappe rapide + Entrée
        scannerInput.addEventListener('input', function(e) {
            const now = Date.now();
            if (now - lastKeyTime > SCAN_TIMEOUT) {
                scanBuffer = '';
            }
            lastKeyTime = now;
            scanBuffer += e.target.value;
            e.target.value = '';
        });

        scannerInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = scanBuffer.trim() || scannerInput.value.trim();
                if (code) {
                    processScan(code);
                    scanBuffer = '';
                    scannerInput.value = '';
                }
            }
        });

        // --- Traitement du scan ---
        function processScan(code) {
            if (!scanning) return;
            scanning = false;

            log(`Scan reçu: ${code.substring(0, 20)}...`);

            fetch('/pointage/test/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    idno: code,
                    site_id: siteId,
                })
            })
            .then(r => r.json())
            .then(data => {
                showResult(data);
                loadPresences();
                scanning = true;
                scannerInput.focus();
            })
            .catch(err => {
                showResult({ status: 'error', message: err.message });
                scanning = true;
                scannerInput.focus();
            });
        }

        function processManualScan() {
            const code = scannerInput.value.trim();
            if (code) {
                processScan(code);
            }
        }

        function simulateScan(secret) {
            scannerInput.value = secret;
            processScan(secret);
        }

        // --- Affichage des résultats ---
        function showResult(data) {
            const area = document.getElementById('result-area');
            const icon = document.getElementById('result-icon');
            const title = document.getElementById('result-title');
            const msg = document.getElementById('result-message');
            const details = document.getElementById('result-details');

            area.classList.remove('hidden');

            if (data.status === 'success') {
                area.className = 'p-4 rounded-lg bg-green-50 border border-green-200';
                icon.className = 'inline-flex items-center justify-center w-12 h-12 rounded-full mb-2 bg-green-500 text-white';
                icon.innerHTML = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                title.textContent = 'Pointage réussi';
                title.className = 'text-lg font-semibold text-green-800';
                msg.textContent = data.data?.employee || 'Employé identifié';
                msg.className = 'text-gray-700';
                details.innerHTML = `Type: <strong>${data.data?.type || 'N/A'}</strong> | Heure: ${new Date().toLocaleTimeString('fr-FR')}`;
            } else {
                area.className = 'p-4 rounded-lg bg-red-50 border border-red-200';
                icon.className = 'inline-flex items-center justify-center w-12 h-12 rounded-full mb-2 bg-red-500 text-white';
                icon.innerHTML = '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                title.textContent = 'Erreur';
                title.className = 'text-lg font-semibold text-red-800';
                msg.textContent = data.message || 'Erreur inconnue';
                msg.className = 'text-red-700';
                details.textContent = '';
            }

            log(`Résultat: ${data.status} - ${data.message || data.data?.employee || ''}`);
        }

        // --- Logs ---
        function log(msg) {
            const area = document.getElementById('log-area');
            const time = new Date().toLocaleTimeString('fr-FR');
            const line = document.createElement('div');
            line.textContent = `[${time}] ${msg}`;
            area.appendChild(line);
            area.scrollTop = area.scrollHeight;
        }

        // --- Présences du jour ---
        function loadPresences() {
            fetch(`/pointage/test/presences?site_id=${siteId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                const list = document.getElementById('presences-list');
                if (data.count === 0) {
                    list.innerHTML = '<div class="text-center text-gray-400 py-8">Aucune présence enregistrée</div>';
                    return;
                }
                list.innerHTML = data.data.map(p => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-800">${p.employe}</div>
                            <div class="text-xs text-gray-500">${p.code}</div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${p.type === 'entree' ? 'bg-green-100 text-green-800' : p.type === 'sortie' ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800'}">${p.type}</span>
                            <div class="text-xs text-gray-500 mt-1">${p.created_at}</div>
                        </div>
                    </div>
                `).join('');
            });
        }

        // --- Réinitialisation ---
        function resetPresences() {
            if (!confirm('Supprimer toutes les présences du jour pour ce site ?')) return;
            fetch('/pointage/test/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ site_id: siteId })
            })
            .then(r => r.json())
            .then(data => {
                log('Présences réinitialisées');
                loadPresences();
            });
        }

        // --- Init ---
        scannerInput.focus();
        loadPresences();
    </script>
</body>
</html>
