<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Chemin vers le fichier Excel
$inputFileName = __DIR__ . '/docs/Rapport de présence.xls';

try {
    // Charger le fichier Excel
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Obtenir les dimensions de la feuille
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
    
    echo "Analyse du fichier: Rapport de présence.xls\n";
    echo "Nombre total de lignes: " . $highestRow . "\n";
    echo "Nombre total de colonnes: " . $highestColumnIndex . "\n\n";
    
    // Extraire les en-têtes (première ligne)
    echo "En-têtes des colonnes:\n";
    $headers = [];
    for ($col = 1; $col <= $highestColumnIndex; ++$col) {
        $cellValue = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
        if (!empty($cellValue)) {
            $headers[$col] = $cellValue;
            echo $col . ": " . $cellValue . "\n";
        }
    }
    
    echo "\nAperçu des données (5 premières lignes):\n";
    // Afficher les 5 premières lignes de données
    for ($row = 2; $row <= min(6, $highestRow); ++$row) {
        echo "Ligne " . $row . ":\n";
        for ($col = 1; $col <= $highestColumnIndex; ++$col) {
            if (isset($headers[$col])) {
                $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                echo "  " . $headers[$col] . ": " . $cellValue . "\n";
            }
        }
        echo "\n";
    }
    
    // Analyse statistique basique
    echo "Analyse statistique:\n";
    
    // Compter les présences/absences si ces colonnes existent
    $presenceCount = 0;
    $absenceCount = 0;
    $retardCount = 0;
    
    // Chercher les colonnes pertinentes
    $statusColumnIndex = null;
    foreach ($headers as $index => $header) {
        if (stripos($header, 'statut') !== false || stripos($header, 'status') !== false || 
            stripos($header, 'présence') !== false || stripos($header, 'presence') !== false) {
            $statusColumnIndex = $index;
            break;
        }
    }
    
    if ($statusColumnIndex) {
        for ($row = 2; $row <= $highestRow; ++$row) {
            $status = strtolower($worksheet->getCellByColumnAndRow($statusColumnIndex, $row)->getValue());
            if (strpos($status, 'présent') !== false || strpos($status, 'present') !== false) {
                $presenceCount++;
            } elseif (strpos($status, 'absent') !== false) {
                $absenceCount++;
            } elseif (strpos($status, 'retard') !== false || strpos($status, 'tard') !== false) {
                $retardCount++;
            }
        }
        
        echo "  Nombre de présences: " . $presenceCount . "\n";
        echo "  Nombre d'absences: " . $absenceCount . "\n";
        echo "  Nombre de retards: " . $retardCount . "\n";
    }
    
    // Recherche de colonnes de dates
    $dateColumnIndex = null;
    foreach ($headers as $index => $header) {
        if (stripos($header, 'date') !== false || stripos($header, 'jour') !== false) {
            $dateColumnIndex = $index;
            break;
        }
    }
    
    if ($dateColumnIndex) {
        $dates = [];
        for ($row = 2; $row <= $highestRow; ++$row) {
            $date = $worksheet->getCellByColumnAndRow($dateColumnIndex, $row)->getValue();
            if (!empty($date)) {
                // Convertir en date si c'est un numéro Excel
                if (is_numeric($date)) {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
                }
                $dates[] = $date;
            }
        }
        
        if (!empty($dates)) {
            $uniqueDates = array_unique($dates);
            echo "\nPériode couverte: " . min($uniqueDates) . " à " . max($uniqueDates) . "\n";
            echo "Nombre de jours distincts: " . count($uniqueDates) . "\n";
        }
    }
    
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    echo "Erreur lors de la lecture du fichier Excel: " . $e->getMessage();
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
