<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Pemrograman Web', 'code' => 'WEB', 'description' => 'Materi pengembangan aplikasi berbasis web.'],
            ['name' => 'Basis Data', 'code' => 'DB', 'description' => 'Materi perancangan dan pengelolaan basis data.'],
            ['name' => 'Jaringan Komputer', 'code' => 'NET', 'description' => 'Materi konsep dan arsitektur jaringan komputer.'],
            ['name' => 'Matematika', 'code' => 'MAT', 'description' => 'Materi matematika terapan dasar dan logika.'],
            ['name' => 'Bahasa Indonesia', 'code' => 'IND', 'description' => 'Materi tata bahasa dan komunikasi ilmiah.'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['code' => $subject['code']],
                [
                    'name' => $subject['name'],
                    'description' => $subject['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}