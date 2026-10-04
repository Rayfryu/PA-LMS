<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $majors = [
            ['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'is_active' => true],
            ['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan', 'is_active' => true],
        ];

        foreach ($majors as $major) {
            Major::query()->updateOrCreate(
                ['code' => $major['code']],
                $major,
            );
        }
    }
}