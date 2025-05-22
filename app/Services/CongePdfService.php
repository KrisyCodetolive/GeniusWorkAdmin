<?php

namespace App\Services;

use App\Models\Conge;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CongePdfService
{
    /**
     * Génère un PDF pour une demande de congé
     *
     * @param Conge $conge La demande de congé
     * @return string Le chemin du fichier PDF généré
     */
    public function generatePdf(Conge $conge): string
    {
        // Formater les dates pour l'affichage
        $dateDebut = Carbon::parse($conge->date_debut)->format('d/m/Y');
        $dateFin = Carbon::parse($conge->date_fin)->format('d/m/Y');
        $dateValidation = $conge->date_validation ? Carbon::parse($conge->date_validation)->format('d/m/Y H:i') : 'Non validé';
        $dateCreation = Carbon::parse($conge->created_at)->format('d/m/Y H:i');
        
        // Préparer les données pour le PDF
        $data = [
            'conge' => $conge,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'dateValidation' => $dateValidation,
            'dateCreation' => $dateCreation,
            'statutLabel' => $this->getStatutLabel($conge->statut),
            'statutClass' => $this->getStatutClass($conge->statut),
        ];
        
        // Générer le PDF
        $pdf = PDF::loadView('pdf.conge', $data);
        
        // Définir le nom du fichier
        $fileName = 'conge_' . $conge->id . '_' . time() . '.pdf';
        $path = 'pdf/conges/' . $fileName;
        
        // Sauvegarder le PDF dans le stockage
        Storage::disk('public')->put($path, $pdf->output());
        
        return $path;
    }
    
    /**
     * Obtient le libellé du statut
     *
     * @param string $statut Le code du statut
     * @return string Le libellé du statut
     */
    private function getStatutLabel(string $statut): string
    {
        return match ($statut) {
            'en_attente' => 'En attente',
            'approuve' => 'Approuvé',
            'rejete' => 'Rejeté',
            'annule' => 'Annulé',
            default => 'Inconnu',
        };
    }
    
    /**
     * Obtient la classe CSS du statut
     *
     * @param string $statut Le code du statut
     * @return string La classe CSS du statut
     */
    private function getStatutClass(string $statut): string
    {
        return match ($statut) {
            'en_attente' => 'warning',
            'approuve' => 'success',
            'rejete' => 'danger',
            'annule' => 'secondary',
            default => 'info',
        };
    }
}
