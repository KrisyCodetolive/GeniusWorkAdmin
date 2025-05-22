@props(['name', 'id' => null, 'label' => 'Département', 'required' => false, 'selected' => null, 'companyId' => null, 'placeholder' => 'Sélectionnez un département', 'error' => null])

<div 
    x-data="{
        departments: [],
        selectedDepartment: @js($selected),
        companyId: @js($companyId),
        loading: false,
        init() {
            if (this.companyId) {
                this.fetchDepartments();
            }
            
            $watch('companyId', value => {
                if (value) {
                    this.fetchDepartments();
                } else {
                    this.departments = [];
                    this.selectedDepartment = null;
                }
            });
        },
        fetchDepartments() {
            this.loading = true;
            fetch(`/api/companies/${this.companyId}/departments`)
                .then(response => response.json())
                .then(data => {
                    this.departments = data;
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Error fetching departments:', error);
                    this.loading = false;
                });
        }
    }"
    class="space-y-1"
>
    <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    <div class="relative">
        <select 
            id="{{ $id ?? $name }}" 
            name="{{ $name }}" 
            x-bind:disabled="loading || !companyId"
            {{ $attributes->merge(['class' => 'w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200' . ($error ? ' border-red-500' : '')]) }}
        >
            <option value="">{{ $placeholder }}</option>
            <template x-for="department in departments" :key="department.id">
                <option 
                    x-bind:value="department.id" 
                    x-bind:selected="selectedDepartment == department.id"
                    x-text="department.nom"
                ></option>
            </template>
        </select>
        
        <div x-show="loading" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
    
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
    
    <p x-show="!companyId" class="text-sm text-gray-500">Veuillez d'abord sélectionner une entreprise</p>
</div>
