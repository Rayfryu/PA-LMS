<?php

namespace Database\Seeders;

use App\Enums\AcademicYearStatus;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            ['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'status' => AcademicYearStatus::Archived],
            ['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'status' => AcademicYearStatus::Active],
            ['name' => '2027/2028', 'start_date' => '2027-07-01', 'end_date' => '2028-06-30', 'status' => AcademicYearStatus::Draft],
        ];

        foreach ($years as $year) {
            AcademicYear::query()->updateOrCreate(
                ['name' => $year['name']],
                $year,
            );
        }
    }
}