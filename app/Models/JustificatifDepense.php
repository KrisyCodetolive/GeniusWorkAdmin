<?php

namespace App\Models;

use App\Services\DepenseWorkflowService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class JustificatifDepense extends Model
{
    use HasUuids;

    public const TYPES = [
        'devis' => 'Devis',
        'facture' => 'Facture',
        'recu' => 'Reçu',
        'autre' => 'Autre',
    ];

    protected $table = 'justificatifs_depense';

    protected $fillable = [
        'demande_depense_id',
        'fichier',
        'nom_original',
        'type',
    ];

    protected static function booted(): void
    {
        static::deleted(fn (JustificatifDepense $justificatif) => Storage::disk(DepenseWorkflowService::DISQUE)->delete($justificatif->fichier));
    }

    public function demande()
    {
        return $this->belongsTo(DemandeDepense::class, 'demande_depense_id');
    }
}
