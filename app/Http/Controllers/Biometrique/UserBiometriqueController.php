<?php

namespace App\Http\Controllers\Biometrique;

use App\Http\Controllers\Controller;
use App\Models\AppareilBiometrique;
use App\Models\User;
use App\Services\Biometrique\AppareilBiometriqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour la gestion des utilisateurs sur les appareils biométriques
 */
class UserBiometriqueController extends Controller
{
    /**
     * @var AppareilBiometriqueService
     */
    protected $appareilService;

    /**
     * Constructeur
     *
     * @param AppareilBiometriqueService $appareilService
     */
    public function __construct(AppareilBiometriqueService $appareilService)
    {
        $this->appareilService = $appareilService;
        $this->middleware('auth');
        $this->middleware('permission:gerer_appareils_biometriques');
    }

    /**
     * Affiche la liste des utilisateurs enregistrés sur les appareils
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filtres
        if ($request->has('site_id') && $request->site_id) {
            $query->whereHas('sites', function ($q) use ($request) {
                $q->where('sites.id', $request->site_id);
            });
        }

        if ($request->has('appareil_id') && $request->appareil_id) {
            $query->whereHas('appareilsBiometriques', function ($q) use ($request) {
                $q->where('appareil_biometriques.id', $request->appareil_id);
            });
        }

        if ($request->has('registered') && $request->registered !== null) {
            if ($request->registered) {
                $query->whereHas('appareilsBiometriques');
            } else {
                $query->whereDoesntHave('appareilsBiometriques');
            }
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->with('appareilsBiometriques')->paginate(20);

        return view('biometrique.users.index', [
            'users' => $users,
            'sites' => \App\Models\Site::orderBy('nom')->get(),
            'appareils' => AppareilBiometrique::orderBy('nom')->get()
        ]);
    }

    /**
     * Affiche les détails d'un utilisateur et ses appareils
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = User::with('appareilsBiometriques')->findOrFail($id);
        $availableDevices = AppareilBiometrique::whereDoesntHave('users', function ($q) use ($id) {
            $q->where('users.id', $id);
        })->get();
        
        return view('biometrique.users.show', [
            'user' => $user,
            'availableDevices' => $availableDevices
        ]);
    }

    /**
     * Enregistre un utilisateur sur un appareil
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'appareil_id' => 'required|exists:appareil_biometriques,id',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->appareilService->registerUserOnDevice(
            $request->user_id,
            $request->appareil_id,
            $request->options ?? []
        );
        
        return response()->json($result);
    }

    /**
     * Supprime un utilisateur d'un appareil
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'appareil_id' => 'required|exists:appareil_biometriques,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->appareilService->removeUserFromDevice(
            $request->user_id,
            $request->appareil_id
        );
        
        return response()->json($result);
    }

    /**
     * Enregistre un utilisateur sur plusieurs appareils
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'appareil_ids' => 'required|array',
            'appareil_ids.*' => 'exists:appareil_biometriques,id',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $success = true;

        foreach ($request->appareil_ids as $appareilId) {
            $result = $this->appareilService->registerUserOnDevice(
                $request->user_id,
                $appareilId,
                $request->options ?? []
            );
            
            $results[$appareilId] = $result;
            
            if (!$result['success']) {
                $success = false;
            }
        }
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Utilisateur enregistré sur tous les appareils' : 'Échec d\'enregistrement sur certains appareils',
            'results' => $results
        ]);
    }

    /**
     * Supprime un utilisateur de plusieurs appareils
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unregisterMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'appareil_ids' => 'required|array',
            'appareil_ids.*' => 'exists:appareil_biometriques,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $success = true;

        foreach ($request->appareil_ids as $appareilId) {
            $result = $this->appareilService->removeUserFromDevice(
                $request->user_id,
                $appareilId
            );
            
            $results[$appareilId] = $result;
            
            if (!$result['success']) {
                $success = false;
            }
        }
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Utilisateur supprimé de tous les appareils' : 'Échec de suppression sur certains appareils',
            'results' => $results
        ]);
    }

    /**
     * Enregistre plusieurs utilisateurs sur un appareil
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'appareil_id' => 'required|exists:appareil_biometriques,id',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $success = true;

        foreach ($request->user_ids as $userId) {
            $result = $this->appareilService->registerUserOnDevice(
                $userId,
                $request->appareil_id,
                $request->options ?? []
            );
            
            $results[$userId] = $result;
            
            if (!$result['success']) {
                $success = false;
            }
        }
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Tous les utilisateurs ont été enregistrés' : 'Échec d\'enregistrement pour certains utilisateurs',
            'results' => $results
        ]);
    }

    /**
     * Supprime plusieurs utilisateurs d'un appareil
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unregisterUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'appareil_id' => 'required|exists:appareil_biometriques,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $success = true;

        foreach ($request->user_ids as $userId) {
            $result = $this->appareilService->removeUserFromDevice(
                $userId,
                $request->appareil_id
            );
            
            $results[$userId] = $result;
            
            if (!$result['success']) {
                $success = false;
            }
        }
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Tous les utilisateurs ont été supprimés' : 'Échec de suppression pour certains utilisateurs',
            'results' => $results
        ]);
    }

    /**
     * Synchronise les utilisateurs d'un site avec un appareil
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncSiteUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'appareil_id' => 'required|exists:appareil_biometriques,id',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->appareilService->syncSiteUsersWithDevice(
            $request->site_id,
            $request->appareil_id,
            $request->options ?? []
        );
        
        return response()->json($result);
    }
}
