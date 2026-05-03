<?php

namespace Tests\Feature\Admin;

use App\Models\OwnerTerms;
use App\Models\TenantTermAgreement;
use App\Support\DefaultRentalTerms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class SettingsTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_admin_can_view_settings_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('Settings', false);
        $response->assertSee('Terms & Conditions', false);
    }

    public function test_admin_can_create_terms_and_conditions(): void
    {
        $admin = $this->createAdmin();
        $content = str_repeat('A', 120);

        $response = $this->actingAs($admin)
            ->from(route('admin.settings.index'))
            ->patch(route('admin.settings.terms.update'), [
                'content' => $content,
            ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('owner_terms', [
            'owner_id' => $admin->id,
            'version' => 1,
        ]);

        $this->assertSame($content, OwnerTerms::where('owner_id', $admin->id)->value('content'));
    }

    public function test_admin_can_update_terms_and_version_increments(): void
    {
        $admin = $this->createAdmin();
        $first = str_repeat('B', 120);
        $second = str_repeat('C', 120);

        $this->actingAs($admin)->patch(route('admin.settings.terms.update'), [
            'content' => $first,
        ]);

        $this->actingAs($admin)->patch(route('admin.settings.terms.update'), [
            'content' => $second,
        ]);

        $terms = OwnerTerms::where('owner_id', $admin->id)->first();
        $this->assertNotNull($terms);
        $this->assertSame(2, $terms->version);
        $this->assertSame($second, $terms->content);
    }

    public function test_updating_terms_requires_tenants_to_re_agree(): void
    {
        $admin = $this->createAdmin();
        $tenantUser = $this->createTenantUser([], [], $admin);
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'occupied');
        $this->createLease($tenantUser->tenant->id, $unit->id, 'active');

        $this->actingAs($admin)->patch(route('admin.settings.terms.update'), [
            'content' => str_repeat('D', 120),
        ]);

        TenantTermAgreement::create([
            'tenant_id' => $tenantUser->tenant->id,
            'owner_id' => $admin->id,
            'version' => 1,
            'agreed_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)->patch(route('admin.settings.terms.update'), [
            'content' => str_repeat('E', 120),
        ]);

        $this->actingAs($tenantUser)->get(route('tenant.dashboard'))->assertRedirect(route('terms.show'));
    }

    public function test_agreed_count_shows_correct_number_for_current_version(): void
    {
        $admin = $this->createAdmin();
        $content = trim(DefaultRentalTerms::content());

        $this->actingAs($admin)->patch(route('admin.settings.terms.update'), [
            'content' => $content,
        ]);

        $terms = OwnerTerms::where('owner_id', $admin->id)->first();
        $this->assertNotNull($terms);

        $tenantA = $this->createTenantUser(['name' => 'Tenant A'], [], $admin);
        $tenantB = $this->createTenantUser(['name' => 'Tenant B'], [], $admin);

        TenantTermAgreement::create([
            'tenant_id' => $tenantA->tenant->id,
            'owner_id' => $admin->id,
            'version' => $terms->version,
            'agreed_at' => now(),
        ]);
        TenantTermAgreement::create([
            'tenant_id' => $tenantB->tenant->id,
            'owner_id' => $admin->id,
            'version' => $terms->version,
            'agreed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertViewHas('agreedCount', 2);
    }
}
