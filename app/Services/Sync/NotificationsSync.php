<?php

namespace App\Services\Sync;

use App\Models\Conge;
use App\Models\ValidationDepense;
use App\Notifications\CongeStatusNotification;
use App\Services\CongePdfService;
use App\Services\DepenseWorkflowService;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Envoie, pour une ligne reçue de l'autre instance, les notifications que l'action aurait envoyées sur place.
 * Les soldes, PDF de validation, etc. ne sont pas recalculés : ils arrivent par leurs propres lignes.
 */
class NotificationsSync
{
    public function __construct(protected DepenseWorkflowService $depenses)
    {
    }

    public function apresReception(string $table, ?array $ancienne, array $nouvelle): void
    {
        try {
            match ($table) {
                'conges' => $this->conge($ancienne, $nouvelle),
                // Chaque étape du circuit d'une dépense crée une validation : une nouvelle validation = une notification
                'validations_depense' => $ancienne === null
                    ? $this->depenses->notifierValidation(ValidationDepense::findOrFail($nouvelle['id']))
                    : null,
                default => null,
            };
        } catch (Throwable $e) {
            // Une notification qui échoue ne doit jamais faire refuser le changement reçu
            Log::warning('Sync : notification non envoyée', ['table' => $table, 'id' => $nouvelle['id'] ?? null, 'erreur' => $e->getMessage()]);
        }
    }

    /**
     * Mêmes notifications que CongeService : l'employé est prévenu quand sa demande est approuvée, rejetée ou annulée.
     */
    protected function conge(?array $ancienne, array $nouvelle): void
    {
        $statut = $nouvelle['statut'] ?? null;

        if (!in_array($statut, ['approuve', 'rejete', 'annule'], true) || ($ancienne['statut'] ?? null) === $statut) {
            return;
        }

        $conge = Conge::findOrFail($nouvelle['id']);
        $user = $conge->employeur?->user;

        if (!$user) {
            return;
        }

        $pdfPath = null;
        if ($statut === 'approuve') {
            try {
                $pdfPath = app(CongePdfService::class)->generatePdf($conge);
            } catch (Throwable $e) {
                Log::error('Sync : attestation de congé non générée', ['conge' => $conge->id, 'erreur' => $e->getMessage()]);
            }
        }

        $user->notify(new CongeStatusNotification($conge, $pdfPath));
    }
}
