<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Major\StoreMajorRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Models\Major;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MajorController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Major::class);

        return Inertia::render('Admin/Majors/Index', [
            'majors' => Major::query()
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Major::class);

        return Inertia::render('Admin/Majors/Create');
    }

    public function store(StoreMajorRequest $request): RedirectResponse
    {
        Major::query()->create($request->validated());

        return redirect()->route('admin.majors.index')->with('success', 'Major created.');
    }

    public function edit(Major $major): Response
    {
        $this->authorize('update', $major);

        return Inertia::render('Admin/Majors/Edit', [
            'major' => $major->only(['id', 'code', 'name', 'description', 'is_active']),
        ]);
    }

    public function update(UpdateMajorRequest $request, Major $major): RedirectResponse
    {
        $major->update($request->validated());

        return redirect()->route('admin.majors.index')->with('success', 'Major updated.');
    }
}