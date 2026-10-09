<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\AcademicYearStatus;
use App\Models\ClassStudent;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class StudentEnrollmentService
{
    public function enroll(SchoolClass $class, int $userId, string $enrolledAt): ClassStudent
    {
        return DB::transaction(function () use ($class, $userId, $enrolledAt) {
            $this->validateAcademicYear($class);
            $this->validateCapacity($class);
            $this->validateUniqueActiveEnrollment($class, $userId);

            return ClassStudent::create([
                'class_id' => $class->id,
                'user_id' => $userId,
                'status' => EnrollmentStatus::Active,
                'enrolled_at' => $enrolledAt,
            ]);
        });
    }

    public function transfer(ClassStudent $enrollment, SchoolClass $newClass, string $transferDate): ClassStudent
    {
        return DB::transaction(function () use ($enrollment, $newClass, $transferDate) {
            // Lock records to prevent race conditions
            $enrollment = ClassStudent::lockForUpdate()->find($enrollment->id);
            
            if ($enrollment->status !== EnrollmentStatus::Active) {
                throw new Exception("Hanya enrollment aktif yang dapat ditransfer.");
            }
            
            if ($enrollment->schoolClass->academic_year_id !== $newClass->academic_year_id) {
                throw new Exception("Transfer hanya diizinkan dalam academic year yang sama.");
            }

            $this->validateAcademicYear($newClass);
            $this->validateCapacity($newClass);
            $this->validateUniqueActiveEnrollment($newClass, $enrollment->user_id);

            // Close current enrollment
            $enrollment->update([
                'status' => EnrollmentStatus::Transferred,
                'left_at' => $transferDate,
            ]);

            // Create new active enrollment
            return ClassStudent::create([
                'class_id' => $newClass->id,
                'user_id' => $enrollment->user_id,
                'status' => EnrollmentStatus::Active,
                'enrolled_at' => $transferDate,
            ]);
        });
    }

    public function withdraw(ClassStudent $enrollment, string $withdrawDate): void
    {
        if ($enrollment->status !== EnrollmentStatus::Active) {
            throw new Exception("Hanya enrollment aktif yang dapat ditarik (withdrawn).");
        }

        $enrollment->update([
            'status' => EnrollmentStatus::Withdrawn,
            'left_at' => $withdrawDate,
        ]);
    }

    private function validateAcademicYear(SchoolClass $class): void
    {
        if ($class->academicYear->status === AcademicYearStatus::Archived) {
            throw new Exception("Tidak dapat melakukan enrollment pada class dengan academic year yang sudah diarsipkan.");
        }
    }

    private function validateCapacity(SchoolClass $class): void
    {
        if ($class->capacity === null) {
            return;
        }

        // Lock rows for safe concurrency counting
        $activeCount = ClassStudent::where('class_id', $class->id)
            ->where('status', EnrollmentStatus::Active)
            ->lockForUpdate()
            ->count();

        if ($activeCount >= $class->capacity) {
            throw new Exception("Kapasitas class penuh.");
        }
    }

    private function validateUniqueActiveEnrollment(SchoolClass $class, int $userId): void
    {
        $hasActive = ClassStudent::where('user_id', $userId)
            ->where('status', EnrollmentStatus::Active)
            ->whereHas('schoolClass', function ($query) use ($class) {
                $query->where('academic_year_id', $class->academic_year_id);
            })
            ->exists();

        if ($hasActive) {
            throw new Exception("Siswa sudah memiliki enrollment aktif di academic year ini.");
        }
    }
}