<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteResource\Pages\ListSites;
use App\Filament\Resources\SiteResource\Pages\ViewSite;
use App\Models\Entreprise;
use App\Models\Site;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class KioskVideoTest extends TestCase
{
    use RefreshDatabase;

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Site::DISQUE_VIDEO_KIOSQUE);

        $entreprise = Entreprise::create(['nom' => 'Entreprise Test', 'email' => 'test@test.local']);
        $this->site = new Site(['entreprise_id' => $entreprise->id, 'nom' => 'Siège', 'statut' => 'actif']);
        $this->site->kiosk_token = 'token-kiosque-test';
        $this->site->save();
    }

    public function test_sans_video_personnalisee_le_kiosque_utilise_la_video_par_defaut(): void
    {
        $this->assertStringEndsWith('/videos/video.mp4', $this->site->kioskVideoUrl());

        $this->get(route('kiosk.index', $this->site->kiosk_token))
            ->assertOk()
            ->assertSee('videos/video.mp4', false);
    }

    public function test_le_kiosque_diffuse_la_video_du_site_et_la_signale_a_la_page(): void
    {
        $this->site->update(['kiosk_video' => 'kiosk/videos/pub.mp4']);

        $this->get(route('kiosk.index', $this->site->kiosk_token))->assertOk()->assertSee('/storage/kiosk/videos/pub.mp4', false);

        $this->getJson(route('kiosk.video', $this->site->kiosk_token))
            ->assertOk()
            ->assertJson(['url' => '/storage/kiosk/videos/pub.mp4'])
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->getJson(route('kiosk.video', 'inconnu'))->assertNotFound();
    }

    public function test_remplacer_ou_retirer_la_video_supprime_l_ancien_fichier(): void
    {
        Storage::disk(Site::DISQUE_VIDEO_KIOSQUE)->put('kiosk/videos/ancienne.mp4', 'x');
        $this->site->update(['kiosk_video' => 'kiosk/videos/ancienne.mp4']);

        Storage::disk(Site::DISQUE_VIDEO_KIOSQUE)->put('kiosk/videos/nouvelle.mp4', 'y');
        $this->site->update(['kiosk_video' => 'kiosk/videos/nouvelle.mp4']);
        Storage::disk(Site::DISQUE_VIDEO_KIOSQUE)->assertMissing('kiosk/videos/ancienne.mp4');
        Storage::disk(Site::DISQUE_VIDEO_KIOSQUE)->assertExists('kiosk/videos/nouvelle.mp4');

        $this->site->update(['kiosk_video' => null]);
        Storage::disk(Site::DISQUE_VIDEO_KIOSQUE)->assertMissing('kiosk/videos/nouvelle.mp4');
        $this->assertStringEndsWith('/videos/video.mp4', $this->site->fresh()->kioskVideoUrl());
    }

    public function test_le_tableau_de_bord_permet_d_envoyer_puis_de_retirer_la_video(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'statut' => 'actif']));

        Livewire::test(ListSites::class)
            ->assertTableActionVisible('videoKiosque', $this->site)
            ->assertTableActionHidden('videoKiosqueParDefaut', $this->site)
            ->callTableAction('videoKiosque', $this->site, ['kiosk_video' => UploadedFile::fake()->create('pub.mp4', 2048, 'video/mp4')])
            ->assertHasNoTableActionErrors();

        $chemin = $this->site->fresh()->kiosk_video;
        $this->assertNotNull($chemin);
        Storage::disk(Site::DISQUE_VIDEO_KIOSQUE)->assertExists($chemin);

        Livewire::test(ViewSite::class, ['record' => $this->site->getRouteKey()])
            ->assertActionVisible('videoKiosqueParDefaut')
            ->callAction('videoKiosqueParDefaut');

        $this->assertNull($this->site->fresh()->kiosk_video);
        Storage::disk(Site::DISQUE_VIDEO_KIOSQUE)->assertMissing($chemin);
    }

    public function test_un_fichier_qui_n_est_pas_une_video_est_refuse(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'statut' => 'actif']));

        Livewire::test(ListSites::class)
            ->callTableAction('videoKiosque', $this->site, ['kiosk_video' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])
            ->assertHasTableActionErrors(['kiosk_video']);

        $this->assertNull($this->site->fresh()->kiosk_video);
    }
}
