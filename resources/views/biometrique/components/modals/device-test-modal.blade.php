@props(['appareil' => null])

<div 
    x-data="{ 
        open: false,
        testInProgress: false,
        testSuccess: false,
        testError: false,
        errorMessage: '',
        testResults: null
    }"
    x-on:show-test-modal.window="open = true"
    x-on:hide-test-modal.window="open = false"
    class="relative"
>
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-40"
        x-cloak
    ></div>

    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="fixed inset-0 z-50 overflow-y-auto"
        x-cloak
    >
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div class="absolute right-0 top-0 pr-4 pt-4">
                    <button 
                        type="button" 
                        class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        x-on:click="open = false"
                        x-bind:disabled="testInProgress"
                    >
                        <span class="sr-only">Fermer</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">
                            Test de connexion
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                <span x-show="!testInProgress && !testSuccess && !testError">
                                    Tester la connexion à l'appareil <strong>{{ $appareil ? $appareil->nom : 'sélectionné' }}</strong>.
                                </span>
                                <span x-show="testInProgress">
                                    Test de connexion en cours. Veuillez patienter...
                                </span>
                                <span x-show="testSuccess">
                                    La connexion à l'appareil a été établie avec succès.
                                </span>
                                <span x-show="testError">
                                    Impossible de se connecter à l'appareil: <span x-text="errorMessage"></span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Loading spinner -->
                <div class="mt-6 flex justify-center" x-show="testInProgress">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                
                <!-- Test results -->
                <div class="mt-4" x-show="testSuccess || testError">
                    <div class="bg-gray-50 rounded-md p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Résultats du test</h4>
                        
                        <div x-show="testSuccess">
                            <div class="grid grid-cols-1 gap-2">
                                <div class="flex justify-between py-1 text-sm">
                                    <span class="text-gray-600">Modèle:</span>
                                    <span class="font-medium" x-text="testResults?.device_info?.model || 'N/A'"></span>
                                </div>
                                <div class="flex justify-between py-1 text-sm">
                                    <span class="text-gray-600">Version du firmware:</span>
                                    <span class="font-medium" x-text="testResults?.device_info?.firmware || 'N/A'"></span>
                                </div>
                                <div class="flex justify-between py-1 text-sm">
                                    <span class="text-gray-600">Numéro de série:</span>
                                    <span class="font-medium" x-text="testResults?.device_info?.serial || 'N/A'"></span>
                                </div>
                                <div class="flex justify-between py-1 text-sm">
                                    <span class="text-gray-600">Temps de réponse:</span>
                                    <span class="font-medium" x-text="testResults?.connection?.response_time + ' ms' || 'N/A'"></span>
                                </div>
                                <div class="flex justify-between py-1 text-sm">
                                    <span class="text-gray-600">Utilisateurs enregistrés:</span>
                                    <span class="font-medium" x-text="testResults?.stats?.users || 'N/A'"></span>
                                </div>
                                <div class="flex justify-between py-1 text-sm">
                                    <span class="text-gray-600">Pointages stockés:</span>
                                    <span class="font-medium" x-text="testResults?.stats?.logs || 'N/A'"></span>
                                </div>
                                <div class="flex justify-between py-1 text-sm">
                                    <span class="text-gray-600">Capacité mémoire:</span>
                                    <span class="font-medium" x-text="testResults?.stats?.memory_usage + '%' || 'N/A'"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="testError" class="text-sm text-red-600">
                            <p class="mb-2">Détails de l'erreur:</p>
                            <pre class="bg-gray-100 p-2 rounded text-xs overflow-auto max-h-32" x-text="JSON.stringify(testResults?.error || {}, null, 2)"></pre>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button" 
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto"
                        x-show="!testInProgress"
                        x-on:click="startTest"
                    >
                        <span x-show="!testSuccess && !testError">Tester la connexion</span>
                        <span x-show="testSuccess || testError">Tester à nouveau</span>
                    </button>
                    <button 
                        type="button" 
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                        x-show="!testInProgress"
                        x-on:click="open = false"
                    >
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('testModal', (appareilId) => ({
        open: false,
        testInProgress: false,
        testSuccess: false,
        testError: false,
        errorMessage: '',
        testResults: null,
        appareilId: appareilId,
        
        resetTest() {
            this.testInProgress = false;
            this.testSuccess = false;
            this.testError = false;
            this.errorMessage = '';
            this.testResults = null;
        },
        
        startTest() {
            this.resetTest();
            this.testInProgress = true;
            
            // Start the test process
            fetch(`/biometrique/appareils/${this.appareilId}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.testSuccess = true;
                    this.testResults = data.results;
                } else {
                    this.testError = true;
                    this.errorMessage = data.message || 'Une erreur inconnue est survenue';
                    this.testResults = { error: data.error || {} };
                }
            })
            .catch(error => {
                this.testError = true;
                this.errorMessage = error.message || 'Une erreur inconnue est survenue';
                this.testResults = { error: { message: error.message } };
            })
            .finally(() => {
                this.testInProgress = false;
            });
        }
    }));
});

function openTestModal(appareilId) {
    window.dispatchEvent(new CustomEvent('show-test-modal', {
        detail: { appareilId: appareilId }
    }));
}
</script>
