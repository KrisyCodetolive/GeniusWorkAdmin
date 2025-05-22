<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class SimulateurCoutController extends Controller
{
    /**
     * Affiche le simulateur de coût
     */
    public function index()
    {
        return view('simulateur-cout');
    }

    /**
     * Calcule le coût en fonction du nombre d'utilisateurs
     */
    public function calculer(Request $request)
    {
        $nombreUtilisateurs = $request->input('nombre_utilisateurs', 0);
        
        // Déterminer le forfait et le coût fixe
        $forfait = '';
        $coutFixe = 0;
        
        if ($nombreUtilisateurs >= 1 && $nombreUtilisateurs <= 50) {
            $forfait = 'Starter';
            $coutFixe = 10000;
        } elseif ($nombreUtilisateurs > 50 && $nombreUtilisateurs <= 100) {
            $forfait = 'Side Business';
            $coutFixe = 15000;
        } elseif ($nombreUtilisateurs > 100) {
            $forfait = 'Entreprise';
            $coutFixe = 30000;
        }
        
        // Calculer le coût des utilisateurs
        $coutUtilisateurs = $nombreUtilisateurs * 100;
        
        // Calculer le coût total
        $coutTotal = $coutFixe + $coutUtilisateurs;
        
        return response()->json([
            'forfait' => $forfait,
            'coutFixe' => $coutFixe,
            'coutUtilisateurs' => $coutUtilisateurs,
            'coutTotal' => $coutTotal,
            'nombreUtilisateurs' => $nombreUtilisateurs
        ]);
    }

    public function genererDevis(Request $request)
    {
        $data = $request->validate([
            'nombreUtilisateurs' => 'required|integer|min:1',
            'forfait' => 'required|string',
            'coutFixe' => 'required|numeric',
            'coutUtilisateurs' => 'required|numeric',
            'coutTotal' => 'required|numeric'
        ]);

        $pdf = PDF::loadView('pdf.devis', [
            'nombreUtilisateurs' => $data['nombreUtilisateurs'],
            'forfait' => $data['forfait'],
            'coutFixe' => number_format($data['coutFixe'], 0, '.', ','),
            'coutUtilisateurs' => number_format($data['coutUtilisateurs'], 0, '.', ','),
            'coutTotal' => number_format($data['coutTotal'], 0, '.', ','),
            'date' => now()->format('Y-m-d'),
            'quoteNumber' => 'GW-' . now()->format('YmdHis')
        ]);

        return $pdf->download('GENIUS WORK-Quote.pdf');
    }

    private function determinerForfait($nombreUtilisateurs)
    {
        if ($nombreUtilisateurs <= 50) {
            return 'Starter';
        } elseif ($nombreUtilisateurs <= 100) {
            return 'Side Business';
        } else {
            return 'Entreprise';
        }
    }

    private function getCoutFixe($forfait)
    {
        return match($forfait) {
            'Starter' => 10000,
            'Side Business' => 15000,
            'Entreprise' => 30000,
            default => 10000
        };
    }
}
