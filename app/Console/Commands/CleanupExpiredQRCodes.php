<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QRCodeService;

class CleanupExpiredQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qrcode:cleanup {--dry-run : Afficher les QR codes qui seraient supprimés sans les supprimer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoie les QR codes expirés des sites';

    /**
     * QR Code Service
     *
     * @var QRCodeService
     */
    protected $qrCodeService;

    /**
     * Create a new command instance.
     *
     * @param QRCodeService $qrCodeService
     */
    public function __construct(QRCodeService $qrCodeService)
    {
        parent::__construct();
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Démarrage du nettoyage des QR codes expirés...');
        
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('⚠️  Mode simulation activé - Aucune suppression ne sera effectuée');
        }

        try {
            // Récupérer les sites avec des QR codes expirés
            $expiredSites = \App\Models\Site::whereNotNull('qr_token')
                ->whereNotNull('qr_generated_at')
                ->where('qr_generated_at', '<', \Carbon\Carbon::now()->subHours(24))
                ->get();

            if ($expiredSites->isEmpty()) {
                $this->info('✅ Aucun QR code expiré trouvé.');
                return Command::SUCCESS;
            }

            $this->info("📊 {$expiredSites->count()} QR code(s) expiré(s) trouvé(s):");
            
            // Afficher les détails des QR codes expirés
            $headers = ['Site ID', 'Nom du site', 'Généré le', 'Expiré depuis'];
            $rows = [];
            
            foreach ($expiredSites as $site) {
                $generatedAt = $site->qr_generated_at;
                $expiredSince = $generatedAt->addHours(24)->diffForHumans();
                
                $rows[] = [
                    $site->id,
                    $site->nom,
                    $generatedAt->format('d/m/Y H:i'),
                    $expiredSince
                ];
            }
            
            $this->table($headers, $rows);

            if ($isDryRun) {
                $this->info('🔍 Mode simulation - Ces QR codes seraient supprimés en mode normal.');
                return Command::SUCCESS;
            }

            // Demander confirmation
            if (!$this->confirm('Voulez-vous vraiment supprimer ces QR codes expirés ?')) {
                $this->info('❌ Opération annulée.');
                return Command::SUCCESS;
            }

            // Effectuer le nettoyage
            $cleanedCount = $this->qrCodeService->cleanupExpiredQRCodes();

            if ($cleanedCount > 0) {
                $this->info("✅ {$cleanedCount} QR code(s) expiré(s) supprimé(s) avec succès.");
                
                // Log de l'opération
                \Illuminate\Support\Facades\Log::info('QR codes expirés nettoyés', [
                    'cleaned_count' => $cleanedCount,
                    'command' => 'qrcode:cleanup',
                    'executed_by' => 'console'
                ]);
            } else {
                $this->warn('⚠️  Aucun QR code n\'a pu être supprimé.');
            }

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du nettoyage: ' . $e->getMessage());
            
            \Illuminate\Support\Facades\Log::error('Erreur lors du nettoyage des QR codes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
