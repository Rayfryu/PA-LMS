<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GradeLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRequest\StoreClassRequest;
use App\Http\Requests\ClassRequest\UpdateClassRequest;
use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\ClassService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClassController extends Controller
{
    public function __construct(protected ClassService $classService) {}

    public function index(): Response
    {
        $this->authorize('viewAny', SchoolClass::class);

        return Inertia::render('Admin/Classes/Index', [
            'classes' => SchoolClass::query()
                ->with(['academicYear:id,name', 'major:id,name', 'homeroomTeacher:id,name'])
                ->orderByDesc('academic_year_id')
                ->orderBy('name')
                ->get()
                ->map(fn (SchoolClass $class) => [
                    'id' => $class->id,
                    'name' => $class->name,
                    'grade_level' => $class->grade_level->value,
                    'academic_year_name' => $class->academicYear->name,
                    'major_name' => $class->major->name,
                    'homeroom_teacher_name' => $class->homeroomTeacher?->name,
                    'capacity' => $class->capacity,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SchoolClass::class);

        return Inertia::render('Admin/Classes/Create', $this->formOptions());
    }

    public function store(StoreClassRequest $request): RedirectResponse
    {
        $this->classService->create($request->validated());

        return redirect()->route('admin.classes.index')->with('success', 'Class created.');
    }

    public function edit(SchoolClass $class): Response
    {
        $this->authorize('update', $class);

        $class->load(['academicYear']);

        return Inertia::render('Admin/Classes/Edit', [
            ...$this->formOptions(includeCurrentAcademicYear: $class->academicYear),
            'class' => [
                'id' => $class->id,
                'academic_year_id' => $class->academic_year_id,
                'major_id' => $class->major_id,
                'grade_level' => $class->grade_level->value,
                'name' => $class->name,
                'homeroom_teacher_id' => $class->homeroom_teacher_id,
                'capacity' => $class->capacity,
            ],
            'academicYearLocked' => $class->academicYear->isArchived(),
        ]);
    }

    public function update(UpdateClassRequest $request, SchoolClass $class): RedirectResponse
    {
        $this->classService->update($class, $request->validated());

        return redirect()->route('admin.classes.index')->with('success', 'Class updated.');
    }

    /**
     * Dropdown options shared by create/edit: only academic years that can
     * still accept new/changed classes (draft/active), only active majors,
     * and only User accounts with the teacher role — never the raw User
     * table, so the frontend never has to filter security-relevant lists
     * itself (§14 of the Phase 2B spec).
     *
     * @return array<string, mixed>
     */
    protected function formOptions(?AcademicYear $includeCurrentAcademicYear = null): array
    {
        $academicYears = AcademicYear::query()
            ->whereIn('status', ['draft', 'active'])
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'status'])
            ->map(fn (AcademicYear $year) => [
                'id' => $year->id,
                'name' => $year->name,
                'status' => $year->status->value,
            ]);

        // When editing a class whose academic year has since been archived,
        // the select must still be able to display that value even though
        // it's no longer a valid choice for NEW assignments.
        if ($includeCurrentAcademicYear && ! $academicYears->contains('id', $includeCurrentAcademicYear->id)) {
            $academicYears->push([
                'id' => $includeCurrentAcademicYear->id,
                'name' => $includeCurrentAcademicYear->name,
                'status' => $includeCurrentAcademicYear->status->value,
            ]);
        }

        return [
            'academicYears' => $academicYears->values(),
            'majors' => Major::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'teachers' => User::query()
                ->whereHas('role', fn ($q) => $q->where('slug', Role::TEACHER))
                ->orderBy('name')
                ->get(['id', 'name']),
            'gradeLevels' => array_map(fn (GradeLevel $g) => $g->value, GradeLevel::cases()),
        ];
    }
}