<?php

namespace App\Http\Controllers;

use App\Services\CongeRapportService;
use App\Models\Departement;
use App\Models\Site;
use App\Models\TypeConge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CongeRapportController extends Controller
{
    /**
     * Le service de rapport de congés
     *
     * @var CongeRapportService
     */
    protected $rapportService;

    /**
     * Constructeur
     *
     * @param CongeRapportService $rapportService
     */
    public function __construct(CongeRapportService $rapportService)
    {
        $this->rapportService = $rapportService;
    }

    /**
     * Affiche la page des rapports de congés
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupérer les données pour les filtres
        $departements = Departement::orderBy('nom')->get();
        $sites = Site::orderBy('nom')->get();
        $typesConge = TypeConge::orderBy('libelle')->get();
        $employes = User::orderBy('nom')->orderBy('prenom')->get();

        return view('app.conge.rapports.index', compact(
            'departements',
            'sites',
            'typesConge',
            'employes'
        ));
    }

    /**
     * Récupère les données pour les rapports en fonction des filtres
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        // Valider les données
        $validator = Validator::make($request->all(), [
            'filters' => 'required|array',
            'report_type' => 'required|string|in:summary,by-department,by-type,by-employee,timeline',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Récupérer les filtres
        $filters = $request->input('filters');
        $reportType = $request->input('report_type');

        // Récupérer les données en fonction du type de rapport
        $data = [];
        switch ($reportType) {
            case 'summary':
                $data['summary'] = $this->rapportService->getSummaryData($filters);
                break;
            case 'by-department':
                $data['byDepartment'] = $this->rapportService->getDepartmentData($filters);
                break;
            case 'by-type':
                $data['byType'] = $this->rapportService->getTypeData($filters);
                break;
            case 'by-employee':
                $data['byEmployee'] = $this->rapportService->getEmployeeData($filters);
                break;
            case 'timeline':
                $data['timeline'] = $this->rapportService->getTimelineData($filters);
                break;
            default:
                // Si aucun type spécifique n'est demandé, récupérer toutes les données
                $data = $this->rapportService->getAllReportData($filters);
                break;
        }

        return response()->json($data);
    }

    /**
     * Exporte les données du rapport au format spécifié
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // Valider les données
        $validator = Validator::make($request->all(), [
            'filters' => 'required|string',
            'report_type' => 'required|string|in:summary,by-department,by-type,by-employee,timeline',
            'format' => 'required|string|in:pdf,excel,csv',
            'include_charts' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Récupérer les filtres
        $filters = json_decode($request->input('filters'), true);
        $reportType = $request->input('report_type');
        $format = $request->input('format');
        $includeCharts = $request->input('include_charts', false);

        // Récupérer les données en fonction du type de rapport
        $data = [];
        switch ($reportType) {
            case 'summary':
                $data['summary'] = $this->rapportService->getSummaryData($filters);
                break;
            case 'by-department':
                $data['byDepartment'] = $this->rapportService->getDepartmentData($filters);
                break;
            case 'by-type':
                $data['byType'] = $this->rapportService->getTypeData($filters);
                break;
            case 'by-employee':
                $data['byEmployee'] = $this->rapportService->getEmployeeData($filters);
                break;
            case 'timeline':
                $data['timeline'] = $this->rapportService->getTimelineData($filters);
                break;
            default:
                // Si aucun type spécifique n'est demandé, récupérer toutes les données
                $data = $this->rapportService->getAllReportData($filters);
                break;
        }

        // Exporter les données
        $options = [
            'include_charts' => $includeCharts,
            'report_type' => $reportType,
            'user' => Auth::user(),
        ];

        return $this->rapportService->exportReport($data, $format, $options);
    }
}
