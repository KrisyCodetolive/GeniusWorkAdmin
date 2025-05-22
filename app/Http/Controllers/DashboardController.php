<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Redirect to the appropriate dashboard based on user role.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return redirect()->route('dashboard.admin');
        } elseif ($user->isEntreprise()) {
            return redirect()->route('dashboard.entreprise');
        } else {
            return redirect()->route('dashboard.employe');
        }
    }

    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin()
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard.index');
        }
        
        // Récupérer les données nécessaires pour le tableau de bord admin
        $data = [
            'title' => 'Tableau de bord Administrateur',
            // Ajouter d'autres données nécessaires pour le tableau de bord admin
        ];
        
        // Utiliser la vue existante pour le tableau de bord entreprise
        // Puisque nous n'avons pas de vue spécifique pour l'admin, nous utilisons celle de l'entreprise
        return view('app.entreprise.dashboard.index', $data);
    }

    /**
     * Display the enterprise dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function entreprise()
    {
        // Vérifier que l'utilisateur est un administrateur d'entreprise
        if (!Auth::user()->isEntreprise()) {
            return redirect()->route('dashboard.index');
        }
        
        // Récupérer les données nécessaires pour le tableau de bord entreprise
        $data = [
            'title' => 'Tableau de bord Entreprise',
            // Ajouter d'autres données nécessaires pour le tableau de bord entreprise
        ];
        
        // Utiliser la vue existante pour le tableau de bord entreprise
        return view('app.entreprise.dashboard.index', $data);
    }

    /**
     * Display the employee dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function employe()
    {
        // Récupérer les données nécessaires pour le tableau de bord employé
        $data = [
            'title' => 'Tableau de bord Employé',
            // Ajouter d'autres données nécessaires pour le tableau de bord employé
        ];
        
        // Utiliser la vue existante pour le tableau de bord employé
        return view('app.employe.dashboard.index', $data);
    }
}
