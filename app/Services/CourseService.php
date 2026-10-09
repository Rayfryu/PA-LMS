<?php

namespace App\Services;

use App\Models\Course;
use App\Models\SchoolClass;

class CourseService
{
    public function create(array $data): Course
    {
        $schoolClass = SchoolClass::findOrFail($data['school_class_id']);
        $data['academic_year_id'] = $schoolClass->academic_year_id;

        return Course::create($data);
    }

    public function update(Course $course, array $data): Course
    {
        if (isset($data['school_class_id'])) {
            $schoolClass = SchoolClass::findOrFail($data['school_class_id']);
            $data['academic_year_id'] = $schoolClass->academic_year_id;
        }

        $course->update($data);
        return $course;
    }

    public function toggleStatus(Course $course): Course
    {
        $course->update([
            'is_active' => !$course->is_active,
        ]);
        return $course;
    }

    public function delete(Course $course): void
    {
        $course->delete();
    }
}