<?php

namespace Database\Seeders;

use App\Models\Regional;
use Illuminate\Database\Seeder;

class RegionalSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $regionals = [
            'Bekasi',
            'Jabatim',
            'Jakarta',
            'Cilegon',
            'Lampung',
        ];

        foreach ($regionals as $namaRegional) {
            Regional::updateOrCreate(
                ['nama_regional' => $namaRegional],
                ['nama_regional' => $namaRegional]
            );
        }
    }
}
