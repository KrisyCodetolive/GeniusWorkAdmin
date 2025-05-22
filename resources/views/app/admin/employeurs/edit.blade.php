@extends('app.entreprise.dashboard.entreprise')

@section('title', 'Modifier un employeur')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
            <i data-lucide="user-edit" class="h-6 w-6 mr-2 text-blue-600"></i>
            Modifier un employeur
        </h1>
        <div class="flex space-x-2">
            <x-ui.tooltip text="Voir les détails de l'employeur" position="left">
                <a href="{{ route('admin.employeurs.show', $employeur->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 flex items-center">
                    <i data-lucide="eye" class="h-4 w-4 mr-1.5"></i> Voir
                </a>
            </x-ui.tooltip>
            <x-ui.tooltip text="Retour à la liste" position="left">
                <a href="{{ route('admin.employeurs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 flex items-center">
                    <i data-lucide="arrow-left" class="h-4 w-4 mr-1.5"></i> Retour
                </a>
            </x-ui.tooltip>
        </div>
    </div>

    <x-data.card class="border border-gray-100 rounded-xl shadow-sm overflow-hidden">
        <form action="{{ route('admin.employeurs.update', $employeur->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Informations personnelles -->
                <div class="bg-gray-50 p-5 rounded-xl">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="user" class="h-5 w-5 mr-2 text-blue-600"></i>
                        Informations personnelles
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.form-input
                            label="Nom"
                            name="nom"
                            :value="old('nom', $employeur->nom)"
                            required="true"
                            :error="$errors->first('nom')"
                        />
                        
                        <x-form.form-input
                            label="Prénom"
                            name="prenom"
                            :value="old('prenom', $employeur->prenom)"
                            required="true"
                            :error="$errors->first('prenom')"
                        />
                        
                        <x-form.form-input
                            label="Email"
                            name="email"
                            type="email"
                            :value="old('email', $employeur->email)"
                            required="true"
                            :error="$errors->first('email')"
                        />
                        
                        <x-form.form-input
                            label="Téléphone"
                            name="telephone"
                            type="tel"
                            :value="old('telephone', $employeur->telephone)"
                            required="true"
                            :error="$errors->first('telephone')"
                        />
                        
                        <x-form.date-picker
                            label="Date de naissance"
                            name="date_naissance"
                            :value="old('date_naissance', $employeur->date_naissance ? $employeur->date_naissance->format('Y-m-d') : '')"
                            :error="$errors->first('date_naissance')"
                        />
                        
                        <div class="space-y-1">
                            <label for="photo" class="block text-sm font-medium text-gray-700">
                                Photo
                            </label>
                            <input type="file" id="photo" name="photo" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200">
                            @if($employeur->photo)
                                <div class="mt-2 flex items-center">
                                    <img src="{{ Storage::url($employeur->photo) }}" alt="{{ $employeur->getNomComplet() }}" class="h-10 w-10 rounded-full object-cover mr-2">
                                    <div class="text-xs text-gray-500">Photo actuelle</div>
                                </div>
                            @endif
                            @error('photo')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Informations professionnelles -->
                <div class="bg-gray-50 p-5 rounded-xl">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i data-lucide="briefcase" class="h-5 w-5 mr-2 text-blue-600"></i>
                        Informations professionnelles
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.form-select
                            label="Entreprise"
                            name="entreprise_id"
                            required="true"
                            :error="$errors->first('entreprise_id')"
                        >
                            <option value="">Sélectionner une entreprise</option>
                            @foreach($entreprises as $entreprise)
                                <option value="{{ $entreprise->id }}" {{ old('entreprise_id', $employeur->entreprise_id) == $entreprise->id ? 'selected' : '' }}>
                                    {{ $entreprise->nom }}
                                </option>
                            @endforeach
                        </x-form.form-select>
                        
                        <x-form.form-select
                            label="Département"
                            name="departement_id"
                            required="true"
                            :error="$errors->first('departement_id')"
                        >
                            <option value="">Sélectionner un département</option>
                            @foreach($departements as $departement)
                                <option value="{{ $departement->id }}" {{ old('departement_id', $employeur->departement_id) == $departement->id ? 'selected' : '' }}>
                                    {{ $departement->nom }}
                                </option>
                            @endforeach
                        </x-form.form-select>
                        
                        <x-form.form-input
                            label="Poste"
                            name="poste"
                            :value="old('poste', $employeur->poste)"
                            required="true"
                            :error="$errors->first('poste')"
                        />
                        
                        <x-form.form-select
                            label="Type de contrat"
                            name="type_contrat"
                            required="true"
                            :error="$errors->first('type_contrat')"
                        >
                            <option value="">Sélectionner un type</option>
                            @foreach($typesContrat as $key => $label)
                                <option value="{{ $key }}" {{ old('type_contrat', $employeur->type_contrat) == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </x-form.form-select>
                        
                        <x-form.form-select
                            label="Statut"
                            name="statut"
                            required="true"
                            :error="$errors->first('statut')"
                        >
                            <option value="actif" {{ old('statut', $employeur->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('statut', $employeur->statut) == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </x-form.form-select>
                    </div>
                </div>
            </div>
            
            <!-- QR Code -->
            <div class="bg-gray-50 p-5 rounded-xl mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="qr-code" class="h-5 w-5 mr-2 text-blue-600"></i>
                    QR Code
                </h2>
                
                <div class="flex items-center space-x-4">
                    @if($employeur->qrcode_data)
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            {!! $employeur->getQRCodeSVG() !!}
                        </div>
                        <div>
                            <div class="flex items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 mr-2">Statut:</span> 
                                @if($employeur->qrcode_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i data-lucide="check-circle" class="h-3 w-3 mr-1"></i> Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i data-lucide="x-circle" class="h-3 w-3 mr-1"></i> Inactif
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mb-3">
                                <span class="font-medium">Dernière génération:</span> 
                                {{ $employeur->qrcode_generated_at ? $employeur->qrcode_generated_at->format('d/m/Y H:i') : 'Jamais' }}
                            </p>
                            <div class="flex space-x-2">
                                <x-ui.tooltip text="Générer un nouveau QR code" position="top">
                                    <form action="{{ route('admin.employeurs.regenerate-qrcode', $employeur->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 px-3 rounded-lg shadow-sm transition-all duration-200 flex items-center">
                                            <i data-lucide="refresh-cw" class="h-4 w-4 mr-1.5"></i> Régénérer
                                        </button>
                                    </form>
                                </x-ui.tooltip>
                                
                                <x-ui.tooltip text="Désactiver le QR code" position="top">
                                    <form action="{{ route('admin.employeurs.deactivate-qrcode', $employeur->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm py-2 px-3 rounded-lg shadow-sm transition-all duration-200 flex items-center">
                                            <i data-lucide="x" class="h-4 w-4 mr-1.5"></i> Désactiver
                                        </button>
                                    </form>
                                </x-ui.tooltip>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center text-gray-600">
                            <i data-lucide="alert-circle" class="h-5 w-5 mr-2 text-gray-400"></i>
                            Aucun QR code n'a été généré pour cet employeur.
                        </div>
                    @endif
                </div>
            </div>
            
            <x-form.form-actions>
                <x-ui.tooltip text="Annuler les modifications" position="top">
                    <a href="{{ route('admin.employeurs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 flex items-center">
                        <i data-lucide="x" class="h-4 w-4 mr-1.5"></i> Annuler
                    </a>
                </x-ui.tooltip>
                
                <x-ui.tooltip text="Enregistrer les modifications" position="top">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 flex items-center">
                        <i data-lucide="save" class="h-4 w-4 mr-1.5"></i> Enregistrer
                    </button>
                </x-ui.tooltip>
            </x-form.form-actions>
        </form>
    </x-data.card>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les icônes Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Filtrage dynamique des départements en fonction de l'entreprise sélectionnée
        const entrepriseSelect = document.getElementById('entreprise_id');
        const departementSelect = document.getElementById('departement_id');
        
        if (entrepriseSelect && departementSelect) {
            entrepriseSelect.addEventListener('change', function() {
                const entrepriseId = this.value;
                
                // Réinitialiser le select des départements
                departementSelect.innerHTML = '<option value="">Sélectionner un département</option>';
                
                if (entrepriseId) {
                    // Charger les départements de l'entreprise sélectionnée via AJAX
                    fetch(`/api/entreprises/${entrepriseId}/departements`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(departement => {
                                const option = document.createElement('option');
                                option.value = departement.id;
                                option.textContent = departement.nom;
                                departementSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Erreur lors du chargement des départements:', error));
                }
            });
        }
    });
</script>
@endpush
@endsection
