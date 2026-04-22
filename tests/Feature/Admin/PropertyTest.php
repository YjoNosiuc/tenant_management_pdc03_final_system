<?php

namespace Tests\Feature\Admin;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class PropertyTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_admin_can_view_properties_index_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.properties.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_a_property_with_valid_data(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Harbor View Residences',
            'address' => 'Rizal Avenue, Angeles City, Pampanga',
            'description' => 'Waterfront community with covered parking.',
        ];

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.index'))
            ->post(route('admin.properties.store'), $payload);

        $response->assertRedirect(route('admin.properties.index'));
        $this->assertDatabaseHas('properties', [
            'name' => 'Harbor View Residences',
            'address' => 'Rizal Avenue, Angeles City, Pampanga',
            'owner_id' => $admin->id,
        ]);
    }

    public function test_admin_cannot_create_property_with_missing_name(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.index'))
            ->post(route('admin.properties.store'), [
                'address' => 'Clark Freeport Zone, Mabalacat, Pampanga',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_cannot_create_property_with_missing_address(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.index'))
            ->post(route('admin.properties.store'), [
                'name' => 'Lakeside Condos',
            ]);

        $response->assertSessionHasErrors('address');
    }

    public function test_admin_can_update_an_existing_property(): void
    {
        $admin = $this->createAdmin();
        $property = $this->createProperty([
            'name' => 'Original Tower Name',
            'address' => 'Original Street, City',
        ], $admin);

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.index'))
            ->patch(route('admin.properties.update', $property), [
                'name' => 'Renamed Tower',
                'address' => 'Updated Avenue, San Fernando, Pampanga',
                'description' => 'Renovated lobby and upgraded elevators.',
            ]);

        $response->assertRedirect(route('admin.properties.index'));
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'Renamed Tower',
            'address' => 'Updated Avenue, San Fernando, Pampanga',
        ]);
    }

    public function test_admin_can_soft_delete_a_property(): void
    {
        $admin = $this->createAdmin();
        $property = $this->createProperty([
            'name' => 'Soft Delete QA Property',
            'address' => '123 QA Street, Tarlac City, Tarlac',
        ], $admin);

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.index'))
            ->delete(route('admin.properties.destroy', $property));

        $response->assertRedirect(route('admin.properties.index'));
        $this->assertSoftDeleted('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_soft_deleted_property_does_not_appear_in_index(): void
    {
        $admin = $this->createAdmin();
        $property = $this->createProperty([
            'name' => 'Hidden After Delete Plaza',
            'address' => '456 Archive Road, Capas, Tarlac',
        ], $admin);

        $this->actingAs($admin)->delete(route('admin.properties.destroy', $property));

        $response = $this->actingAs($admin)->get(route('admin.properties.index'));

        $response->assertOk();
        $response->assertDontSee('Hidden After Delete Plaza');
    }
}
