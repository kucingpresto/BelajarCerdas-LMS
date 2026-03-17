<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;   // Tambahkan ini
use Illuminate\Support\Facades\Hash; // Tambahkan ini

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Menyuntikkan 5 data spesifik ke tabel user_accounts
        DB::table('user_accounts')->insert([
            [
                'email'       => 'kepsek@sekolah.com',
                'password'    => Hash::make('password123'), // Password default
                'no_hp'       => '081111111111',
                'role'        => 'kepala sekolah',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'email'       => 'kurikulum@sekolah.com',
                'password'    => Hash::make('password123'),
                'no_hp'       => '082222222222',
                'role'        => 'kurikulum',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'email'       => 'guru@sekolah.com',
                'password'    => Hash::make('password123'),
                'no_hp'       => '083333333333',
                'role'        => 'guru',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'email'       => 'murid@sekolah.com',
                'password'    => Hash::make('password123'),
                'no_hp'       => '084444444444',
                'role'        => 'murid',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'email'       => 'orangtua@sekolah.com',
                'password'    => Hash::make('password123'),
                'no_hp'       => '085555555555',
                'role'        => 'orangtua',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        ]);
    }
}