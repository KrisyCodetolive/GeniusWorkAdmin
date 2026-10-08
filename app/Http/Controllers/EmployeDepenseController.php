<?php

namespace App\Http\Controllers;

use App\Models\CategorieDepense;
use App\Models\DemandeDepense;
use App\Models\JustificatifDepense;
use App\Models\ParametreDepense;
use App\Services\DepenseWorkflowService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * Espace employé : le demandeur crée ses demandes de dépense, les soumet au circuit
 * puis, une fois payées, joint ses reçus pour justification. Toutes les transitions
 * passent par DepenseWorkflowService, qui revérifie les droits (DemandeDepensePolicy).
 */
class EmployeDepenseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $demandes = DemandeDepense::query()
            ->where(function ($query) use ($user) {
                $query->where('cree_par_user_id', $user->id);

                if ($user->employeur_id) {
                    $query->orWhere('demandeur_id', $user->employeur_id);
                }
            })
            ->when(
                $request->filled('statut') && array_key_exists($request->statut, DemandeDepense::STATUTS),
                fn ($query) => $query->where('statut', $request->statut)
            )
            ->with(['categorie'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('employe.depenses.index', [
            'demandes' => $demandes,
            'statuts' => DemandeDepense::STATUTS,
        ]);
    }

    public function create(Request $request)
    {
        $entrepriseId = $this->entrepriseId($request->user());

        if (! $entrepriseId) {
            return redirect()->route('employe.depenses.index')
                ->with('error', 'Votre compte n\'est rattaché à aucune entreprise : vous ne pouvez pas créer de demande ici.');
        }

        $parametres = ParametreDepense::pour($entrepriseId);

        return view('employe.depenses.create', [
            'categories' => CategorieDepense::actif()->orderBy('nom')->get(),
            'parametres' => $parametres,
            'typesJustificatif' => JustificatifDepense::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $entrepriseId = $this->entrepriseId($user);

        if (! $entrepriseId) {
            return redirect()->route('employe.depenses.index')
                ->with('error', 'Votre compte n\'est rattaché à aucune entreprise : vous ne pouvez pas créer de demande ici.');
        }

        $data = $request->validate([
            'objet' => ['required', 'string', 'max:255'],
            'categorie_depense_id' => [
                'required',
                Rule::exists('categories_depense', 'id')->where('entreprise_id', $entrepriseId),
            ],
            'montant' => ['required', 'numeric', 'min:1'],
            'beneficiaire' => ['required', 'string', 'max:255'],
            'date_besoin' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'justificatifs' => ['nullable', 'array'],
            'justificatifs.*.type' => ['required_with:justificatifs.*.fichier', Rule::in(array_keys(JustificatifDepense::TYPES))],
            'justificatifs.*.fichier' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $demande = DB::transaction(function () use ($user, $data, $request, $entrepriseId) {
            $demande = DemandeDepense::create([
                'entreprise_id' => $entrepriseId,
                'demandeur_id' => $user->employeur_id,
                'cree_par_user_id' => $user->id,
                'categorie_depense_id' => $data['categorie_depense_id'],
                'objet' => $data['objet'],
                'description' => $data['description'] ?? null,
                'montant' => $data['montant'],
                'beneficiaire' => $data['beneficiaire'],
                'date_besoin' => $data['date_besoin'] ?? null,
            ]);

            foreach ($request->file('justificatifs', []) as $piece) {
                $this->enregistrerJustificatif($demande, $piece['fichier'] ?? null, $piece['type'] ?? 'autre');
            }

            return $demande;
        });

        return redirect()
            ->route('employe.depenses.show', $demande)
            ->with('success', "Demande {$demande->reference} enregistrée en brouillon. Vous pouvez la soumettre quand elle est prête.");
    }

    public function show(DemandeDepense $demande)
    {
        Gate::authorize('view', $demande);

        $demande->loadMissing(['categorie', 'departement', 'demandeur', 'creePar', 'payeePar', 'justificatifs', 'validations.user']);

        return view('employe.depenses.show', [
            'demande' => $demande,
            'typesJustificatif' => JustificatifDepense::TYPES,
        ]);
    }

    public function soumettre(DemandeDepense $demande, DepenseWorkflowService $service)
    {
        return $this->executer(
            $demande,
            fn () => $service->soumettre($demande, request()->user()),
            'Demande soumise à la comptabilité.'
        );
    }

    public function annuler(Request $request, DemandeDepense $demande, DepenseWorkflowService $service)
    {
        return $this->executer(
            $demande,
            fn () => $service->annuler($demande, $request->user(), $request->input('motif')),
            'Demande annulée.'
        );
    }

    /**
     * Ajout d'une pièce : en brouillon (devis, facture…) ou après paiement (reçu de justification).
     */
    public function ajouterJustificatif(Request $request, DemandeDepense $demande)
    {
        Gate::authorize('ajouterJustificatif', $demande);

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(JustificatifDepense::TYPES))],
            'fichier' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $this->enregistrerJustificatif($demande, $data['fichier'], $data['type']);

        return back()->with('success', 'Justificatif ajouté.');
    }

    /**
     * Décharge du demandeur après approbation : le paiement n'est possible qu'ensuite.
     */
    public function signer(Request $request, DemandeDepense $demande, DepenseWorkflowService $service)
    {
        $request->validate(['signature' => ['required', 'string']]);

        return $this->executer(
            $demande,
            fn () => $service->signerParDemandeur($demande, $request->user(), $request->input('signature')),
            'Signature enregistrée. La comptabilité peut procéder au paiement.'
        );
    }

    /**
     * Les reçus sont joints : la justification part en vérification comptable.
     */
    public function soumettreJustification(DemandeDepense $demande, DepenseWorkflowService $service)
    {
        return $this->executer(
            $demande,
            fn () => $service->soumettreJustification($demande, request()->user()),
            'Justification envoyée à la comptabilité pour vérification.'
        );
    }

    /**
     * Entreprise de rattachement du demandeur : compte utilisateur, sinon sa fiche employé.
     */
    private function entrepriseId($user): ?string
    {
        return $user->entreprise_id ?? $user->employeur?->entreprise_id;
    }

    private function enregistrerJustificatif(DemandeDepense $demande, $fichier, string $type): void
    {
        if (! $fichier) {
            return;
        }

        $demande->justificatifs()->create([
            'fichier' => $fichier->store("depenses/{$demande->id}/justificatifs", DepenseWorkflowService::DISQUE),
            'nom_original' => $fichier->getClientOriginalName(),
            'type' => $type,
        ]);
    }

    /**
     * Exécute une transition du circuit et traduit les refus (droits, données invalides)
     * en message d'erreur plutôt qu'en page d'exception.
     */
    private function executer(DemandeDepense $demande, callable $etape, string $succes)
    {
        try {
            $etape();
        } catch (AuthorizationException) {
            return back()->with('error', "Cette action n'est pas possible sur cette demande (droits insuffisants ou étape déjà franchie).");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('employe.depenses.show', $demande)->with('success', $succes);
    }
}
