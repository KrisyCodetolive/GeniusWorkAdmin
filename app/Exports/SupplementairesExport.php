<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class SupplementairesExport implements FromCollection, WithHeadings, WithTitle, WithStyles
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
            'Rapport d\'heures supplémentaires',
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
            'Total heures supplémentaires',
            $this->statistiques['total_heures_formatees']
        ]);
        
        $rows->push([
            'Moyenne minutes par employé',
            $this->statistiques['moyenne_minutes_par_employe']
        ]);
        
        // Ligne vide
        $rows->push([]);
        
        // Statistiques par département
        $rows->push([
            'Heures supplémentaires par département',
            'Heures'
        ]);
        
        foreach ($this->statistiques['supplementaires_par_departement'] as $departement) {
            $rows->push([
                $departement['departement']->nom,
                $departement['heures_formatees']
            ]);
        }
        
        // Ligne vide
        $rows->push([]);
        
        // En-tête des données employés
        $rows->push([
            'Employé',
            'Département',
            'Heures supplémentaires',
            'Nombre d\'occurrences'
        ]);
        
        // Données des employés
        foreach ($this->donnees as $donnee) {
            $rows->push([
                $donnee['employe']->nom . ' ' . $donnee['employe']->prenom,
                $donnee['employe']->departement->nom ?? 'N/A',
                $donnee['heures_formatees'],
                count($donnee['supplementaires'])
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
        return 'Rapport d\'heures supplémentaires';
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
            7 => ['font' => ['bold' => true]],
            count($this->statistiques['supplementaires_par_departement']) + 9 => ['font' => ['bold' => true]],
        ];
    }
}
