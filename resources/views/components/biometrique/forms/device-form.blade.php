<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nom de l'appareil -->
        <div>
            <label for="nom" class="block text-sm font-medium text-gray-700">Nom de l'appareil</label>
            <input type="text" name="nom" id="nom" value="{{ old('nom', $appareil->nom ?? '') }}" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                required>
            @error('nom')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Modèle -->
        <div>
            <label for="modele" class="block text-sm font-medium text-gray-700">Modèle</label>
            <input type="text" name="modele" id="modele" value="{{ old('modele', $appareil->modele ?? '') }}" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                required>
            @error('modele')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Numéro de série -->
        <div>
            <label for="numero_serie" class="block text-sm font-medium text-gray-700">Numéro de série</label>
            <input type="text" name="numero_serie" id="numero_serie" value="{{ old('numero_serie', $appareil->numero_serie ?? '') }}" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                required>
            @error('numero_serie')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Adresse IP -->
        <div>
            <label for="adresse_ip" class="block text-sm font-medium text-gray-700">Adresse IP</label>
            <input type="text" name="adresse_ip" id="adresse_ip" value="{{ old('adresse_ip', $appareil->adresse_ip ?? '') }}" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                required>
            @error('adresse_ip')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Port -->
        <div>
            <label for="port" class="block text-sm font-medium text-gray-700">Port</label>
            <input type="number" name="port" id="port" value="{{ old('port', $appareil->port ?? '4370') }}" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                required>
            @error('port')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Site -->
        <div>
            <label for="site_id" class="block text-sm font-medium text-gray-700">Site</label>
            <select name="site_id" id="site_id" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                required>
                <option value="">Sélectionner un site</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ old('site_id', $appareil->site_id ?? '') == $site->id ? 'selected' : '' }}>
                        {{ $site->nom }}
                    </option>
                @endforeach
            </select>
            @error('site_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Identifiant de connexion -->
        <div>
            <label for="identifiant" class="block text-sm font-medium text-gray-700">Identifiant de connexion</label>
            <input type="text" name="identifiant" id="identifiant" value="{{ old('identifiant', $appareil->identifiant ?? '') }}" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            @error('identifiant')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Mot de passe -->
        <div>
            <label for="mot_de_passe" class="block text-sm font-medium text-gray-700">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" value="{{ old('mot_de_passe', $appareil->mot_de_passe ?? '') }}" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            @error('mot_de_passe')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Statut -->
    <div class="mt-4">
        <div class="flex items-center">
            <input type="checkbox" name="actif" id="actif" value="1" 
                {{ old('actif', $appareil->actif ?? true) ? 'checked' : '' }}
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="actif" class="ml-2 block text-sm text-gray-700">Appareil actif</label>
        </div>
        @error('actif')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Notes -->
    <div class="mt-4">
        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" id="notes" rows="3" 
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('notes', $appareil->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Boutons d'action -->
    <div class="flex justify-end space-x-3 mt-6">
        <a href="{{ route('biometrique.appareils.index') }}" 
            class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Annuler
        </a>
        <button type="submit" 
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            {{ $appareil ? 'Mettre à jour' : 'Enregistrer' }}
        </button>
    </div>
</form>
