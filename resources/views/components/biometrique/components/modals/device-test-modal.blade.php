<div id="deviceTestModal" 
    x-data="{ 
        open: false,
        appareilId: @if($appareil) {{ $appareil->id }} @else null @endif,
        appareilName: @if($appareil) '{{ $appareil->nom }}' @else '' @endif,
        testing: false,
        message: '',
        status: '',
        
        init() {
            this.$watch('open', value => {
                if (!value) {
                    this.resetModal();
                }
            });
        },
        
        resetModal() {
            this.testing = false;
            this.message = '';
            this.status = '';
        },
        
        async testDevice() {
            if (!this.appareilId) {
                this.message = 'Aucun appareil sélectionné pour le test.';
                this.status = 'error';
                return;
            }
            
            this.testing = true;
            this.message = 'Test de connexion en cours...';
            this.status = 'info';
            
            try {
                const response = await fetch(`/biometrique/appareils/${this.appareilId}/test`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.message = data.message || 'Test de connexion réussi.';
                    this.status = 'success';
                } else {
                    this.message = data.message || 'Erreur lors du test de connexion.';
                    this.status = 'error';
                }
            } catch (error) {
                this.message = 'Erreur de connexion. Veuillez réessayer.';
                this.status = 'error';
                console.error('Test error:', error);
            } finally {
                this.testing = false;
            }
        }
    }"
    x-on:open-test-modal.window="open = true; appareilId = $event.detail.appareilId; appareilName = $event.detail.appareilName"
    class="relative z-50">

    <!-- Modal backdrop -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <!-- Modal panel -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Test de connexion pour l'appareil
                            <span x-text="appareilName" class="font-semibold"></span>
                        </h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">
                                Cette action va tester la connexion avec l'appareil biométrique.
                                Assurez-vous que l'appareil est allumé et connecté au réseau.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Message de statut -->
                    <div x-show="message" class="mt-4">
                        <div :class="{
                            'bg-green-50 text-green-800': status === 'success',
                            'bg-blue-50 text-blue-800': status === 'info',
                            'bg-red-50 text-red-800': status === 'error'
                        }" class="rounded-md p-4">
                            <p x-text="message" class="text-sm"></p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                    <button type="button" 
                            x-on:click="testDevice()"
                            x-bind:disabled="testing"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:col-start-2 sm:text-sm"
                            :class="{ 'opacity-75 cursor-not-allowed': testing }">
                        <span x-show="!testing">Tester la connexion</span>
                        <span x-show="testing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Test en cours...
                        </span>
                    </button>
                    <button type="button" 
                            x-on:click="open = false"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
