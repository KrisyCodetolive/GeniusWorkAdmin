<?php

namespace App\Http\Controllers;

use App\Models\Visite;
use App\Services\Visite\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Télécharger le ticket de visite
     *
     * @param Visite $visite
     * @return \Illuminate\Http\Response
     */
    public function telechargerTicket(Visite $visite)
    {
        // Vérifier que l'utilisateur a accès à cette visite (même entreprise)
        if ($visite->entreprise_id !== auth()->user()->entreprise_id) {
            abort(403, 'Vous n\'avez pas accès à cette visite.');
        }

        return $this->ticketService->generateTicket($visite, true);
    }
}
