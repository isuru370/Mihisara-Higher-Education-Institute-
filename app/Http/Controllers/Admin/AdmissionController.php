<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdmissionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Admission::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('name', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%");
        }

        $admissions = $query->latest()->paginate(10);

        return view('admin.admissions.index', compact('admissions'));
    }

    public function create(): View
    {
        return view('admin.admissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'amount'    => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'note'      => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        Admission::create($validated);

        return redirect()
            ->route('admin.admissions.index')
            ->with('success', 'Admission created successfully.');
    }

    public function show(Admission $admission): View
    {
        $admission->load('payments');

        return view('admin.admissions.show', compact('admission'));
    }

    public function edit(Admission $admission): View
    {
        return view('admin.admissions.edit', compact('admission'));
    }

    public function update(Request $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'amount'    => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'note'      => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $admission->update($validated);

        return redirect()
            ->route('admin.admissions.index')
            ->with('success', 'Admission updated successfully.');
    }

    public function destroy(Admission $admission): RedirectResponse
    {
        $admission->delete();

        return redirect()
            ->route('admin.admissions.index')
            ->with('success', 'Admission deleted successfully.');
    }
}
