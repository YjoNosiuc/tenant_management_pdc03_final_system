<?php

namespace Tests\Feature\Admin;

use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class UnitTest extends TestCase
{
    use CreatesTestData;
    use RefreshDatabase;

    public function test_admin_can_view_units_index_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.units.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_a_unit_with_valid_data(): void
    {
        $admin = $this->createAdmin();
        $property = $this->createProperty([], $admin);

        $payload = [
            'property_id' => $property->id,
            'unit_number' => 'PH-A',
            'unit_type' => 'Penthouse',
            'rent_price' => 32500.75,
            'status' => 'vacant',
        ];

        $response = $this->actingAs($admin)
            ->from(route('admin.units.index'))
            ->post(route('admin.units.store'), $payload);

        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseHas('units', [
            'property_id' => $property->id,
            'unit_number' => 'PH-A',
            'unit_type' => 'Penthouse',
            'status' => 'vacant',
        ]);
    }

    public function test_admin_cannot_create_unit_with_missing_required_fields(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->from(route('admin.units.index'))
            ->post(route('admin.units.store'), []);

        $response->assertSessionHasErrors([
            'property_id',
            'unit_number',
            'unit_type',
            'rent_price',
            'status',
        ]);
    }

    public function test_admin_can_update_a_unit(): void
    {
        $admin = $this->createAdmin();
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id, 'vacant', [
            'unit_number' => '201',
            'unit_type' => 'One-bedroom',
            'rent_price' => 14000.00,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.units.index'))
            ->patch(route('admin.units.update', $unit), [
                'property_id' => $property->id,
                'unit_number' => '201A',
                'unit_type' => 'One-bedroom deluxe',
                'rent_price' => 15250.00,
                'status' => 'occupied',
            ]);

        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'unit_number' => '201A',
            'unit_type' => 'One-bedroom deluxe',
            'rent_price' => 15250.00,
            'status' => 'occupied',
        ]);
    }

    public function test_admin_can_soft_delete_a_unit(): void
    {
        $admin = $this->createAdmin();
        $property = $this->createProperty([], $admin);
        $unit = $this->createUnit($property->id);

        $response = $this->actingAs($admin)
            ->from(route('admin.units.index'))
            ->delete(route('admin.units.destroy', $unit));

        $response->assertRedirect(route('admin.units.index'));
        $this->assertSoftDeleted('units', [
            'id' => $unit->id,
        ]);
    }

    public function test_unit_status_starts_as_vacant_by_default(): void
    {
        $property = $this->createProperty();

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => '305',
            'unit_type' => 'Studio',
            'rent_price' => 9800.00,
        ]);

        $this->assertSame('vacant', $unit->fresh()->status);
    }
}
