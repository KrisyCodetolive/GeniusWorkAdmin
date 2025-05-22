@props(['appareil' => null])

<div 
    x-data="{ 
        open: false,
        syncInProgress: false,
        syncSuccess: false,
        syncError: false,
        errorMessage: '',
        progressValue: 0,
        progressMax: 100,
        progressText: '',
        logs: []
    }"
    x-on:show-sync-modal.window="open = true"
    x-on:hide-sync-modal.window="open = false"
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
                        x-bind:disabled="syncInProgress"
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">
                            Synchronisation de l'appareil
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                <span x-show="!syncInProgress && !syncSuccess && !syncError">
                                    Vous êtes sur le point de synchroniser l'appareil <strong>{{ $appareil ? $appareil->nom : 'sélectionné' }}</strong>. 
                                    Cette opération peut prendre plusieurs minutes. Voulez-vous continuer?
                                </span>
                                <span x-show="syncInProgress">
                                    Synchronisation en cours. Veuillez patienter...
                                </span>
                                <span x-show="syncSuccess">
                                    La synchronisation a été effectuée avec succès.
                                </span>
                                <span x-show="syncError">
                                    Une erreur est survenue lors de la synchronisation: <span x-text="errorMessage"></span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Progress bar -->
                <div class="mt-4" x-show="syncInProgress">
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200" x-text="progressText">
                                    Initialisation...
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-blue-600" x-text="Math.round((progressValue / progressMax) * 100) + '%'">
                                    0%
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                            <div 
                                x-bind:style="'width: ' + (progressValue / progressMax * 100) + '%'"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-500 ease-in-out">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Logs -->
                <div class="mt-4 max-h-40 overflow-y-auto bg-gray-50 rounded p-2" x-show="logs.length > 0">
                    <div class="text-xs font-mono">
                        <template x-for="(log, index) in logs" :key="index">
                            <div class="py-1" x-bind:class="{'text-green-600': log.type === 'success', 'text-red-600': log.type === 'error', 'text-blue-600': log.type === 'info'}">
                                <span x-text="log.timestamp"></span>: <span x-text="log.message"></span>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button" 
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto"
                        x-show="!syncInProgress && !syncSuccess && !syncError"
                        x-on:click="startSync"
                    >
                        Synchroniser
                    </button>
                    <button 
                        type="button" 
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                        x-show="!syncInProgress"
                        x-on:click="open = false"
                    >
                        Fermer
                    </button>
                    <button 
                        type="button" 
                        class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                        x-show="syncInProgress"
                        x-on:click="cancelSync"
                    >
                        Annuler
                    </button>
                    <button 
                        type="button" 
                        class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto"
                        x-show="syncSuccess || syncError"
                        x-on:click="resetSync"
                    >
                        Nouvelle synchronisation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('syncModal', (appareilId) => ({
        open: false,
        syncInProgress: false,
        syncSuccess: false,
        syncError: false,
        errorMessage: '',
        progressValue: 0,
        progressMax: 100,
        progressText: '',
        logs: [],
        appareilId: appareilId,
        syncController: null,
        
        resetSync() {
            this.syncInProgress = false;
            this.syncSuccess = false;
            this.syncError = false;
            this.errorMessage = '';
            this.progressValue = 0;
            this.progressMax = 100;
            this.progressText = '';
            this.logs = [];
        },
        
        startSync() {
            this.resetSync();
            this.syncInProgress = true;
            this.progressText = 'Initialisation...';
            this.addLog('info', 'Démarrage de la synchronisation');
            
            // Create an AbortController for cancellation
            this.syncController = new AbortController();
            const signal = this.syncController.signal;
            
            // Start the sync process
            fetch(`/biometrique/appareils/${this.appareilId}/sync`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                signal: signal
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.syncSuccess = true;
                    this.addLog('success', 'Synchronisation terminée avec succès');
                    this.progressValue = this.progressMax;
                    this.progressText = 'Terminé';
                    
                    // Trigger event to refresh the page data
                    const event = new CustomEvent('deviceSyncCompleted', {
                        detail: { appareilId: this.appareilId }
                    });
                    window.dispatchEvent(event);
                } else {
                    this.syncError = true;
                    this.errorMessage = data.message || 'Une erreur inconnue est survenue';
                    this.addLog('error', this.errorMessage);
                }
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    this.addLog('info', 'Synchronisation annulée par l\'utilisateur');
                } else {
                    this.syncError = true;
                    this.errorMessage = error.message || 'Une erreur inconnue est survenue';
                    this.addLog('error', this.errorMessage);
                }
            })
            .finally(() => {
                this.syncInProgress = false;
                this.syncController = null;
            });
            
            // Simulate progress updates (in a real app, you would get these from the server)
            this.simulateProgress();
        },
        
        cancelSync() {
            if (this.syncController) {
                this.syncController.abort();
                this.syncInProgress = false;
                this.addLog('info', 'Synchronisation annulée');
            }
        },
        
        addLog(type, message) {
            const now = new Date();
            const timestamp = now.toLocaleTimeString();
            this.logs.push({
                type: type,
                message: message,
                timestamp: timestamp
            });
        },
        
        simulateProgress() {
            // This is just a simulation - in a real app, you would get progress updates from the server
            const totalSteps = 5;
            const stepDuration = 2000; // 2 seconds per step
            
            const steps = [
                { text: 'Connexion à l\'appareil...', value: 10 },
                { text: 'Récupération des utilisateurs...', value: 30 },
                { text: 'Récupération des pointages...', value: 60 },
                { text: 'Traitement des données...', value: 80 },
                { text: 'Finalisation...', value: 95 }
            ];
            
            steps.forEach((step, index) => {
                setTimeout(() => {
                    if (this.syncInProgress) {
                        this.progressValue = step.value;
                        this.progressText = step.text;
                        this.addLog('info', step.text);
                    }
                }, index * stepDuration);
            });
        }
    }));
});

function openSyncModal(appareilId) {
    window.dispatchEvent(new CustomEvent('show-sync-modal', {
        detail: { appareilId: appareilId }
    }));
}
</script>
