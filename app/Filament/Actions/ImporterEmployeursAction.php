<?php

namespace App\Filament\Actions;

use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\Departement;
use App\Models\Filiale;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ImporterEmployeursAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'importerEmployes';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Importer des employés')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->form([
                Forms\Components\Section::make('Importation d\'employés')
                    ->description('Téléchargez le modèle Excel, remplissez-le avec les données des employés, puis importez-le.')
                    ->schema([
                        Forms\Components\FileUpload::make('fichier_excel')
                            ->label('Fichier Excel')
                            ->helperText('Formats acceptés: .xlsx, .xls')
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->maxSize(5120) // 5 MB
                            ->required(),
                            
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('telecharger_modele')
                                ->label('Télécharger le modèle Excel')
                                ->icon('heroicon-o-document-arrow-down')
                                ->color('primary')
                                ->url(function () {
                                    return $this->genererModeleExcel();
                                }, true)
                        ]),
                    ]),
            ])
            ->requiresConfirmation()
            ->modalHeading('Importer des employés')
            ->modalDescription('Cette action vous permet d\'importer plusieurs employés à la fois à partir d\'un fichier Excel. Assurez-vous que votre fichier respecte le format du modèle fourni.')
            ->modalSubmitActionLabel('Importer')
            ->action(function (array $data): void {
                $user = auth()->user();
                $entreprise = $user->entreprise;
                
                if (!$entreprise) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Vous devez être associé à une entreprise pour importer des employés.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Vérifier si l'entreprise a un abonnement actif
                if (!$entreprise->abonnementActif) {
                    Notification::make()
                        ->title('Erreur')
                        ->body('Votre entreprise n\'a pas d\'abonnement actif. Veuillez souscrire à un abonnement avant d\'importer des employés.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Récupérer le fichier téléchargé
                $filePath = Storage::disk('public')->path($data['fichier_excel']);
                
                try {
                    // Charger le fichier Excel
                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    
                    // Récupérer les données (en ignorant la première ligne d'en-tête)
                    $rows = $worksheet->toArray();
                    $headers = array_shift($rows); // Enlever les en-têtes
                    
                    // Vérifier la limite d'employés pour l'entreprise
                    $currentEmployeeCount = $entreprise->getEmployeCount();
                    $limit = $entreprise->abonnementActif->nombre_personnels ?? 
                            $entreprise->abonnementActif->planAbonnement->nombre_employes_max ?? 0;
                    
                    // Nombre d'employés à importer
                    $nombreEmployesAImporter = count($rows);
                    
                    // Vérifier si l'importation dépasserait la limite
                    if (($currentEmployeeCount + $nombreEmployesAImporter) > $limit) {
                        Notification::make()
                            ->title('Limite d\'employés atteinte')
                            ->body('Votre abonnement actuel permet un maximum de ' . $limit . ' employés. Vous avez déjà ' . $currentEmployeeCount . ' employés. L\'importation de ' . $nombreEmployesAImporter . ' employés dépasserait cette limite. Veuillez mettre à niveau votre abonnement ou réduire le nombre d\'employés à importer.')
                            ->warning()
                            ->send();
                        return;
                    }
                    
                    // Préparer les résultats
                    $created = [];
                    $errors = [];
                    
                    // Traiter chaque ligne
                    foreach ($rows as $index => $row) {
                        // Ignorer les lignes vides
                        if (empty(array_filter($row))) {
                            continue;
                        }
                        
                        // Récupérer les données de la ligne
                        $rowData = array_combine($headers, $row);
                        
                        try {
                            // Trouver ou créer le département si spécifié
                            $departementId = null;
                            if (!empty($rowData['Département'])) {
                                $departement = Departement::firstOrCreate(
                                    ['nom' => $rowData['Département'], 'entreprise_id' => $entreprise->id],
                                    ['description' => 'Département créé via importation']
                                );
                                $departementId = $departement->id;
                            }
                            
                            // Trouver ou créer la filiale si spécifiée
                            $filialeId = null;
                            if (!empty($rowData['Filiale'])) {
                                $filiale = Filiale::firstOrCreate(
                                    ['nom' => $rowData['Filiale'], 'entreprise_id' => $entreprise->id],
                                    ['description' => 'Filiale créée via importation']
                                );
                                $filialeId = $filiale->id;
                            }
                            
                            // Formater la date d'embauche
                            $dateEmbauche = null;
                            if (!empty($rowData['Date d\'embauche'])) {
                                try {
                                    $dateEmbauche = Carbon::createFromFormat('d/m/Y', $rowData['Date d\'embauche'])->format('Y-m-d');
                                } catch (\Exception $e) {
                                    // Essayer un autre format si le premier échoue
                                    try {
                                        $dateEmbauche = Carbon::parse($rowData['Date d\'embauche'])->format('Y-m-d');
                                    } catch (\Exception $e2) {
                                        $errors[] = "Ligne " . ($index + 2) . ": Format de date d'embauche invalide pour " . $rowData['Nom'] . " " . $rowData['Prénom'];
                                        continue;
                                    }
                                }
                            }
                            
                            // Formater la date de naissance
                            $dateNaissance = null;
                            if (!empty($rowData['Date de naissance'])) {
                                try {
                                    $dateNaissance = Carbon::createFromFormat('d/m/Y', $rowData['Date de naissance'])->format('Y-m-d');
                                } catch (\Exception $e) {
                                    // Essayer un autre format si le premier échoue
                                    try {
                                        $dateNaissance = Carbon::parse($rowData['Date de naissance'])->format('Y-m-d');
                                    } catch (\Exception $e2) {
                                        $errors[] = "Ligne " . ($index + 2) . ": Format de date de naissance invalide pour " . $rowData['Nom'] . " " . $rowData['Prénom'];
                                        continue;
                                    }
                                }
                            }
                            
                            // Générer un code employé unique
                            $nom = $rowData['Nom'] ?? '';
                            $prenom = $rowData['Prénom'] ?? '';
                            $codeEmploye = 'EMP-' . strtoupper(substr($nom, 0, 3) . substr($prenom, 0, 2)) . 
                                           str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                            
                            // Vérifier si ce code existe déjà dans la base de données
                            $codeExists = Employeur::where('code_employe', $codeEmploye)->exists();
                            
                            // Si le code existe déjà, ajouter un timestamp pour le rendre unique
                            if ($codeExists) {
                                $codeEmploye = 'EMP-' . strtoupper(substr($nom, 0, 3) . substr($prenom, 0, 2)) . 
                                              str_pad($index + 1, 3, '0', STR_PAD_LEFT) . '-' . time();
                            }
                            
                            // Générer un QR code secret
                            $qrCodeSecret = Str::uuid()->toString();
                            
                            // Créer l'employé
                            $employe = new Employeur();
                            $employe->entreprise_id = $entreprise->id;
                            $employe->departement_id = $departementId;
                            $employe->filiale_id = $filialeId;
                            $employe->nom = $nom;
                            $employe->prenom = $prenom;
                            $employe->email = $rowData['Email'] ?? '';
                            $employe->telephone = $rowData['Téléphone'] ?? '';
                            $employe->date_naissance = $dateNaissance;
                            $employe->lieu_naissance = $rowData['Lieu de naissance'] ?? '';
                            $employe->genre = $rowData['Genre'] ?? '';
                            $employe->date_embauche = $dateEmbauche;
                            $employe->type_contrat = $rowData['Type de contrat'] ?? 'CDI';
                            $employe->statut = $rowData['Statut'] ?? 'actif';
                            $employe->poste = $rowData['Poste'] ?? '';
                            $employe->salaire_base = $rowData['Salaire de base'] ?? 0;
                            $employe->code_employe = $codeEmploye;
                            $employe->qr_code_secret = $qrCodeSecret;
                            $employe->qr_code_expires_at = Carbon::now()->addYear();
                            $employe->qr_code_active = true;
                            
                            // Sauvegarder l'employé
                            $employe->save();
                            
                            $created[] = $employe->nom_complet;
                        } catch (\Exception $e) {
                            $errors[] = "Ligne " . ($index + 2) . ": " . $e->getMessage();
                            Log::error('Erreur lors de l\'importation d\'un employé', [
                                'ligne' => $index + 2,
                                'donnees' => $rowData,
                                'erreur' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                    
                    // Supprimer le fichier temporaire
                    Storage::disk('public')->delete($data['fichier_excel']);
                    
                    // Préparer le message de résultat
                    $message = '';
                    if (count($created) > 0) {
                        $message .= count($created) . ' employé(s) importé(s) avec succès.';
                    }
                    
                    if (count($errors) > 0) {
                        $message .= ' ' . count($errors) . ' erreur(s) rencontrée(s).';
                        $message .= '<br><br>Détails des erreurs:<br>' . implode('<br>', $errors);
                    }
                    
                    // Déterminer le type de notification en fonction du résultat
                    $notificationType = count($created) > 0 ? 'success' : 'warning';
                    
                    // Créer la notification avec le message
                    $notification = Notification::make()
                        ->title(count($created) > 0 ? 'Importation réussie' : 'Importation échouée')
                        ->body($message)
                        ->$notificationType();
                    
                    $notification->send();
                    
                } catch (\Exception $e) {
                    Log::error('Erreur lors de l\'importation des employés', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    Notification::make()
                        ->title('Erreur lors de l\'importation')
                        ->body('Une erreur est survenue: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Génère un modèle Excel pour l'importation d'employés
     * 
     * @return string URL du fichier généré
     */
    protected function genererModeleExcel(): string
    {
        // Créer un nouveau spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Modèle importation employés');
        
        // Définir les en-têtes
        $headers = [
            'Nom', 'Prénom', 'Email', 'Téléphone', 'Date de naissance', 
            'Lieu de naissance', 'Genre', 'Poste', 'Date d\'embauche', 
            'Type de contrat', 'Statut', 'Département', 'Filiale', 'Salaire de base'
        ];
        
        // Ajouter les en-têtes
        foreach ($headers as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key + 1, 1, $header);
            $sheet->getStyleByColumnAndRow($key + 1, 1)->getFont()->setBold(true);
        }
        
        // Ajouter des exemples avec des noms ivoiriens
        $examples = [
            ['Koné', 'Ibrahim', 'ibrahim.kone@example.com', '+225 07 12 34 56', '22/08/1980', 'Bouaké', 'Homme', 'Directeur Financier', '15/06/2017', 'CDI', 'actif', 'Finance', 'Siège', '1800000'],
            ['Kouassi', 'Aya', 'aya.kouassi@example.com', '+225 05 98 76 54', '15/05/1985', 'Abidjan', 'Femme', 'Directrice des Ressources Humaines', '01/03/2018', 'CDI', 'actif', 'Ressources Humaines', 'Siège', '1500000'],
            ['Touré', 'Amadou', 'amadou.toure@example.com', '+225 01 45 67 89', '18/04/1990', 'Yamoussoukro', 'Homme', 'Ingénieur Informatique', '15/01/2020', 'CDI', 'actif', 'Informatique', 'Siège', '1100000'],
            ['Bamba', 'Mariam', 'mariam.bamba@example.com', '+225 05 67 89 01', '30/11/1988', 'Abidjan', 'Femme', 'Responsable Marketing', '10/02/2019', 'CDI', 'actif', 'Marketing', 'Siège', '1200000'],
            ['Diallo', 'Fatou', 'fatou.diallo@example.com', '+225 01 23 45 67', '05/09/1992', 'Abidjan', 'Femme', 'Comptable', '01/05/2021', 'CDI', 'actif', 'Finance', 'Siège', '900000'],
            ['Yao', 'Kouadio', 'kouadio.yao@example.com', '+225 07 89 01 23', '03/12/1991', 'Daloa', 'Homme', 'Technicien Maintenance', '01/03/2022', 'CDD', 'actif', 'Technique', 'Agence Ouest', '700000'],
        ];
        
        foreach ($examples as $rowIndex => $example) {
            foreach ($example as $colIndex => $value) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $value);
            }
        }
        
        // Ajouter des commentaires d'aide
        $sheet->getCommentByColumnAndRow(1, 1)->getText()->createTextRun('Nom de famille de l\'employé (obligatoire)');
        $sheet->getCommentByColumnAndRow(2, 1)->getText()->createTextRun('Prénom de l\'employé (obligatoire)');
        $sheet->getCommentByColumnAndRow(3, 1)->getText()->createTextRun('Adresse email professionnelle (obligatoire)');
        $sheet->getCommentByColumnAndRow(5, 1)->getText()->createTextRun('Format: JJ/MM/AAAA');
        $sheet->getCommentByColumnAndRow(7, 1)->getText()->createTextRun('Valeurs possibles: Homme, Femme');
        $sheet->getCommentByColumnAndRow(9, 1)->getText()->createTextRun('Format: JJ/MM/AAAA');
        $sheet->getCommentByColumnAndRow(10, 1)->getText()->createTextRun('Valeurs possibles: CDI, CDD, Stage, Prestation');
        $sheet->getCommentByColumnAndRow(11, 1)->getText()->createTextRun('Valeurs possibles: actif, inactif');
        $sheet->getCommentByColumnAndRow(12, 1)->getText()->createTextRun('Si le département n\'existe pas, il sera créé');
        $sheet->getCommentByColumnAndRow(13, 1)->getText()->createTextRun('Si la filiale n\'existe pas, elle sera créée');
        $sheet->getCommentByColumnAndRow(14, 1)->getText()->createTextRun('Salaire de base annuel en FCFA');
        
        // Auto-dimensionner les colonnes
        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Générer le nom du fichier
        $filename = 'modele_import_employes_' . Str::random(8) . '.xlsx';
        $path = 'templates/' . $filename;
        
        // Sauvegarder le fichier
        $writer = new Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $path);
        
        // Créer le répertoire s'il n'existe pas
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        
        $writer->save($tempPath);
        
        // Retourner l'URL du fichier
        return Storage::disk('public')->url($path);
    }

    public static function make(?string $name = null): static
    {
        $action = parent::make($name);
        
        // Vérifier si l'utilisateur a le droit d'utiliser cette action
        $user = auth()->user();
        if (!$user || !$user->entreprise_id || !($user->isAdmin() || $user->isSuperAdmin() || $user->isSupport())) {
            $action->hidden();
        }
        
        return $action;
    }
}
