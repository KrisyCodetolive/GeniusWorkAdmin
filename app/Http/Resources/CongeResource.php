<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CongeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type_conge' => [
                'id' => $this->typeConge->id,
                'nom' => $this->typeConge->nom,
                'est_paye' => $this->typeConge->est_paye,
            ],
            'date_debut' => $this->date_debut->format('Y-m-d'),
            'date_fin' => $this->date_fin->format('Y-m-d'),
            'duree_jours' => $this->duree_jours,
            'motif' => $this->motif,
            'justificatif' => $this->justificatif ? url('storage/' . $this->justificatif) : null,
            'statut' => $this->statut,
            'est_paye' => $this->est_paye,
            'validateur' => $this->whenLoaded('validateur', function() {
                return [
                    'id' => $this->validateur->id,
                    'nom' => $this->validateur->name,
                ];
            }),
            'date_validation' => $this->date_validation?->format('Y-m-d H:i:s'),
            'commentaire_validation' => $this->commentaire_validation,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
