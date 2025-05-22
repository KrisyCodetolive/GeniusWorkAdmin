@props(['label', 'name', 'id' => null, 'value' => null, 'required' => false, 'error' => null, 'placeholder' => 'Sélectionner un employé', 'multiple' => false, 'departmentId' => null])

<div x-data="{
    open: false,
    search: '',
    selected: @js($value ? (is_array($value) ? $value : [$value]) : []),
    employees: [],
    loading: false,
    init() {
        this.fetchEmployees();
        
        // Watch for department changes if using a department filter
        if (this.$refs.departmentFilter) {
            this.$watch('$refs.departmentFilter.value', (value) => {
                this.fetchEmployees(value);
            });
        }
    },
    fetchEmployees(departmentId = null) {
        this.loading = true;
        
        // Use the provided departmentId or the one from props
        const deptId = departmentId || @js($departmentId);
        
        fetch(`/api/employees${deptId ? `?department_id=${deptId}` : ''}`)
            .then(response => response.json())
            .then(data => {
                this.employees = data;
                this.loading = false;
            })
            .catch(error => {
                console.error('Error fetching employees:', error);
                this.loading = false;
            });
    },
    toggleSelection(employee) {
        if (this.isSelected(employee.id)) {
            this.selected = @js($multiple) 
                ? this.selected.filter(id => id !== employee.id)
                : [];
        } else {
            this.selected = @js($multiple)
                ? [...this.selected, employee.id]
                : [employee.id];
        }
        
        // Update hidden input value
        this.$refs.input.value = @js($multiple)
            ? JSON.stringify(this.selected)
            : this.selected[0] || '';
            
        // Close dropdown if not multiple
        if (!@js($multiple)) {
            this.open = false;
        }
    },
    isSelected(id) {
        return this.selected.includes(id);
    },
    getSelectedEmployees() {
        return this.employees.filter(employee => this.isSelected(employee.id));
    },
    getSelectedNames() {
        const selected = this.getSelectedEmployees();
        return selected.length 
            ? selected.map(e => `${e.prenom} ${e.nom}`).join(', ')
            : @js($placeholder);
    }
}" class="space-y-1">
    <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    <div class="relative">
        <button 
            type="button"
            x-on:click="open = !open"
            class="w-full bg-white relative border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm {{ $error ? 'border-red-500' : '' }}"
        >
            <span class="block truncate" x-text="getSelectedNames()"></span>
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <i data-lucide="chevron-down" class="h-5 w-5 text-gray-400"></i>
            </span>
        </button>
        
        <div 
            x-show="open"
            x-on:click.away="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
            style="display: none;"
        >
            <div class="sticky top-0 z-10 bg-white p-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        x-model="search" 
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Rechercher un employé..."
                    />
                </div>
            </div>
            
            <div x-show="loading" class="p-4 text-center text-sm text-gray-500">
                Chargement...
            </div>
            
            <template x-for="employee in employees.filter(e => (e.nom + ' ' + e.prenom).toLowerCase().includes(search.toLowerCase()))" :key="employee.id">
                <div 
                    x-on:click="toggleSelection(employee)"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                    :class="{ 'bg-blue-50': isSelected(employee.id) }"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8">
                            <template x-if="employee.photo_path">
                                <img :src="`/storage/${employee.photo_path}`" class="h-8 w-8 rounded-full" />
                            </template>
                            <template x-if="!employee.photo_path">
                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-500" x-text="employee.prenom.charAt(0) + employee.nom.charAt(0)"></span>
                                </div>
                            </template>
                        </div>
                        
                        <div class="ml-3 flex-1">
                            <div class="text-sm font-medium text-gray-900" x-text="`${employee.prenom} ${employee.nom}`"></div>
                            <div class="text-xs text-gray-500" x-text="employee.poste || employee.departement?.nom || ''"></div>
                        </div>
                        
                        <div x-show="isSelected(employee.id)" class="absolute inset-y-0 right-0 flex items-center pr-4">
                            <i data-lucide="check" class="h-5 w-5 text-blue-600"></i>
                        </div>
                    </div>
                </div>
            </template>
            
            <div x-show="employees.length === 0 && !loading" class="p-4 text-center text-sm text-gray-500">
                Aucun employé trouvé
            </div>
            
            <div x-show="employees.length > 0 && employees.filter(e => (e.nom + ' ' + e.prenom).toLowerCase().includes(search.toLowerCase())).length === 0" class="p-4 text-center text-sm text-gray-500">
                Aucun résultat pour "<span x-text="search"></span>"
            </div>
        </div>
    </div>
    
    <input 
        type="hidden" 
        x-ref="input"
        id="{{ $id ?? $name }}" 
        name="{{ $name }}" 
        :value="{{ $multiple ? 'JSON.stringify(selected)' : 'selected[0] || ""' }}"
        @if($required) required @endif
    />
    
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
