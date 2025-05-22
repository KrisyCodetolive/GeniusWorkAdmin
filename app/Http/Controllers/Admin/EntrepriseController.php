<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entreprise;
use App\Models\Presence;
use App\Models\Conge;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EntrepriseController extends Controller
{
    /**
     * Affiche le tableau de bord de l'entreprise
     */
    public function dashboard()
    {
        $entreprise = Auth::user()->entreprise;
        return view('dashboard.entreprise.index', compact('entreprise'));
    }
    
    /**
     * Affiche la page des rapports
     */
    public function rapports()
    {
        $entreprise = Auth::user()->entreprise;
        return view('dashboard.entreprise.rapports.index', compact('entreprise'));
    }
    
    /**
     * Affiche la page des abonnements
     */
    public function abonnements()
    {
        $entreprise = Auth::user()->entreprise;
        $abonnements = $entreprise->abonnements;
        return view('dashboard.entreprise.abonnements.index', compact('entreprise', 'abonnements'));
    }
    
    /**
     * Affiche la page des paramètres
     */
    public function parametres()
    {
        $entreprise = Auth::user()->entreprise;
        return view('dashboard.entreprise.parametres.index', compact('entreprise'));
    }
    
    /**
     * Affiche le profil de l'entreprise
     */
    public function profil()
    {
        $entreprise = Auth::user()->entreprise;
        return view('dashboard.entreprise.profil.index', compact('entreprise'));
    }
    
    /**
     * Met à jour le profil de l'entreprise
     */
    public function updateProfil(Request $request)
    {
        $entreprise = Auth::user()->entreprise;
        
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $entreprise->logo = $logoPath;
        }
        
        $entreprise->nom = $validated['nom'];
        $entreprise->adresse = $validated['adresse'];
        $entreprise->telephone = $validated['telephone'];
        $entreprise->email = $validated['email'];
        $entreprise->save();
        
        return redirect()->route('entreprise.profil')->with('success', 'Profil mis à jour avec succès');
    }
    
    /**
     * Affiche la liste des présences
     */
    public function presencesIndex()
    {
        $entreprise = Auth::user()->entreprise;
        $presences = Presence::whereHas('employeur', function($query) use ($entreprise) {
            $query->where('entreprise_id', $entreprise->id);
        })->orderBy('created_at', 'desc')->paginate(15);
        
        return view('dashboard.entreprise.presences.index', compact('presences'));
    }
    
    /**
     * Affiche le rapport des présences
     */
    public function presencesRapport()
    {
        $entreprise = Auth::user()->entreprise;
        return view('dashboard.entreprise.presences.rapport', compact('entreprise'));
    }
    
    /**
     * Affiche la page de validation des présences
     */
    public function presencesValidation()
    {
        $entreprise = Auth::user()->entreprise;
        $presencesEnAttente = Presence::whereHas('employeur', function($query) use ($entreprise) {
            $query->where('entreprise_id', $entreprise->id);
        })->where('statut', 'en_attente')->orderBy('created_at', 'desc')->paginate(15);
        
        return view('dashboard.entreprise.presences.validation', compact('presencesEnAttente'));
    }
    
    /**
     * Valide une présence
     */
    public function validerPresence($id)
    {
        $presence = Presence::findOrFail($id);
        
        // Vérifier que la présence appartient à l'entreprise de l'utilisateur
        $entreprise = Auth::user()->entreprise;
        if ($presence->employeur->entreprise_id != $entreprise->id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à valider cette présence');
        }
        
        $presence->statut = 'validee';
        $presence->validateur_id = Auth::id();
        $presence->date_validation = now();
        $presence->save();
        
        return redirect()->back()->with('success', 'Présence validée avec succès');
    }
    
    /**
     * Rejette une présence
     */
    public function rejeterPresence($id)
    {
        $presence = Presence::findOrFail($id);
        
        // Vérifier que la présence appartient à l'entreprise de l'utilisateur
        $entreprise = Auth::user()->entreprise;
        if ($presence->employeur->entreprise_id != $entreprise->id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à rejeter cette présence');
        }
        
        $presence->statut = 'rejetee';
        $presence->validateur_id = Auth::id();
        $presence->date_validation = now();
        $presence->save();
        
        return redirect()->back()->with('success', 'Présence rejetée');
    }
    
    /**
     * Affiche la liste des congés
     */
    public function congesIndex()
    {
        $entreprise = Auth::user()->entreprise;
        $conges = Conge::whereHas('employeur', function($query) use ($entreprise) {
            $query->where('entreprise_id', $entreprise->id);
        })->orderBy('created_at', 'desc')->paginate(15);
        
        return view('dashboard.entreprise.conges.index', compact('conges'));
    }
    
    /**
     * Affiche le calendrier des congés
     */
    public function congesCalendrier()
    {
        $entreprise = Auth::user()->entreprise;
        $conges = Conge::whereHas('employeur', function($query) use ($entreprise) {
            $query->where('entreprise_id', $entreprise->id);
        })->where('statut', 'approuve')->get();
        
        return view('dashboard.entreprise.conges.calendrier', compact('conges'));
    }
    
    /**
     * Affiche la page de validation des congés
     */
    public function congesValidation()
    {
        $entreprise = Auth::user()->entreprise;
        $congesEnAttente = Conge::whereHas('employeur', function($query) use ($entreprise) {
            $query->where('entreprise_id', $entreprise->id);
        })->where('statut', 'en_attente')->orderBy('created_at', 'desc')->paginate(15);
        
        return view('dashboard.entreprise.conges.validation', compact('congesEnAttente'));
    }
    
    /**
     * Valide un congé
     */
    public function validerConge($id)
    {
        $conge = Conge::findOrFail($id);
        
        // Vérifier que le congé appartient à l'entreprise de l'utilisateur
        $entreprise = Auth::user()->entreprise;
        if ($conge->employeur->entreprise_id != $entreprise->id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à valider ce congé');
        }
        
        $conge->statut = 'approuve';
        $conge->validateur_id = Auth::id();
        $conge->date_validation = now();
        $conge->save();
        
        return redirect()->back()->with('success', 'Congé approuvé avec succès');
    }
    
    /**
     * Rejette un congé
     */
    public function rejeterConge($id)
    {
        $conge = Conge::findOrFail($id);
        
        // Vérifier que le congé appartient à l'entreprise de l'utilisateur
        $entreprise = Auth::user()->entreprise;
        if ($conge->employeur->entreprise_id != $entreprise->id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à rejeter ce congé');
        }
        
        $conge->statut = 'rejete';
        $conge->validateur_id = Auth::id();
        $conge->date_validation = now();
        $conge->save();
        
        return redirect()->back()->with('success', 'Congé rejeté');
    }
    
    /**
     * Affiche les notifications de l'entreprise
     */
    public function notifications()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(15);
        
        return view('dashboard.entreprise.notifications.index', compact('notifications'));
    }
}
