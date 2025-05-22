<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    /**
     * Impersonate a user (login as them)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $userId
     * @return \Illuminate\Http\Response
     */
    public function impersonate(Request $request, $userId)
    {
        // Vérifier si l'utilisateur actuel est un SuperAdmin ou Support
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport()) {
            return redirect()->back()->with('error', 'Vous n\'avez pas les permissions nécessaires pour cette action.');
        }

        // Trouver l'utilisateur à impersonate
        $userToImpersonate = User::find($userId);

        if (!$userToImpersonate) {
            return redirect()->back()->with('error', 'Utilisateur introuvable.');
        }

        // Sauvegarder l'ID de l'utilisateur original en session
        Session::put('impersonate_origin_id', Auth::id());
        
        // Sauvegarder le rôle original en session pour restaurer les permissions correctement
        Session::put('impersonate_origin_role', Auth::user()->role);

        // Se connecter en tant que l'utilisateur cible
        Auth::login($userToImpersonate);

        return redirect()->route('dashboard.index')
            ->with('success', 'Vous êtes maintenant connecté en tant que ' . $userToImpersonate->name);
    }

    /**
     * Revenir à l'utilisateur original
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function stopImpersonating(Request $request)
    {
        if (!Session::has('impersonate_origin_id')) {
            return redirect()->route('dashboard.index');
        }

        // Récupérer l'utilisateur original
        $originalUser = User::find(Session::get('impersonate_origin_id'));

        if (!$originalUser) {
            // Si l'utilisateur original n'existe plus, déconnecter simplement
            Auth::logout();
            Session::forget('impersonate_origin_id');
            Session::forget('impersonate_origin_role');
            return redirect()->route('login');
        }

        // Se reconnecter en tant qu'utilisateur original
        Auth::login($originalUser);

        // Supprimer les données d'impersonate de la session
        Session::forget('impersonate_origin_id');
        Session::forget('impersonate_origin_role');

        return redirect()->route('dashboard.index')
            ->with('success', 'Vous êtes revenu à votre compte original.');
    }
}
