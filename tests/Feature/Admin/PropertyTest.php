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
            'province' => 'Pampanga',
            'city' => 'Angeles City',
            'barangay' => 'Cutcut',
            'address_line1' => 'Rizal Avenue',
            'address_line2' => null,
            'description' => 'Waterfront community with covered parking.',
        ];

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.index'))
            ->post(route('admin.properties.store'), $payload);

        $response->assertRedirect(route('admin.properties.index'));
        $this->assertDatabaseHas('properties', [
            'name' => 'Harbor View Residences',
            'province' => 'Pampanga',
            'city' => 'Angeles City',
            'barangay' => 'Cutcut',
            'address_line1' => 'Rizal Avenue',
            'owner_id' => $admin->id,
        ]);
    }

    public function test_admin_cannot_create_property_with_missing_name(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.index'))
            ->post(route('admin.properties.store'), [
                'province' => 'Pampanga',
                'city' => 'Mabalacat City',
                'barangay' => 'Dolores',
                'address_line1' => 'Clark Freeport Zone',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_cannot_create_property_with_missing_address_fields(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.index'))
            ->post(route('admin.properties.store'), [
                'name' => 'Lakeside Condos',
            ]);

        $response->assertSessionHasErrors(['province', 'city', 'barangay', 'address_line1']);
    }

    public function test_admin_can_update_an_existing_property(): void
    {
        $admin = $this->createAdmin();
        $property = $this->createProperty([
            'name' => 'Original Tower Name',
            'province' => 'Pampanga',
            'city' => 'Angeles City',
            'barangay' => 'Pulungbulu',
            'address_line1' => 'Original Street',
        ], $admin);

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.index'))
            ->patch(route('admin.properties.update', $property), [
                'name' => 'Renamed Tower',
                'province' => 'Pampanga',
                'city' => 'City of San Fernando',
                'barangay' => 'San Agustin',
                'address_line1' => 'Updated Avenue',
                'address_line2' => null,
                'description' => 'Renovated lobby and upgraded elevators.',
            ]);

        $response->assertRedirect(route('admin.properties.index'));
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'Renamed Tower',
            'province' => 'Pampanga',
            'city' => 'City of San Fernando',
            'barangay' => 'San Agustin',
            'address_line1' => 'Updated Avenue',
        ]);
    }

    public function test_admin_can_soft_delete_a_property(): void
    {
        $admin = $this->createAdmin();
        $property = $this->createProperty([
            'name' => 'Soft Delete QA Property',
            'province' => 'Tarlac',
            'city' => 'Tarlac City',
            'barangay' => 'Poblacion',
            'address_line1' => '123 QA Street',
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
            'province' => 'Tarlac',
            'city' => 'Capas',
            'barangay' => 'Poblacion',
            'address_line1' => '456 Archive Road',
        ], $admin);

        $this->actingAs($admin)->delete(route('admin.properties.destroy', $property));

        $response = $this->actingAs($admin)->get(route('admin.properties.index'));

        $response->assertOk();
        $response->assertDontSee('Hidden After Delete Plaza');
    }

    public function test_admin_can_update_late_fee_settings(): void
    {
        $admin = $this->createAdmin();
        $property = $this->createProperty([], $admin);

        $response = $this->actingAs($admin)
            ->from(route('admin.properties.show', $property))
            ->patch(route('admin.properties.late-fee.update', $property), [
                'late_fee_type' => 'fixed',
                'late_fee_value' => '25.50',
            ]);

        $response->assertRedirect(route('admin.properties.show', $property));
        $response->assertSessionHas('success', 'Late fee settings updated successfully.');

        $property->refresh();
        $this->assertSame('fixed', $property->late_fee_type);
        $this->assertEquals(25.5, (float) $property->late_fee_value);
    }

    public function test_calculate_late_fee_returns_correct_amount_for_percentage(): void
    {
        $property = new Property([
            'late_fee_type' => 'percentage',
            'late_fee_value' => 10,
        ]);

        $this->assertSame(1000.0, $property->calculateLateFee(10000.0));
    }

    public function test_calculate_late_fee_returns_correct_amount_for_fixed(): void
    {
        $property = new Property([
            'late_fee_type' => 'fixed',
            'late_fee_value' => 350.75,
        ]);

        $this->assertSame(350.75, $property->calculateLateFee(99999.0));
    }

    public function test_calculate_late_fee_returns_zero_when_late_fee_value_is_zero(): void
    {
        $property = new Property([
            'late_fee_type' => 'percentage',
            'late_fee_value' => 0,
        ]);

        $this->assertSame(0.0, $property->calculateLateFee(6500.0));
    }
}
