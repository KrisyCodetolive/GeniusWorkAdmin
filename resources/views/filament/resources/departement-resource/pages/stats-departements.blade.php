<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 mb-6">
        @if ($this->getWidgets())
            <x-filament-widgets::widgets
                :columns="$this->getColumns()"
                :widgets="$this->getWidgets()"
            />
        @endif
    </div>
</x-filament-panels::page>
