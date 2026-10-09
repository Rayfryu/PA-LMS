<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\SubjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Subject::class);

        $query = Subject::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $subjects = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => $subjects,
            'filters' => $request->only(['search']),
            'can' => [
                'create' => $request->user()->can('create', Subject::class),
                'update' => $request->user()->can('update', new Subject()),
                'delete' => $request->user()->can('delete', new Subject()),
            ],
        ]);
    }

    public function store(StoreSubjectRequest $request, SubjectService $service)
    {
        Gate::authorize('create', Subject::class);

        $service->create($request->validated());

        return back()->with('success', 'Subject created successfully.');
    }

    public function update(UpdateSubjectRequest $request, Subject $subject, SubjectService $service)
    {
        Gate::authorize('update', $subject);

        $service->update($subject, $request->validated());

        return back()->with('success', 'Subject updated successfully.');
    }

    public function toggleStatus(Subject $subject, SubjectService $service)
    {
        Gate::authorize('update', $subject);

        $service->toggleStatus($subject);

        return back()->with('success', 'Subject status updated successfully.');
    }

    public function destroy(Subject $subject, SubjectService $service)
    {
        Gate::authorize('delete', $subject);

        try {
            $service->delete($subject);
            return back()->with('success', 'Subject deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}