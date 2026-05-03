<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\OwnerTerms;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\TenantTermAgreement;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeasePaymentService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            foreach ([
                'tenant_term_agreements',
                'owner_terms',
                'notifications',
                'payments',
                'leases',
                'unit_images',
                'property_images',
                'units',
                'tenants',
                'properties',
                'users',
            ] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $owner = User::create([
            'name' => 'Juan dela Cruz',
            'email' => 'owner@renttrack.com',
            'password' => 'password',
            'role' => 'admin',
            'must_change_password' => false,
        ]);

        $ownerTerms = OwnerTerms::create([
            'owner_id' => $owner->id,
            'version' => 1,
            'content' => "RENTAL TERMS AND CONDITIONS\n\n".
                "1. RENT PAYMENT\nMonthly rent is due on the date specified in your lease agreement. ".
                "Failure to pay on time will result in a late payment fee.\n\n".
                "2. LATE PAYMENT POLICY\nA late fee will be applied to any payment not received by the due date. ".
                "The late fee is calculated based on your property's policy and will be shown in your payment breakdown.\n\n".
                "3. PROPERTY CARE AND MAINTENANCE\nTenants are responsible for maintaining the unit in good condition. ".
                "Any damage beyond normal wear and tear will be charged to the tenant.\n\n".
                "4. SECURITY DEPOSIT\nThe security deposit will be held for the duration of the lease. ".
                "It will be returned within 30 days after move-out, minus any deductions.\n\n".
                "5. PROHIBITED ACTIVITIES\n- No illegal activities on the premises\n".
                "- No unauthorized pets unless specified in lease\n".
                "- No subletting without written permission\n".
                "- No structural modifications to the unit\n\n".
                "6. MOVE-OUT POLICY\nTenant must give at least 30 days written notice before move-out. ".
                "The unit must be returned in the same condition as move-in.\n\n".
                "7. ACKNOWLEDGMENT\nBy agreeing to these terms, you confirm that you have read, ".
                'understood, and agree to all conditions stated above.',
        ]);

        $propertyData = require __DIR__.'/property_seed_data.php';

        $unitTypes = ['Studio', '1BR', '2BR', '3BR'];
        $rentPrices = [4000, 4500, 5000, 5500, 6000, 6500, 7000, 7500, 8000, 8500, 9000, 10000, 12000, 15000];
        $lateFeeValues = [3, 4, 5, 6, 7, 8, 10];

        $properties = [];
        foreach ($propertyData as $index => $data) {
            $properties[] = Property::create([
                'owner_id' => $owner->id,
                'name' => $data[0],
                'province' => $data[1],
                'city' => $data[2],
                'barangay' => $data[3],
                'address_line1' => $data[4],
                'address_line2' => null,
                'description' => $data[5],
                'late_fee_type' => 'percentage',
                'late_fee_value' => $lateFeeValues[$index % count($lateFeeValues)],
            ]);
        }

        $units = [];
        foreach ($properties as $property) {
            for ($u = 1; $u <= 2; $u++) {
                $unitType = $unitTypes[array_rand($unitTypes)];
                $rentPrice = $rentPrices[array_rand($rentPrices)];
                $units[] = Unit::create([
                    'property_id' => $property->id,
                    'unit_number' => (string) (($property->id * 100) + $u),
                    'unit_type' => $unitType,
                    'rent_price' => $rentPrice,
                    'status' => 'vacant',
                ]);
            }
        }

        $tenantNames = [
            'Ana Reyes', 'Maria Santos', 'Pedro Garcia', 'Jose Dela Cruz',
            'Rosa Mendoza', 'Juan Ramos', 'Elena Castro', 'Carlos Torres',
            'Luz Flores', 'Miguel Bautista', 'Carmen Aquino', 'Ramon Villanueva',
            'Pilar Magno', 'Eduardo Salazar', 'Gloria Pascual', 'Antonio Navarro',
            'Esperanza Lim', 'Francisco Aguilar', 'Remedios Cruz', 'Alberto Morales',
            'Teresita Rojas', 'Emmanuel Delos Santos', 'Corazon Reyes', 'Rodolfo Manalo',
            'Felicitas Ocampo', 'Domingo Santiago', 'Milagros Fernandez', 'Renato Lagman',
            'Estrella Buenaventura', 'Alfredo Soriano', 'Natividad Villareal', 'Ernesto Padilla',
            'Concepcion Dimayuga', 'Gregorio Malabanan', 'Adoracion Macapagal', 'Virgilio Tolentino',
            'Resurreccion Dizon', 'Celestino Pangilinan', 'Asuncion Villafuerte', 'Herminio Castillo',
            'Purificacion Dela Rosa', 'Bartolome Magtoto', 'Encarnacion Esguerra', 'Liberato Guevara',
            'Presentacion Mercado', 'Cipriano Valenzuela', 'Tranquilino Bulatao', 'Felicidad Pineda',
            'Maximino Sison', 'Benedicta Macaraig',
        ];

        $tenantModels = [];

        $tenantCities = collect(['Angeles City', 'Mabalacat City', 'City of San Fernando', 'Porac', 'Mexico']);
        $tenantBarangays = collect(['Poblacion', 'Sto. Domingo', 'Dolores', 'Balibago', 'Sindalan', 'Malabanias', 'Cutcut', 'Friendship']);

        foreach ($tenantNames as $i => $name) {
            $email = 'tenant'.($i + 1).'@renttrack.com';
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => 'password',
                'role' => 'tenant',
                'must_change_password' => true,
            ]);

            $tenantModels[] = Tenant::create([
                'user_id' => $user->id,
                'owner_id' => $owner->id,
                'phone_number' => '09'.rand(100000000, 999999999),
                'emergency_contact_name' => 'Emergency Contact '.($i + 1),
                'emergency_contact_number' => '09'.rand(100000000, 999999999),
                'province' => 'Pampanga',
                'city' => $tenantCities->random(),
                'barangay' => $tenantBarangays->random(),
                'address_line1' => ($i + 1).' Sample Street',
                'address_line2' => null,
            ]);
        }

        $inclusions = ['Water', 'Electricity', 'Internet/WiFi', 'Parking', 'Cable TV', 'Trash Collection'];
        $leaseService = new LeasePaymentService;

        for ($i = 0; $i < 40; $i++) {
            $unit = $units[$i];
            $tenant = $tenantModels[$i];

            $monthsAgo = rand(1, 8);
            $startDate = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $endDate = $startDate->copy()->addYear();

            $selectedInclusions = array_slice($inclusions, 0, rand(2, 4));

            $notes = $i % 3 === 0
                ? 'Tenant is allowed to have one small pet. Parking slot #'.($i + 1).' is included.'
                : null;

            $lease = Lease::create([
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'monthly_rent' => $unit->rent_price,
                'deposit_amount' => $unit->rent_price * 2,
                'deposit_status' => 'held',
                'status' => 'active',
                'inclusions' => $selectedInclusions,
                'notes' => $notes,
            ]);

            $unit->update(['status' => 'occupied']);

            $leaseService->generatePayments($lease);

            $pastPayments = $lease->payments()
                ->where('due_date', '<', now()->toDateString())
                ->orderBy('due_date')
                ->get();

            foreach ($pastPayments as $pIndex => $payment) {
                if ($pIndex < $pastPayments->count() - 1) {
                    $payment->update([
                        'status' => 'paid',
                        'payment_date' => $payment->due_date->copy()->subDays(rand(1, 5))->format('Y-m-d'),
                        'payment_method' => collect(['gcash', 'cash', 'bank_transfer', 'maya'])->random(),
                        'verified_at' => now()->subDays(rand(1, 3)),
                        'late_fee_amount' => 0,
                        'total_amount_due' => $payment->amount_paid,
                    ]);
                } else {
                    if ($i % 4 === 0) {
                        $payment->update([
                            'due_date' => now()->subDays(rand(1, 10))->format('Y-m-d'),
                        ]);
                    } elseif ($i % 4 === 1) {
                        $payment->update([
                            'status' => 'verifying',
                            'payment_date' => now()->subDays(rand(1, 3))->format('Y-m-d'),
                            'payment_method' => collect(['gcash', 'cash', 'bank_transfer', 'maya'])->random(),
                            'proof_of_payment' => null,
                            'total_amount_due' => $payment->amount_paid,
                        ]);
                    }
                }
            }

            if ($i % 5 !== 0) {
                TenantTermAgreement::create([
                    'tenant_id' => $tenant->id,
                    'owner_id' => $owner->id,
                    'version' => $ownerTerms->version,
                    'agreed_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        for ($i = 40; $i < 45; $i++) {
            $unit = $units[$i];
            $tenant = $tenantModels[$i];

            $startDate = Carbon::now()->subMonths(14);
            $endDate = Carbon::now()->subMonths(2);

            $lease = Lease::create([
                'tenant_id' => $tenant->id,
                'unit_id' => $unit->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'monthly_rent' => $unit->rent_price,
                'deposit_amount' => $unit->rent_price * 2,
                'deposit_status' => 'returned',
                'status' => 'completed',
                'inclusions' => ['Water', 'Electricity'],
                'notes' => null,
            ]);

            $leaseService->generatePayments($lease);

            $lease->payments()->update([
                'status' => 'paid',
                'payment_date' => now()->subMonths(1)->format('Y-m-d'),
                'payment_method' => 'gcash',
                'verified_at' => now()->subMonths(1),
                'late_fee_amount' => 0,
                'total_amount_due' => DB::raw('amount_paid'),
            ]);

            TenantTermAgreement::create([
                'tenant_id' => $tenant->id,
                'owner_id' => $owner->id,
                'version' => $ownerTerms->version,
                'agreed_at' => $startDate->copy()->addDay(),
            ]);
        }

        DB::table('notifications')->insert([
            [
                'user_id' => $owner->id,
                'type' => 'payment_submitted',
                'data' => json_encode([
                    'message' => 'Ana Reyes submitted proof of payment for Unit '.$units[0]->unit_number,
                    'url' => '/admin/payments/1',
                ]),
                'read_at' => null,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'user_id' => $owner->id,
                'type' => 'payment_late_owner',
                'data' => json_encode([
                    'message' => 'Pedro Garcia has a late payment for Unit '.$units[8]->unit_number,
                    'url' => '/admin/payments/2',
                ]),
                'read_at' => null,
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
        ]);
    }
}
