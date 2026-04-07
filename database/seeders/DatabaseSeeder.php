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
                'email'       => 'kepsek@belajarcerdas.id',
                'password'    => Hash::make('password123'), // Password default
                'no_hp'       => '081111111111',
                'role'        => 'Guru',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'email'       => 'kurikulum@belajarcerdas.id',
                'password'    => Hash::make('password123'),
                'no_hp'       => '082222222222',
                'role'        => 'Administrator',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'email'       => 'guru@belajarcerdas.id',
                'password'    => Hash::make('password123'),
                'no_hp'       => '083333333333',
                'role'        => 'Guru',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'email'       => 'murid@belajarcerdas.id',
                'password'    => Hash::make('password123'),
                'no_hp'       => '084444444444',
                'role'        => 'Murid',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'email'       => 'orangtua@belajarcerdas.id',
                'password'    => Hash::make('password123'),
                'no_hp'       => '085555555555',
                'role'        => 'Siswa',
                'status_akun' => 'aktif',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        ]);
    }
}