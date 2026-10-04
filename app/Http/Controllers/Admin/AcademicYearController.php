<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYear\StoreAcademicYearRequest;
use App\Http\Requests\AcademicYear\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function __construct(protected AcademicYearService $academicYearService) {}

    public function index(): Response
    {
        $this->authorize('viewAny', AcademicYear::class);

        return Inertia::render('Admin/AcademicYears/Index', [
            'academicYears' => AcademicYear::query()
                ->orderByDesc('start_date')
                ->get(['id', 'name', 'start_date', 'end_date', 'status'])
                ->map(fn (AcademicYear $year) => [
                    'id' => $year->id,
                    'name' => $year->name,
                    'start_date' => $year->start_date->toDateString(),
                    'end_date' => $year->end_date->toDateString(),
                    'status' => $year->status->value,
                ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AcademicYear::class);

        return Inertia::render('Admin/AcademicYears/Create');
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        AcademicYear::query()->create($request->validated());

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Academic year created.');
    }

    // NOTE: parameter must be named $academic_year (snake_case) to match the
    // route wildcard {academic_year} — Route::resource('academic-years', ...)
    // converts the hyphen to an underscore for its implicit binding name, and
    // Laravel requires an exact name match to resolve the model.
    public function edit(AcademicYear $academic_year): Response
    {
        $this->authorize('update', $academic_year);

        return Inertia::render('Admin/AcademicYears/Edit', [
            'academicYear' => [
                'id' => $academic_year->id,
                'name' => $academic_year->name,
                'start_date' => $academic_year->start_date->toDateString(),
                'end_date' => $academic_year->end_date->toDateString(),
                'status' => $academic_year->status->value,
            ],
        ]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academic_year): RedirectResponse
    {
        $academic_year->update($request->validated());

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'Academic year updated.');
    }

    public function activate(AcademicYear $academic_year): RedirectResponse
    {
        $this->authorize('transition', $academic_year);

        $this->academicYearService->activate($academic_year);

        return redirect()->route('admin.academic-years.index')
            ->with('success', "{$academic_year->name} is now active.");
    }

    public function archive(AcademicYear $academic_year): RedirectResponse
    {
        $this->authorize('transition', $academic_year);

        $this->academicYearService->archive($academic_year);

        return redirect()->route('admin.academic-years.index')
            ->with('success', "{$academic_year->name} has been archived.");
    }
}