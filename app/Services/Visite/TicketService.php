<?php

namespace App\Services\Visite;

use App\Models\Visite;
use App\Models\Visiteur;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

/**
 * Service responsable de la gestion des tickets de visite
 */
class TicketService
{
    /**
     * Génère un ticket de visite au format PDF
     *
     * @param Visite $visite La visite pour laquelle générer le ticket
     * @param bool $download Indique si le PDF doit être téléchargé ou renvoyé comme contenu binaire
     * @return mixed Le contenu PDF ou la réponse de téléchargement
     */
    public function generateTicket(Visite $visite, bool $download = false)
    {
        try {
            // Récupérer les informations nécessaires pour le ticket
            $visiteur = $visite->visiteur;
            $entreprise = $visite->entreprise;
            $site = $visite->site;
            
            // Récupérer le nombre de visites en cours aujourd'hui
            $visitesDuJour = $this->getVisitesEnCoursDuJour($entreprise->id);
            $positionVisite = $this->getPositionVisite($visite, $visitesDuJour);
            
            // Générer le PDF
            $pdf = PDF::loadView('tickets.visite', [
                'visite' => $visite,
                'visiteur' => $visiteur,
                'entreprise' => $entreprise,
                'site' => $site,
                'total_visites' => count($visitesDuJour),
                'position' => $positionVisite,
                'date_generation' => Carbon::now()->format('d/m/Y H:i:s'),
            ]);
            
            $filename = 'ticket_visite_' . $visite->id . '_' . time() . '.pdf';
            
            // Si téléchargement demandé, renvoyer la réponse de téléchargement
            if ($download) {
                return $pdf->download($filename);
            }
            
            // Sinon, renvoyer le contenu PDF
            Log::info('Ticket de visite généré avec succès', [
                'visite_id' => $visite->id
            ]);
            
            return $pdf->output();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du ticket de visite', [
                'visite_id' => $visite->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Imprime automatiquement un ticket de visite
     *
     * @param string $pdfContent Le contenu PDF du ticket
     * @return bool Succès de l'impression
     */
    public function printTicket(string $pdfContent): bool
    {
        try {
            // Créer un fichier temporaire pour l'impression
            $tempFile = tempnam(sys_get_temp_dir(), 'ticket_') . '.pdf';
            file_put_contents($tempFile, $pdfContent);
            
            // Commande d'impression silencieuse adaptée au système d'exploitation
            if (PHP_OS_FAMILY === 'Windows') {
                // Commande Windows pour imprimer sur l'imprimante par défaut
                $command = 'SumatraPDF.exe -print-to-default -silent "' . $tempFile . '"';
                exec($command, $output, $returnCode);
            } else {
                // Commande Linux/Unix pour imprimer sur l'imprimante par défaut
                $command = 'lpr "' . $tempFile . '"';
                exec($command, $output, $returnCode);
            }
            
            // Supprimer le fichier temporaire
            @unlink($tempFile);
            
            $success = $returnCode === 0;
            
            if ($success) {
                Log::info('Ticket de visite imprimé avec succès');
            } else {
                Log::warning('Problème lors de l\'impression du ticket de visite', [
                    'return_code' => $returnCode,
                    'output' => $output
                ]);
            }
            
            return $success;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'impression du ticket de visite', [
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Génère et imprime un ticket de visite en une seule opération
     *
     * @param Visite $visite La visite pour laquelle générer et imprimer le ticket
     * @return bool Succès de l'opération
     */
    public function generateAndPrintTicket(Visite $visite): bool
    {
        try {
            $pdfContent = $this->generateTicket($visite);
            return $this->printTicket($pdfContent);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération et impression du ticket', [
                'visite_id' => $visite->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Récupère toutes les visites en cours pour aujourd'hui
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @return \Illuminate\Database\Eloquent\Collection Les visites en cours du jour
     */
    private function getVisitesEnCoursDuJour(string $entrepriseId): \Illuminate\Database\Eloquent\Collection
    {
        $today = Carbon::today();
        
        return Visite::where('entreprise_id', $entrepriseId)
            ->where('statut', 'en_cours')
            ->whereDate('date_arrivee', $today)
            ->orderBy('date_arrivee')
            ->get();
    }
    
    /**
     * Détermine la position de la visite dans la liste des visites du jour
     *
     * @param Visite $visite La visite pour laquelle déterminer la position
     * @param \Illuminate\Database\Eloquent\Collection $visitesDuJour Les visites du jour
     * @return int La position de la visite
     */
    private function getPositionVisite(Visite $visite, \Illuminate\Database\Eloquent\Collection $visitesDuJour): int
    {
        foreach ($visitesDuJour as $index => $v) {
            if ($v->id === $visite->id) {
                return $index + 1;
            }
        }
        
        return 0;
    }
}
