<?php

namespace App\Http\Requests\CourseSchedule; // Namespace diperbaiki sesuai folder

use App\Models\Course;
use App\Models\CourseSchedule;
use App\Enums\AcademicYearStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CourseScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        
        $roleSlug = strtolower($user->role?->slug ?? '');
        return in_array($roleSlug, ['admin', 'administrator']);
    }

    public function rules(): array
    {
        return [
           'day_of_week' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:255',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $course = $this->route('course');
            $scheduleId = $this->route('schedule') ? $this->route('schedule')->id : null;
            
            if (!$course) return;

            // 1. Validasi Academic Year (Archived)
            $academicYear = $course->schoolClass?->academicYear;
            if ($academicYear) {
                $statusValue = $academicYear->status instanceof AcademicYearStatus ? $academicYear->status->value : $academicYear->status;
                if ($statusValue === 'archived' || $statusValue === AcademicYearStatus::Archived->value) {
                    $validator->errors()->add('general', 'Tidak dapat memanipulasi jadwal pada tahun ajaran yang diarsip.');
                    return;
                }
            }

            // 2. Deteksi Konflik (Overlap Interval: start < new_end AND end > new_start)
            $start = $this->input('start_time');
            $end = $this->input('end_time');
            $day = $this->input('day_of_week');
            $room = $this->input('room');

            // Ambil semua jadwal di hari yang sama yang beririsan waktu
            $overlappingSchedules = CourseSchedule::with('course')
                ->where('day_of_week', $day)
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
                })
                ->when($scheduleId, fn($q) => $q->where('id', '!=', $scheduleId))
                ->get();

            foreach ($overlappingSchedules as $overlap) {
                // Konflik Kelas
                if ($overlap->course->school_class_id === $course->school_class_id) {
                    $validator->errors()->add('time', "Konflik Jadwal Kelas: Kelas ini sudah memiliki jadwal pada waktu tersebut ({$overlap->start_time} - {$overlap->end_time}).");
                }
                // Konflik Guru
                if ($course->teacher_id && $overlap->course->teacher_id === $course->teacher_id) {
                    $validator->errors()->add('time', "Konflik Jadwal Guru: Guru ini sudah mengajar di kelas lain pada waktu tersebut.");
                }
                // Konflik Ruangan
                if ($room && $overlap->room === $room) {
                    $validator->errors()->add('room', "Konflik Ruangan: Ruangan {$room} sudah terpakai pada waktu tersebut.");
                }
            }
        });
    }
}