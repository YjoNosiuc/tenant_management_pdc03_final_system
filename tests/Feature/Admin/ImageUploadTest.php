<?php

namespace Tests\Feature\Admin;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Unit;
use App\Models\UnitImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private function createOwner(): User
    {
        return User::create([
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);
    }

    private function createProperty(User $owner): Property
    {
        return Property::create([
            'owner_id' => $owner->id,
            'name' => 'Test Property',
            'province' => 'Pampanga',
            'city' => 'Angeles City',
            'barangay' => 'Sto. Domingo',
            'address_line1' => '123 Test Street',
            'description' => 'Test',
            'late_fee_type' => 'percentage',
            'late_fee_value' => 5,
        ]);
    }

    private function createUnit(Property $property): Unit
    {
        return Unit::create([
            'property_id' => $property->id,
            'unit_number' => '101',
            'unit_type' => 'Studio',
            'rent_price' => 6500,
            'status' => 'vacant',
        ]);
    }

    // =====================
    // PROPERTY IMAGES
    // =====================

    public function test_owner_can_upload_property_images(): void
    {
        Storage::fake('public');
        $owner = $this->createOwner();
        $property = $this->createProperty($owner);

        $response = $this->actingAs($owner)
            ->from(route('admin.properties.show', $property))
            ->post(route('admin.properties.images.upload', $property), [
                'images' => [
                    UploadedFile::fake()->image('photo1.jpg'),
                    UploadedFile::fake()->image('photo2.jpg'),
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $property->refresh();
        $this->assertEquals(2, $property->images()->count());
    }

    public function test_owner_cannot_upload_more_than_5_property_images(): void
    {
        Storage::fake('public');
        $owner = $this->createOwner();
        $property = $this->createProperty($owner);

        for ($i = 0; $i < 4; $i++) {
            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => 'properties/test'.$i.'.jpg',
                'order' => $i,
            ]);
        }

        $response = $this->actingAs($owner)
            ->from(route('admin.properties.show', $property))
            ->post(route('admin.properties.images.upload', $property), [
                'images' => [
                    UploadedFile::fake()->image('photo5.jpg'),
                    UploadedFile::fake()->image('photo6.jpg'),
                ],
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(4, $property->fresh()->images()->count());
    }

    public function test_owner_can_delete_a_property_image(): void
    {
        Storage::fake('public');
        $owner = $this->createOwner();
        $property = $this->createProperty($owner);

        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image_path' => 'properties/test.jpg',
            'order' => 0,
        ]);

        $response = $this->actingAs($owner)
            ->from(route('admin.properties.show', $property))
            ->delete(route('admin.properties.images.delete', [$property, $image]));

        $response->assertRedirect();
        $this->assertEquals(0, $property->fresh()->images()->count());
    }

    public function test_owner_cannot_upload_non_image_files_to_property(): void
    {
        Storage::fake('public');
        $owner = $this->createOwner();
        $property = $this->createProperty($owner);

        $response = $this->actingAs($owner)
            ->from(route('admin.properties.show', $property))
            ->post(route('admin.properties.images.upload', $property), [
                'images' => [
                    UploadedFile::fake()->create('document.pdf', 100),
                ],
            ]);

        $response->assertSessionHasErrors('images.0');
        $this->assertEquals(0, $property->fresh()->images()->count());
    }

    public function test_owner_cannot_upload_images_to_another_owners_property(): void
    {
        Storage::fake('public');
        $owner1 = $this->createOwner();
        $owner2 = User::create([
            'name' => 'Owner 2',
            'email' => 'owner2@test.com',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $property2 = $this->createProperty($owner2);

        $response = $this->actingAs($owner1)
            ->post(route('admin.properties.images.upload', $property2), [
                'images' => [UploadedFile::fake()->image('photo.jpg')],
            ]);

        $response->assertStatus(403);
        $this->assertEquals(0, $property2->fresh()->images()->count());
    }

    public function test_owner_cannot_delete_another_owners_property_image(): void
    {
        Storage::fake('public');
        $owner1 = $this->createOwner();
        $owner2 = User::create([
            'name' => 'Owner 2',
            'email' => 'owner2@test.com',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $property2 = $this->createProperty($owner2);
        $image = PropertyImage::create([
            'property_id' => $property2->id,
            'image_path' => 'properties/test.jpg',
            'order' => 0,
        ]);

        $response = $this->actingAs($owner1)
            ->delete(route('admin.properties.images.delete', [$property2, $image]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('property_images', ['id' => $image->id]);
    }

    // =====================
    // UNIT IMAGES
    // =====================

    public function test_owner_can_upload_unit_images(): void
    {
        Storage::fake('public');
        $owner = $this->createOwner();
        $property = $this->createProperty($owner);
        $unit = $this->createUnit($property);

        $response = $this->actingAs($owner)
            ->from(route('admin.units.show', $unit))
            ->post(route('admin.units.images.upload', $unit), [
                'images' => [
                    UploadedFile::fake()->image('unit1.jpg'),
                    UploadedFile::fake()->image('unit2.jpg'),
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $unit->refresh();
        $this->assertEquals(2, $unit->images()->count());
    }

    public function test_owner_cannot_upload_more_than_5_unit_images(): void
    {
        Storage::fake('public');
        $owner = $this->createOwner();
        $property = $this->createProperty($owner);
        $unit = $this->createUnit($property);

        for ($i = 0; $i < 5; $i++) {
            UnitImage::create([
                'unit_id' => $unit->id,
                'image_path' => 'units/test'.$i.'.jpg',
                'order' => $i,
            ]);
        }

        $response = $this->actingAs($owner)
            ->from(route('admin.units.show', $unit))
            ->post(route('admin.units.images.upload', $unit), [
                'images' => [UploadedFile::fake()->image('extra.jpg')],
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(5, $unit->fresh()->images()->count());
    }

    public function test_owner_can_delete_a_unit_image(): void
    {
        Storage::fake('public');
        $owner = $this->createOwner();
        $property = $this->createProperty($owner);
        $unit = $this->createUnit($property);

        $image = UnitImage::create([
            'unit_id' => $unit->id,
            'image_path' => 'units/test.jpg',
            'order' => 0,
        ]);

        $response = $this->actingAs($owner)
            ->from(route('admin.units.show', $unit))
            ->delete(route('admin.units.images.delete', [$unit, $image]));

        $response->assertRedirect();
        $this->assertEquals(0, $unit->fresh()->images()->count());
    }
}
