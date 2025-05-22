<?php

namespace App\Filament\Resources\FacturationResource\Pages;

use App\Filament\Resources\FacturationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFacturations extends ListRecords
{
    protected static string $resource = FacturationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        
        // Trier par date d'échéance (les plus proches d'abord pour les impayées)
        return $query->orderByRaw("CASE WHEN statut_paiement = 'impaye' THEN 0 ELSE 1 END, date_echeance ASC");
    }
}
