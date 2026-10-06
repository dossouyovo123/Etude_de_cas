<?php

namespace Tests\Feature;

use App\Models\Demande;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemandeApiTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'npi' => '1234567890',
            'type_acte' => 'acte_naissance',
            'nombre_copies' => 2,
        ], $override);
    }

    public function test_depot_valide_retourne_201_et_statut_deposee(): void
    {
        $this->postJson('/api/demandes', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.statut', 'deposee')
            ->assertJsonStructure(['data' => ['id']]);
    }

    public function test_npi_invalide_est_refuse(): void
    {
        foreach (['123', '12345678901', 'abcdefghij', ''] as $npi) {
            $this->postJson('/api/demandes', $this->payload(['npi' => $npi]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('npi');
        }
    }

    public function test_type_acte_invalide_est_refuse(): void
    {
        $this->postJson('/api/demandes', $this->payload(['type_acte' => 'passeport']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('type_acte');
    }

    public function test_nombre_de_copies_hors_bornes_est_refuse(): void
    {
        foreach ([0, 6, -1] as $n) {
            $this->postJson('/api/demandes', $this->payload(['nombre_copies' => $n]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('nombre_copies');
        }
    }

    public function test_cycle_de_vie_complet_jusqu_a_validation(): void
    {
        $id = $this->postJson('/api/demandes', $this->payload())->json('data.id');

        $this->patchJson("/api/demandes/$id/statut", ['statut' => 'en_cours'])
            ->assertOk()->assertJsonPath('data.statut', 'en_cours');

        $this->patchJson("/api/demandes/$id/statut", ['statut' => 'validee'])
            ->assertOk()->assertJsonPath('data.statut', 'validee');
    }

    public function test_saut_d_etape_est_refuse(): void
    {
        $id = $this->postJson('/api/demandes', $this->payload())->json('data.id');

        $this->patchJson("/api/demandes/$id/statut", ['statut' => 'validee'])
            ->assertStatus(409);
    }

    public function test_rejet_sans_motif_est_refuse(): void
    {
        $id = $this->postJson('/api/demandes', $this->payload())->json('data.id');
        $this->patchJson("/api/demandes/$id/statut", ['statut' => 'en_cours']);

        $this->patchJson("/api/demandes/$id/statut", ['statut' => 'rejetee'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('motif');

        $this->patchJson("/api/demandes/$id/statut", ['statut' => 'rejetee', 'motif' => 'Pièces manquantes'])
            ->assertOk()->assertJsonPath('data.motif_rejet', 'Pièces manquantes');
    }

    public function test_un_statut_final_ne_peut_plus_changer(): void
    {
        $id = $this->postJson('/api/demandes', $this->payload())->json('data.id');
        $this->patchJson("/api/demandes/$id/statut", ['statut' => 'en_cours']);
        $this->patchJson("/api/demandes/$id/statut", ['statut' => 'validee']);

        $this->patchJson("/api/demandes/$id/statut", ['statut' => 'en_cours'])
            ->assertStatus(409);
    }

    public function test_liste_usager_triee_du_plus_recent_et_filtrable(): void
    {
        $ancienne = Demande::create($this->payload() + ['statut' => 'deposee', 'created_at' => now()->subDays(2)]);
        $recente = Demande::create($this->payload(['type_acte' => 'casier_judiciaire']) + ['statut' => 'en_cours', 'created_at' => now()]);
        Demande::create($this->payload(['npi' => '0000000000']) + ['statut' => 'deposee']);

        $ids = collect($this->getJson('/api/usagers/1234567890/demandes')->assertOk()->json('data'))->pluck('id');
        $this->assertEquals([$recente->id, $ancienne->id], $ids->all());

        $filtre = $this->getJson('/api/usagers/1234567890/demandes?statut=deposee')->json('data');
        $this->assertCount(1, $filtre);
    }

    public function test_pagination_limitee_a_20(): void
    {
        for ($i = 0; $i < 25; $i++) {
            Demande::create($this->payload() + ['statut' => 'deposee']);
        }

        $this->getJson('/api/usagers/1234567890/demandes')
            ->assertJsonCount(20, 'data');
    }

    public function test_statistiques_par_statut(): void
    {
        Demande::create($this->payload() + ['statut' => 'deposee']);
        Demande::create($this->payload() + ['statut' => 'deposee']);
        Demande::create($this->payload() + ['statut' => 'validee']);

        $this->getJson('/api/demandes/statistiques')
            ->assertOk()
            ->assertJsonPath('data.deposee', 2)
            ->assertJsonPath('data.validee', 1)
            ->assertJsonPath('data.rejetee', 0);
    }
}