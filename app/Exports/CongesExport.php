<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class CongesExport implements FromCollection, WithHeadings, WithTitle, WithStyles
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
            'Rapport de congés',
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
            'Total jours de congés utilisés',
            $this->statistiques['total_conges_utilises']
        ]);
        
        $rows->push([
            'Total jours de congés restants',
            $this->statistiques['total_conges_restants']
        ]);
        
        $rows->push([
            'Moyenne de jours utilisés par employé',
            $this->statistiques['moyenne_conges_utilises']
        ]);
        
        // Ligne vide
        $rows->push([]);
        
        // En-tête des données employés
        $rows->push([
            'Employé',
            'Département',
            'Jours de congés utilisés',
            'Jours de congés restants',
            'Congés annuels',
            'Pourcentage utilisé'
        ]);
        
        // Données des employés
        foreach ($this->donnees as $donnee) {
            $congesAnnuels = $donnee['employe']->conges_annuels ?? 0;
            $pourcentageUtilise = $congesAnnuels > 0 
                ? round(($donnee['jours_utilises'] / $congesAnnuels) * 100, 2) 
                : 0;
                
            $rows->push([
                $donnee['employe']->nom . ' ' . $donnee['employe']->prenom,
                $donnee['employe']->departement->nom ?? 'N/A',
                $donnee['jours_utilises'],
                $donnee['jours_restants'],
                $congesAnnuels,
                $pourcentageUtilise . '%'
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
        return 'Rapport de congés';
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
            8 => ['font' => ['bold' => true]],
        ];
    }
}
