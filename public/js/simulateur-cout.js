document.addEventListener('alpine:init', () => {
    Alpine.data('simulateurCout', () => ({
        nombreUtilisateurs: 1,
        forfait: '',
        forfaitActif: '',
        coutFixe: 0,
        coutUtilisateurs: 0,
        coutTotal: 0,
        coutParUtilisateur: 100,
        periodeFacturation: 'Mensuel',
        coutAnnuel: 0,
        economieAnnuelle: 0,
        resultatVisible: false,
        loading: false,
        error: null,
        showTooltip: false,
        comparaisonVisible: false,

        init() {
            this.$watch('nombreUtilisateurs', () => {
                this.calculerCout();
            });
            this.calculerCout();
        },

        formatNumber(number) {
            return new Intl.NumberFormat('fr-FR').format(number);
        },

        calculerCout() {
            this.loading = true;
            this.error = null;

            try {
                // Déterminer le forfait et le coût fixe en fonction du nombre d'utilisateurs
                if (this.nombreUtilisateurs <= 50) {
                    this.forfait = 'Starter';
                    this.coutFixe = 10000;
                    this.economieAnnuelle = 0; // Pas d'économie pour le forfait de base
                } else if (this.nombreUtilisateurs <= 100) {
                    this.forfait = 'Side Business';
                    this.coutFixe = 15000;
                    // Économie par rapport au forfait Starter
                    const coutStarter = (10000 + (this.nombreUtilisateurs * 100)) * 12;
                    this.economieAnnuelle = coutStarter - ((15000 + (this.nombreUtilisateurs * 100)) * 12);
                } else {
                    this.forfait = 'Enterprise';
                    this.coutFixe = 30000;
                    // Économie par rapport au forfait Side Business
                    const coutSideBusiness = (15000 + (this.nombreUtilisateurs * 100)) * 12;
                    this.economieAnnuelle = coutSideBusiness - ((30000 + (this.nombreUtilisateurs * 100)) * 12);
                }

                // Calculer le coût des utilisateurs (100 FCFA par utilisateur)
                this.coutUtilisateurs = this.nombreUtilisateurs * this.coutParUtilisateur;
                
                // Calculer le coût total mensuel
                this.coutTotal = this.coutFixe + this.coutUtilisateurs;

                // Calculer le coût annuel
                this.coutAnnuel = this.coutTotal * 12;

                // Calculer le coût par utilisateur effectif
                this.coutParUtilisateurEffectif = this.coutTotal / this.nombreUtilisateurs;

                // Mettre à jour le forfait actif
                this.forfaitActif = this.forfait;

                // Formater les nombres pour l'affichage
                this.coutFixeFormatted = this.formatNumber(this.coutFixe);
                this.coutUtilisateursFormatted = this.formatNumber(this.coutUtilisateurs);
                this.coutTotalFormatted = this.formatNumber(this.coutTotal);
                this.coutAnnuelFormatted = this.formatNumber(this.coutAnnuel);
                this.coutParUtilisateurEffectifFormatted = this.formatNumber(Math.round(this.coutParUtilisateurEffectif));
                this.economieAnnuelleFormatted = this.formatNumber(Math.abs(Math.round(this.economieAnnuelle)));

                // Afficher les résultats avec une animation
                setTimeout(() => {
                    this.resultatVisible = true;
                    this.loading = false;
                    
                    // Afficher la comparaison si des économies sont possibles
                    this.comparaisonVisible = this.economieAnnuelle !== 0;
                }, 300);

            } catch (err) {
                this.error = "Une erreur s'est produite lors du calcul. Veuillez réessayer.";
                this.loading = false;
                console.error('Erreur de calcul:', err);
            }
        },

        async genererDevis() {
            try {
                const response = await fetch('/generer-devis', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        nombreUtilisateurs: this.nombreUtilisateurs,
                        forfait: this.forfait,
                        coutFixe: this.coutFixe,
                        coutUtilisateurs: this.coutUtilisateurs,
                        coutTotal: this.coutTotal,
                        coutAnnuel: this.coutAnnuel,
                        coutParUtilisateurEffectif: this.coutParUtilisateurEffectif,
                        economieAnnuelle: this.economieAnnuelle,
                        details: {
                            coutParUtilisateur: this.coutParUtilisateur,
                            periodeFacturation: this.periodeFacturation,
                            dateValidite: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                            conditionsPaiement: 'Paiement à 30 jours',
                            notesSupplementaires: 'Prix en FCFA. TVA non applicable.'
                        }
                    })
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Devis-GENIUS WORK-${new Date().toISOString().split('T')[0]}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    throw new Error('Échec de la génération du PDF');
                }
            } catch (err) {
                this.error = "Échec de la génération du devis PDF. Veuillez réessayer.";
                console.error('Erreur PDF:', err);
            }
        }
    }));
});
