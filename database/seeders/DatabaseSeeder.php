<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\Payment;
use App\Models\User;
use App\Models\Waste;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default user for JWT authentication
        User::firstOrCreate(
            ['email' => 'admin@waste-collection.local'],
            [
                'name'     => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        // Create households
        $households = [
            [
                'owner_name' => 'Budi Santoso',
                'address'    => 'Jl. Merdeka No. 17, Jakarta Pusat',
                'block'      => 'A',
                'no'         => '12',
            ],
            [
                'owner_name' => 'Siti Aminah',
                'address'    => 'Jl. Sudirman Kav. 5, Bandung',
                'block'      => 'B',
                'no'         => '3',
            ],
            [
                'owner_name' => 'Ahmad Wijaya',
                'address'    => 'Jl. Gatot Subroto Km. 4, Jakarta Selatan',
                'block'      => 'C',
                'no'         => '7',
            ],
            [
                'owner_name' => 'Dewi Lestari',
                'address'    => 'Jl. Ahmad Yani No. 45, Surabaya',
                'block'      => 'D',
                'no'         => '22',
            ],
            [
                'owner_name' => 'Rudi Hermawan',
                'address'    => 'Jl. Diponegoro No. 88, Yogyakarta',
                'block'      => 'E',
                'no'         => '15',
            ],
        ];

        $createdHouseholds = [];
        foreach ($households as $data) {
            $createdHouseholds[] = Household::create($data);
        }

        // Create wastes with mixed types and statuses for testing
        // Household 1 (Budi) - has various waste types
        Waste::create([
            'household_id' => (string) $createdHouseholds[0]->_id,
            'type' => 'organic',
            'status' => 'completed',
            'pickup_date' => now()->subDays(2),
        ]);

        Waste::create([
            'household_id' => (string) $createdHouseholds[0]->_id,
            'type' => 'plastic',
            'status' => 'scheduled',
            'pickup_date' => now()->addDays(1),
        ]);

        Waste::create([
            'household_id' => (string) $createdHouseholds[0]->_id,
            'type' => 'electronic',
            'status' => 'pending',
            'safety_check' => false,
        ]);

        // Household 2 (Siti) - has completed pickups with payments
        Waste::create([
            'household_id' => (string) $createdHouseholds[1]->_id,
            'type' => 'organic',
            'status' => 'completed',
            'pickup_date' => now()->subDays(1),
        ]);

        Waste::create([
            'household_id' => (string) $createdHouseholds[1]->_id,
            'type' => 'paper',
            'status' => 'pending',
        ]);

        // Household 3 (Ahmad) - has pending electronic with safety check
        Waste::create([
            'household_id' => (string) $createdHouseholds[2]->_id,
            'type' => 'electronic',
            'status' => 'pending',
            'safety_check' => true,
        ]);

        // Household 4 (Dewi) - has canceled pickup
        Waste::create([
            'household_id' => (string) $createdHouseholds[3]->_id,
            'type' => 'organic',
            'status' => 'canceled',
        ]);

        // Household 5 (Rudi) - has unpaid payment (will block new pickups)
        // This is the household with unpaid payment to test business rule
        $unpaidPayment = Payment::create([
            'household_id' => (string) $createdHouseholds[4]->_id,
            'amount' => 50000,
            'payment_date' => now()->subDays(5),
            'status' => 'pending',
        ]);

        // Also create some completed payments for other households
        Payment::create([
            'household_id' => (string) $createdHouseholds[0]->_id,
            'amount' => 50000,
            'payment_date' => now()->subDays(3),
            'status' => 'paid',
        ]);

        Payment::create([
            'household_id' => (string) $createdHouseholds[1]->_id,
            'amount' => 50000,
            'payment_date' => now()->subDays(2),
            'status' => 'paid',
        ]);

        // Create a failed payment for testing
        Payment::create([
            'household_id' => (string) $createdHouseholds[2]->_id,
            'amount' => 75000,
            'payment_date' => now()->subDays(7),
            'status' => 'failed',
        ]);
    }
}