<?php

namespace Database\Seeders;

use App\Models\Regional;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'username' => 'admin',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'regional_id' => null,
            ]
        );

        $regionals = Regional::query()->pluck('id', 'nama_regional');
        $regionalNames = ['Bekasi', 'Jabatim', 'Jakarta', 'Cilegon', 'Lampung'];

        foreach ($regionalNames as $regionalName) {
            $regionalId = $regionals->get($regionalName);
            if (!$regionalId) {
                continue;
            }

            $slug = Str::slug($regionalName);

            User::updateOrCreate(
                ['email' => "{$slug}.teknisi@test.com"],
                [
                    'username' => $slug,
                    'role' => 'teknisi',
                    'password' => Hash::make('password'),
                    'regional_id' => $regionalId,
                ]
            );

            User::updateOrCreate(
                ['email' => "{$slug}.ro@test.com"],
                [
                    'username' => "{$slug}_ro",
                    'role' => 'kepala_ro',
                    'password' => Hash::make('password'),
                    'regional_id' => $regionalId,
                ]
            );
        }

        $jakartaRegionalId = $regionals->get('Jakarta');

        User::updateOrCreate(
            ['email' => 'jakarta.pusat@test.com'],
            [
                'username' => 'jakarta_pusat',
                'role' => 'pusat',
                'password' => Hash::make('password'),
                'regional_id' => null,
            ]
        );
    }
}
