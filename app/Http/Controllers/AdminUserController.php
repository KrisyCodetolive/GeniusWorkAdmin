<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs pour les SuperAdmin et Support
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Vérifier si l'utilisateur est SuperAdmin ou Support
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport()) {
            return redirect()->route('dashboard.index')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
        }

        // Récupérer les filtres
        $entrepriseId = $request->input('entreprise_id');
        $role = $request->input('role');
        $search = $request->input('search');

        // Requête de base
        $query = User::query()->with('entreprise');

        // Appliquer les filtres
        if ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Récupérer les utilisateurs paginés
        $users = $query->orderBy('name')->paginate(15);

        // Récupérer toutes les entreprises pour le filtre
        $entreprises = Entreprise::orderBy('nom')->get();

        // Récupérer tous les rôles disponibles
        $roles = User::select('role')->distinct()->pluck('role')->toArray();

        return view('admin.users.index', compact('users', 'entreprises', 'roles', 'entrepriseId', 'role', 'search'));
    }
}
