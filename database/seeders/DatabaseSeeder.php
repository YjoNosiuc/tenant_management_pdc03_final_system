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

        $propertyData = [
            ['Sunshine Apartments', 'Brgy. Sto. Domingo, Angeles City, Pampanga', 'A well-maintained apartment complex near SM Clark with 24/7 security and CCTV surveillance.'],
            ['Greenview Residences', 'Brgy. Dolores, Mabalacat, Pampanga', 'Affordable residential units in a quiet neighborhood perfect for young professionals and families.'],
            ['Clark Heights', 'Brgy. Malabanias, Angeles City, Pampanga', 'Modern living spaces near Clark Freeport Zone with easy access to commercial areas.'],
            ['Villa San Fernando', 'Brgy. Sindalan, San Fernando, Pampanga', 'Comfortable units in the heart of San Fernando city with nearby schools and markets.'],
            ['Pampanga Garden Suites', 'Brgy. Telabastagan, San Fernando, Pampanga', 'Garden-themed residential complex with landscaped areas and covered parking.'],
            ['Metro Angeles Flats', 'Brgy. Pulungbulu, Angeles City, Pampanga', 'Convenient urban living near transportation hubs and commercial centers.'],
            ['Sunset View Residences', 'Brgy. Sapang Bato, Angeles City, Pampanga', 'Peaceful residential community with beautiful sunset views and cool breeze.'],
            ['Clark Business Suites', 'Brgy. Balibago, Angeles City, Pampanga', 'Perfect for business travelers and professionals working in the Clark area.'],
            ['Pampanga Homes', 'Brgy. Del Pilar, San Fernando, Pampanga', 'Family-friendly residential complex with playground and community area.'],
            ['Angeles Garden Villas', 'Brgy. Lourdes Sur, Angeles City, Pampanga', 'Premium garden villas with spacious units and well-maintained surroundings.'],
            ['SM Clark Residences', 'Brgy. Ninoy Aquino, Angeles City, Pampanga', 'Walking distance to SM Clark and other major commercial establishments.'],
            ['Mabalacat Suites', 'Brgy. Poblacion, Mabalacat, Pampanga', 'Centrally located suites in Mabalacat with easy access to NLEX and main roads.'],
            ['Pampanga Heritage Homes', 'Brgy. San Nicolas, San Fernando, Pampanga', 'Heritage-inspired residential complex with modern amenities and facilities.'],
            ['Clark Investors Hub', 'Brgy. Margot, Angeles City, Pampanga', 'Investor-grade units with high rental yield potential near Clark economic zone.'],
            ['Holy Family Residences', 'Brgy. Holy Family, Angeles City, Pampanga', 'Peaceful and secure residential complex near Holy Family Parish Church.'],
            ['Nepo Quad Apartments', 'Brgy. Pulung Maragul, Angeles City, Pampanga', 'Modern apartments near Nepo Mall and Robinsons Starmills Pampanga.'],
            ['Diamond Residences', 'Brgy. Cutcut, Angeles City, Pampanga', 'Premium residential units with diamond-class amenities and 24/7 security.'],
            ['Friendship Village', 'Brgy. Friendship, Angeles City, Pampanga', 'Well-established residential community with complete utilities and facilities.'],
            ['Pampanga Central Flats', 'Brgy. San Jose, San Fernando, Pampanga', 'Centrally located flats with easy access to government offices and commercial areas.'],
            ['Angeles Heights', 'Brgy. Anunas, Angeles City, Pampanga', 'Elevated residential complex with panoramic views and cool mountain breeze.'],
            ['Starmills Residences', 'Brgy. San Isidro, San Fernando, Pampanga', 'Modern living near Robinsons Starmills and other major shopping centers.'],
            ['Clark Green City Homes', 'Brgy. Capaya, Angeles City, Pampanga', 'Eco-friendly residential complex with green spaces and sustainable facilities.'],
            ['Apalit River View', 'Brgy. San Vicente, Apalit, Pampanga', 'Scenic river view units perfect for nature lovers and peaceful living.'],
            ['Guagua Modern Flats', 'Brgy. Poblacion, Guagua, Pampanga', 'Modern residential flats in the historic town of Guagua with complete amenities.'],
            ['Bacolor Heritage Suites', 'Brgy. San Pablo, Bacolor, Pampanga', 'Heritage-inspired suites in the historic town of Bacolor, Pampanga.'],
            ['Floridablanca Garden Homes', 'Brgy. Poblacion, Floridablanca, Pampanga', 'Garden homes in the peaceful town of Floridablanca with fresh air and nature.'],
            ['Porac Mountain View', 'Brgy. Poblacion, Porac, Pampanga', 'Mountain view residential units in Porac with cool climate and fresh environment.'],
            ['Lubao Riverside Homes', 'Brgy. San Antonio, Lubao, Pampanga', 'Riverside residential homes in Lubao with scenic views and peaceful ambiance.'],
            ['Sasmuan Coastal Living', 'Brgy. Malusac, Sasmuan, Pampanga', 'Unique coastal living experience near Sasmuan Coastal Wetlands.'],
            ['Mexico Town Residences', 'Brgy. Poblacion, Mexico, Pampanga', 'Affordable residential units in the progressive town of Mexico, Pampanga.'],
            ['Magalang Hillside Homes', 'Brgy. Camias, Magalang, Pampanga', 'Hillside homes in Magalang with cool climate and scenic mountain views.'],
            ['Candaba Wetlands Villas', 'Brgy. Poblacion, Candaba, Pampanga', 'Unique villas near Candaba Wetlands, perfect for nature enthusiasts.'],
            ['Sta. Ana Modern Suites', 'Brgy. Poblacion, Sta. Ana, Pampanga', 'Modern suites in the quiet town of Sta. Ana with complete basic amenities.'],
            ['Masantol Waterfront Homes', 'Brgy. Sagrada Familia, Masantol, Pampanga', 'Waterfront residential homes in Masantol with beautiful river views.'],
            ['Macabebe Riverside Flats', 'Brgy. Poblacion, Macabebe, Pampanga', 'Affordable riverside flats in Macabebe with easy access to main roads.'],
            ['Minalin Peaceful Homes', 'Brgy. Poblacion, Minalin, Pampanga', 'Peaceful residential homes in the quiet town of Minalin, Pampanga.'],
            ['Sto. Tomas Garden Units', 'Brgy. Poblacion, Sto. Tomas, Pampanga', 'Garden units in the progressive municipality of Sto. Tomas, Pampanga.'],
            ['San Luis Valley Homes', 'Brgy. Poblacion, San Luis, Pampanga', 'Valley view homes in San Luis with fresh air and peaceful environment.'],
            ['San Simon Riverside', 'Brgy. Poblacion, San Simon, Pampanga', 'Riverside residential units in San Simon with scenic views and cool breeze.'],
            ['Sta. Rita Modern Flats', 'Brgy. Poblacion, Sta. Rita, Pampanga', 'Modern residential flats in Sta. Rita with complete utilities and amenities.'],
            ['Betis Heritage Homes', 'Brgy. Betis, Guagua, Pampanga', 'Heritage-inspired homes in the historic barangay of Betis, Guagua.'],
            ['Del Carmen Suites', 'Brgy. Del Carmen, San Fernando, Pampanga', 'Modern suites in Del Carmen with easy access to SM Pampanga and NLEX.'],
            ['Dolores Residential Park', 'Brgy. Dolores, San Fernando, Pampanga', 'Residential park with green spaces and family-friendly environment.'],
            ['San Agustin Modern Homes', 'Brgy. San Agustin, San Fernando, Pampanga', 'Modern homes in San Agustin with complete amenities and 24/7 security.'],
            ['San Felipe Garden Villas', 'Brgy. San Felipe, San Fernando, Pampanga', 'Garden villas in San Felipe with landscaped surroundings and parking.'],
            ['San Juan Residential Hub', 'Brgy. San Juan, San Fernando, Pampanga', 'Residential hub in San Juan strategically located near commercial areas.'],
            ['San Pedro Valley Homes', 'Brgy. San Pedro, San Fernando, Pampanga', 'Valley homes in San Pedro with mountain views and cool climate.'],
            ['Santiago Premium Suites', 'Brgy. Santiago, San Fernando, Pampanga', 'Premium suites in Santiago with high-end finishes and modern amenities.'],
            ['Quebiawan Modern Flats', 'Brgy. Quebiawan, San Fernando, Pampanga', 'Modern flats in Quebiawan near Robinsons Starmills and main commercial areas.'],
            ['Sindalan Garden Residences', 'Brgy. Sindalan, San Fernando, Pampanga', 'Garden residences in Sindalan with lush greenery and peaceful environment.'],
        ];

        $unitTypes = ['Studio', '1BR', '2BR', 'Room', 'Studio', '1BR'];
        $rentPrices = [4000, 4500, 5000, 5500, 6000, 6500, 7000, 7500, 8000, 8500, 9000, 10000, 12000, 15000];
        $lateFeeValues = [3, 4, 5, 6, 7, 8, 10];

        $properties = [];
        foreach ($propertyData as $index => $data) {
            $properties[] = Property::create([
                'owner_id' => $owner->id,
                'name' => $data[0],
                'address' => $data[1],
                'description' => $data[2],
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
                'address' => 'Brgy. Sample, Angeles City, Pampanga',
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
