<?php

namespace App\Services\Paiement;

use App\Models\Facturation;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ManuelService implements PasserelleInterface
{
    /**
     * Initialiser un paiement manuel
     *
     * @param Facturation $facturation
     * @param User $initiateur
     * @param array $options
     * @return array
     */
    public function initialiser(Facturation $facturation, User $initiateur, array $options = []): array
    {
        try {
            // Créer un enregistrement de paiement
            $paiement = Paiement::create([
                'facturation_id' => $facturation->id,
                'abonnement_id' => $facturation->abonnement_id,
                'entreprise_id' => $facturation->entreprise_id,
                'initiateur_id' => $initiateur->id,
                'reference' => Paiement::genererReference(),
                'montant' => $facturation->montant_ttc,
                'devise' => $facturation->devise,
                'methode' => $options['methode'] ?? Paiement::METHODE_VIREMENT,
                'passerelle' => Paiement::PASSERELLE_MANUEL,
                'statut' => Paiement::STATUT_EN_ATTENTE,
                'date_paiement' => now(),
                'meta_donnees' => [
                    'email' => $initiateur->email,
                    'nom' => $initiateur->name,
                    'telephone' => $initiateur->telephone ?? null,
                    'facturation_numero' => $facturation->numero_facture,
                    'options' => $options,
                    'instructions' => $this->getInstructions($options['methode'] ?? Paiement::METHODE_VIREMENT)
                ]
            ]);

            return [
                'success' => true,
                'message' => 'Paiement manuel initialisé avec succès',
                'paiement' => $paiement,
                'reference' => $paiement->reference,
                'instructions' => $this->getInstructions($options['methode'] ?? Paiement::METHODE_VIREMENT)
            ];
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'initialisation du paiement manuel', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'facturation_id' => $facturation->id
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement
     *
     * @param Paiement $paiement
     * @return array
     */
    public function verifierStatut(Paiement $paiement): array
    {
        // Pour les paiements manuels, le statut est mis à jour manuellement
        // donc on retourne simplement le statut actuel
        return [
            'success' => true,
            'message' => 'Statut vérifié avec succès',
            'paiement' => $paiement,
            'status' => $paiement->statut
        ];
    }

    /**
     * Traiter la réponse de la passerelle (callback)
     *
     * @param array $donnees
     * @return array
     */
    public function traiterReponse(array $donnees): array
    {
        try {
            $reference = $donnees['reference'] ?? null;
            
            if (!$reference) {
                return [
                    'success' => false,
                    'message' => 'Référence manquante dans la réponse'
                ];
            }

            // Trouver le paiement correspondant
            $paiement = Paiement::where('reference', $reference)->first();

            if (!$paiement) {
                return [
                    'success' => false,
                    'message' => 'Paiement non trouvé pour la référence: ' . $reference
                ];
            }

            // Mettre à jour le paiement si des informations supplémentaires sont fournies
            if (isset($donnees['preuve_paiement']) || isset($donnees['commentaire'])) {
                $metaDonnees = $paiement->meta_donnees ?? [];
                
                if (isset($donnees['preuve_paiement'])) {
                    $metaDonnees['preuve_paiement'] = $donnees['preuve_paiement'];
                }
                
                $paiement->update([
                    'meta_donnees' => $metaDonnees,
                    'commentaire' => $donnees['commentaire'] ?? $paiement->commentaire
                ]);
            }

            return [
                'success' => true,
                'message' => 'Informations de paiement mises à jour avec succès',
                'paiement' => $paiement
            ];
        } catch (\Exception $e) {
            Log::error('Exception lors du traitement de la réponse manuel', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'donnees' => $donnees
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Annuler un paiement
     *
     * @param Paiement $paiement
     * @param string|null $raison
     * @return bool
     */
    public function annuler(Paiement $paiement, ?string $raison = null): bool
    {
        $paiement->update([
            'statut' => Paiement::STATUT_ANNULE,
            'commentaire' => $raison ?? 'Annulé par l\'utilisateur',
            'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                'annulation' => [
                    'date' => now()->toIso8601String(),
                    'raison' => $raison ?? 'Annulé par l\'utilisateur'
                ]
            ])
        ]);

        return true;
    }

    /**
     * Rembourser un paiement
     *
     * @param Paiement $paiement
     * @param float|null $montant
     * @param string|null $raison
     * @return bool
     */
    public function rembourser(Paiement $paiement, ?float $montant = null, ?string $raison = null): bool
    {
        // Pour les paiements manuels, le remboursement est également manuel
        $paiement->update([
            'statut' => Paiement::STATUT_REMBOURSE,
            'commentaire' => $raison ?? 'Remboursement demandé',
            'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                'remboursement' => [
                    'date' => now()->toIso8601String(),
                    'montant' => $montant ?? $paiement->montant,
                    'raison' => $raison ?? 'Remboursement demandé'
                ]
            ])
        ]);

        return true;
    }

    /**
     * Obtenir les instructions de paiement en fonction de la méthode
     *
     * @param string $methode
     * @return array
     */
    protected function getInstructions(string $methode): array
    {
        switch ($methode) {
            case Paiement::METHODE_VIREMENT:
                return [
                    'titre' => 'Instructions pour le virement bancaire',
                    'contenu' => [
                        'Veuillez effectuer un virement aux coordonnées bancaires suivantes:',
                        'Banque: ' . config('paiement.manuel.banque', 'Banque XYZ'),
                        'IBAN: ' . config('paiement.manuel.iban', 'XX00 0000 0000 0000 0000 0000'),
                        'BIC/SWIFT: ' . config('paiement.manuel.bic', 'XYZXYZXX'),
                        'Bénéficiaire: ' . config('paiement.manuel.beneficiaire', 'GENIUS WORK SARL'),
                        'Motif: Veuillez indiquer la référence ' . Paiement::genererReference() . ' dans le motif du virement'
                    ],
                    'note' => 'Votre abonnement sera activé après vérification du paiement par notre équipe (délai de 24 à 48h ouvrées).'
                ];
            case Paiement::METHODE_CHEQUE:
                return [
                    'titre' => 'Instructions pour le paiement par chèque',
                    'contenu' => [
                        'Veuillez envoyer votre chèque à l\'adresse suivante:',
                        config('paiement.manuel.adresse', 'GENIUS WORK SARL, 123 Avenue Principale, 00000 Ville, Pays'),
                        'Ordre: ' . config('paiement.manuel.ordre', 'GENIUS WORK SARL'),
                        'Veuillez indiquer la référence ' . Paiement::genererReference() . ' au dos du chèque'
                    ],
                    'note' => 'Votre abonnement sera activé après réception et encaissement du chèque (délai de 5 à 10 jours ouvrés).'
                ];
            case Paiement::METHODE_ESPECES:
                return [
                    'titre' => 'Instructions pour le paiement en espèces',
                    'contenu' => [
                        'Veuillez vous présenter à nos bureaux à l\'adresse suivante:',
                        config('paiement.manuel.adresse', 'GENIUS WORK SARL, 123 Avenue Principale, 00000 Ville, Pays'),
                        'Horaires: ' . config('paiement.manuel.horaires', 'Du lundi au vendredi de 9h à 17h'),
                        'Veuillez vous munir de la référence ' . Paiement::genererReference()
                    ],
                    'note' => 'Votre abonnement sera activé immédiatement après paiement.'
                ];
            default:
                return [
                    'titre' => 'Instructions de paiement',
                    'contenu' => [
                        'Veuillez contacter notre service client pour plus d\'informations:',
                        'Email: ' . config('paiement.manuel.email', 'contact@GENIUS WORK.com'),
                        'Téléphone: ' . config('paiement.manuel.telephone', '+XX XXX XXX XXX'),
                        'Référence à mentionner: ' . Paiement::genererReference()
                    ],
                    'note' => 'Votre abonnement sera activé après vérification du paiement par notre équipe.'
                ];
        }
    }
}
