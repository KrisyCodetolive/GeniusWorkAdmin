<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class PresencesExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $donnees;
    protected $statistiques;
    protected $dateDebut;
    protected $dateFin;

    /**
     * @param array $donnees
     * @param array $statistiques
     * @param Carbon $dateDebut
     * @param Carbon $dateFin
     */
    public function __construct($donnees, $statistiques, Carbon $dateDebut, Carbon $dateFin)
    {
        $this->donnees = $donnees;
        $this->statistiques = $statistiques;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $rows = new Collection();

        // Ajouter les informations d'en-tête
        $rows->push([
            'Rapport de présences',
            'Période: ' . $this->dateDebut->format('d/m/Y') . ' - ' . $this->dateFin->format('d/m/Y')
        ]);
        
        $rows->push([
            'Statistiques globales',
            'Valeur'
        ]);
        
        $rows->push([
            'Nombre total d\'employés',
            $this->statistiques['total_employes']
        ]);
        
        $rows->push([
            'Total jours de présence',
            $this->statistiques['total_presences']
        ]);
        
        $rows->push([
            'Total jours d\'absence',
            $this->statistiques['total_absences']
        ]);
        
        $rows->push([
            'Total retards',
            $this->statistiques['total_retards']
        ]);
        
        $rows->push([
            'Taux de présence',
            $this->statistiques['taux_presence'] . '%'
        ]);
        
        $rows->push([
            'Taux de retard',
            $this->statistiques['taux_retard'] . '%'
        ]);
        
        // Ligne vide
        $rows->push([]);
        
        // En-tête des données employés
        $rows->push([
            'Employé',
            'Département',
            'Heures de présence',
            'Jours de présence',
            'Jours d\'absence',
            'Nombre de retards'
        ]);
        
        // Données des employés
        foreach ($this->donnees as $donnee) {
            $rows->push([
                $donnee['employe']->nom . ' ' . $donnee['employe']->prenom,
                $donnee['employe']->departement->nom ?? 'N/A',
                $donnee['heures_presence']['heures_formatees'],
                $donnee['jours_presence'],
                $donnee['jours_absence'],
                count($donnee['retards']['details_jours'])
            ]);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Rapport de présences';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            9 => ['font' => ['bold' => true]],
        ];
    }
}
