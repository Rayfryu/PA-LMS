<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role teacher ada
        $teacherRole = Role::firstOrCreate(
            ['slug' => 'teacher'],
            ['name' => 'Teacher']
        );

        $password = Hash::make('password123'); // Password default untuk semua guru

        // Daftar Guru Mapel Umum
        $guruUmum = [
            ['name' => 'Budi Santoso, S.Pd', 'email' => 'budi.mtk@sekolah.com'],
            ['name' => 'Siti Aminah, M.Pd', 'email' => 'siti.bindo@sekolah.com'],
            ['name' => 'Drs. Agus Wijaya', 'email' => 'agus.pkn@sekolah.com'],
            ['name' => 'Rina Melati, S.Pd', 'email' => 'rina.inggris@sekolah.com'],
            ['name' => 'Ahmad Faisal, S.Ag', 'email' => 'ahmad.pai@sekolah.com'],
        ];

        // Daftar Guru Mapel Produktif / Jurusan
        $guruProduktif = [
            ['name' => 'Hendra Kusuma, S.Kom', 'email' => 'hendra.rpl@sekolah.com'],
            ['name' => 'Diana Fitri, S.T', 'email' => 'diana.tkj@sekolah.com'],
            ['name' => 'Eko Prasetyo, S.Kom', 'email' => 'eko.mm@sekolah.com'],
            ['name' => 'Wahyu Hidayat, S.T', 'email' => 'wahyu.mesin@sekolah.com'],
            ['name' => 'Ratna Sari, S.E', 'email' => 'ratna.akuntansi@sekolah.com'],
        ];

        $semuaGuru = array_merge($guruUmum, $guruProduktif);

        foreach ($semuaGuru as $guru) {
            User::firstOrCreate(
                ['email' => $guru['email']],
                [
                    'name' => $guru['name'],
                    'password' => $password,
                    'role_id' => $teacherRole->id,
                ]
            );
        }

        // (Opsional) Tambahkan 10 guru random menggunakan Factory
        User::factory(10)->create([
            'role_id' => $teacherRole->id,
            'password' => $password,
        ]);
    }
}