@props(['appareil' => null, 'sites' => [], 'action', 'method' => 'POST'])

<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif
    
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <!-- Informations générales -->
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informations générales</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom de l'appareil <span class="text-red-600">*</span></label>
                    <input type="text" name="nom" id="nom" value="{{ old('nom', $appareil->nom ?? '') }}" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    @error('nom')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="site_id" class="block text-sm font-medium text-gray-700 mb-1">Site <span class="text-red-600">*</span></label>
                    <select name="site_id" id="site_id" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="">Sélectionner un site</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ old('site_id', $appareil->site_id ?? '') == $site->id ? 'selected' : '' }}>{{ $site->nom }}</option>
                        @endforeach
                    </select>
                    @error('site_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="fabricant" class="block text-sm font-medium text-gray-700 mb-1">Fabricant <span class="text-red-600">*</span></label>
                    <input type="text" name="fabricant" id="fabricant" value="{{ old('fabricant', $appareil->fabricant ?? '') }}" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    @error('fabricant')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="modele" class="block text-sm font-medium text-gray-700 mb-1">Modèle <span class="text-red-600">*</span></label>
                    <input type="text" name="modele" id="modele" value="{{ old('modele', $appareil->modele ?? '') }}" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    @error('modele')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="numero_serie" class="block text-sm font-medium text-gray-700 mb-1">Numéro de série</label>
                    <input type="text" name="numero_serie" id="numero_serie" value="{{ old('numero_serie', $appareil->numero_serie ?? '') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    @error('numero_serie')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="statut" class="block text-sm font-medium text-gray-700 mb-1">Statut <span class="text-red-600">*</span></label>
                    <select name="statut" id="statut" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="actif" {{ old('statut', $appareil->statut ?? '') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ old('statut', $appareil->statut ?? '') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="maintenance" {{ old('statut', $appareil->statut ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="erreur" {{ old('statut', $appareil->statut ?? '') == 'erreur' ? 'selected' : '' }}>Erreur</option>
                    </select>
                    @error('statut')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Configuration réseau -->
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration réseau</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="adresse_ip" class="block text-sm font-medium text-gray-700 mb-1">Adresse IP <span class="text-red-600">*</span></label>
                    <input type="text" name="adresse_ip" id="adresse_ip" value="{{ old('adresse_ip', $appareil->adresse_ip ?? '') }}" required pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    @error('adresse_ip')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="port" class="block text-sm font-medium text-gray-700 mb-1">Port <span class="text-red-600">*</span></label>
                    <input type="number" name="port" id="port" value="{{ old('port', $appareil->port ?? '4370') }}" required min="1" max="65535" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    @error('port')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="protocole" class="block text-sm font-medium text-gray-700 mb-1">Protocole <span class="text-red-600">*</span></label>
                    <select name="protocole" id="protocole" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="hikvision" {{ old('protocole', $appareil->protocole ?? '') == 'hikvision' ? 'selected' : '' }}>HikVision</option>
                        <option value="anviz" {{ old('protocole', $appareil->protocole ?? '') == 'anviz' ? 'selected' : '' }}>Anviz</option>
                        <option value="zkteco" {{ old('protocole', $appareil->protocole ?? '') == 'zkteco' ? 'selected' : '' }}>ZKTeco</option>
                        <option value="http" {{ old('protocole', $appareil->protocole ?? '') == 'http' ? 'selected' : '' }}>HTTP Générique</option>
                    </select>
                    @error('protocole')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="timeout" class="block text-sm font-medium text-gray-700 mb-1">Timeout (secondes)</label>
                    <input type="number" name="timeout" id="timeout" value="{{ old('timeout', $appareil->timeout ?? '30') }}" min="1" max="300" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    @error('timeout')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Authentification -->
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Authentification</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $appareil->username ?? '') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input type="password" name="password" id="password" value="{{ old('password', '') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    <p class="mt-1 text-xs text-gray-500">{{ $appareil ? 'Laissez vide pour conserver le mot de passe actuel' : '' }}</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="api_key" class="block text-sm font-medium text-gray-700 mb-1">Clé API</label>
                    <input type="text" name="api_key" id="api_key" value="{{ old('api_key', $appareil->api_key ?? '') }}" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    @error('api_key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="auth_method" class="block text-sm font-medium text-gray-700 mb-1">Méthode d'authentification</label>
                    <select name="auth_method" id="auth_method" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="basic" {{ old('auth_method', $appareil->auth_method ?? '') == 'basic' ? 'selected' : '' }}>Basic</option>
                        <option value="digest" {{ old('auth_method', $appareil->auth_method ?? '') == 'digest' ? 'selected' : '' }}>Digest</option>
                        <option value="token" {{ old('auth_method', $appareil->auth_method ?? '') == 'token' ? 'selected' : '' }}>Token</option>
                        <option value="none" {{ old('auth_method', $appareil->auth_method ?? '') == 'none' ? 'selected' : '' }}>Aucune</option>
                    </select>
                    @error('auth_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Configuration de synchronisation -->
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration de synchronisation</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="sync_frequency" class="block text-sm font-medium text-gray-700 mb-1">Fréquence de synchronisation</label>
                    <select name="sync_frequency" id="sync_frequency" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="5" {{ old('sync_frequency', $appareil->sync_frequency ?? '') == '5' ? 'selected' : '' }}>Toutes les 5 minutes</option>
                        <option value="15" {{ old('sync_frequency', $appareil->sync_frequency ?? '') == '15' ? 'selected' : '' }}>Toutes les 15 minutes</option>
                        <option value="30" {{ old('sync_frequency', $appareil->sync_frequency ?? '30') == '30' ? 'selected' : '' }}>Toutes les 30 minutes</option>
                        <option value="60" {{ old('sync_frequency', $appareil->sync_frequency ?? '') == '60' ? 'selected' : '' }}>Toutes les heures</option>
                        <option value="360" {{ old('sync_frequency', $appareil->sync_frequency ?? '') == '360' ? 'selected' : '' }}>Toutes les 6 heures</option>
                        <option value="720" {{ old('sync_frequency', $appareil->sync_frequency ?? '') == '720' ? 'selected' : '' }}>Toutes les 12 heures</option>
                        <option value="1440" {{ old('sync_frequency', $appareil->sync_frequency ?? '') == '1440' ? 'selected' : '' }}>Tous les jours</option>
                        <option value="0" {{ old('sync_frequency', $appareil->sync_frequency ?? '') == '0' ? 'selected' : '' }}>Manuel uniquement</option>
                    </select>
                    @error('sync_frequency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="sync_mode" class="block text-sm font-medium text-gray-700 mb-1">Mode de synchronisation</label>
                    <select name="sync_mode" id="sync_mode" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="auto" {{ old('sync_mode', $appareil->sync_mode ?? 'auto') == 'auto' ? 'selected' : '' }}>Automatique</option>
                        <option value="pull" {{ old('sync_mode', $appareil->sync_mode ?? '') == 'pull' ? 'selected' : '' }}>Pull uniquement</option>
                        <option value="push" {{ old('sync_mode', $appareil->sync_mode ?? '') == 'push' ? 'selected' : '' }}>Push uniquement</option>
                    </select>
                    @error('sync_mode')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="sync_data" class="block text-sm font-medium text-gray-700 mb-1">Données à synchroniser</label>
                    <select name="sync_data" id="sync_data" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="all" {{ old('sync_data', $appareil->sync_data ?? 'all') == 'all' ? 'selected' : '' }}>Tout</option>
                        <option value="users" {{ old('sync_data', $appareil->sync_data ?? '') == 'users' ? 'selected' : '' }}>Utilisateurs uniquement</option>
                        <option value="logs" {{ old('sync_data', $appareil->sync_data ?? '') == 'logs' ? 'selected' : '' }}>Logs uniquement</option>
                        <option value="pointages" {{ old('sync_data', $appareil->sync_data ?? '') == 'pointages' ? 'selected' : '' }}>Pointages uniquement</option>
                    </select>
                    @error('sync_data')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Notes et commentaires -->
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Notes et commentaires</h3>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('notes', $appareil->notes ?? '') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="flex justify-end space-x-3">
        <a href="{{ route('biometrique.appareils.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Annuler
        </a>
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            {{ $appareil ? 'Mettre à jour' : 'Créer' }}
        </button>
    </div>
</form>
