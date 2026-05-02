<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeasePaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $owner1 = User::updateOrCreate(
            ['email' => 'owner1@test.com'],
            [
                'name' => 'Juan Owner',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $owner2 = User::updateOrCreate(
            ['email' => 'owner2@test.com'],
            [
                'name' => 'Maria Owner',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $ana = User::updateOrCreate(
            ['email' => 'ana@tenant.com'],
            [
                'name' => 'Ana Reyes',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'must_change_password' => true,
            ]
        );

        $pedro = User::updateOrCreate(
            ['email' => 'pedro@tenant.com'],
            [
                'name' => 'Pedro Santos',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'must_change_password' => true,
            ]
        );

        $sunshine = Property::updateOrCreate(
            ['owner_id' => $owner1->id, 'name' => 'Sunshine Apartments'],
            [
                'address' => 'Brgy. Sta. Cruz, Angeles City',
                'description' => null,
            ]
        );

        $greenview = Property::updateOrCreate(
            ['owner_id' => $owner2->id, 'name' => 'Greenview Residences'],
            [
                'address' => 'Brgy. Dolores, Mabalacat',
                'description' => null,
            ]
        );

        $unit101 = Unit::updateOrCreate(
            ['property_id' => $sunshine->id, 'unit_number' => '101'],
            [
                'unit_type' => 'Studio',
                'rent_price' => 6500,
                'status' => 'occupied',
            ]
        );

        Unit::updateOrCreate(
            ['property_id' => $sunshine->id, 'unit_number' => '102'],
            [
                'unit_type' => '1BR',
                'rent_price' => 8500,
                'status' => 'vacant',
            ]
        );

        Unit::updateOrCreate(
            ['property_id' => $sunshine->id, 'unit_number' => '103'],
            [
                'unit_type' => 'Studio',
                'rent_price' => 6500,
                'status' => 'vacant',
            ]
        );

        $unit201 = Unit::updateOrCreate(
            ['property_id' => $greenview->id, 'unit_number' => '201'],
            [
                'unit_type' => 'Studio',
                'rent_price' => 5500,
                'status' => 'occupied',
            ]
        );

        Unit::updateOrCreate(
            ['property_id' => $greenview->id, 'unit_number' => '202'],
            [
                'unit_type' => '1BR',
                'rent_price' => 7500,
                'status' => 'vacant',
            ]
        );

        $tenantAna = Tenant::updateOrCreate(
            ['user_id' => $ana->id],
            [
                'owner_id' => $owner1->id,
                'phone_number' => '09171111111',
                'emergency_contact_name' => null,
                'emergency_contact_number' => null,
                'address' => null,
            ]
        );

        $tenantPedro = Tenant::updateOrCreate(
            ['user_id' => $pedro->id],
            [
                'owner_id' => $owner2->id,
                'phone_number' => '09282222222',
                'emergency_contact_name' => null,
                'emergency_contact_number' => null,
                'address' => null,
            ]
        );

        $leaseAna = Lease::updateOrCreate(
            [
                'tenant_id' => $tenantAna->id,
                'unit_id' => $unit101->id,
                'start_date' => now()->toDateString(),
            ],
            [
                'end_date' => now()->addYear()->toDateString(),
                'monthly_rent' => 6500,
                'deposit_amount' => 13000,
                'deposit_status' => 'held',
                'status' => 'active',
            ]
        );

        $leasePedro = Lease::updateOrCreate(
            [
                'tenant_id' => $tenantPedro->id,
                'unit_id' => $unit201->id,
                'start_date' => now()->toDateString(),
            ],
            [
                'end_date' => now()->addYear()->toDateString(),
                'monthly_rent' => 5500,
                'deposit_amount' => 11000,
                'deposit_status' => 'held',
                'status' => 'active',
            ]
        );

        (new LeasePaymentService)->generatePayments($leaseAna);
        (new LeasePaymentService)->generatePayments($leasePedro);
    }
}
