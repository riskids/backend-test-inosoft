<?php

namespace Database\Seeders;

use App\Models\Household;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($households as $data) {
            Household::create($data);
        }
    }
}
