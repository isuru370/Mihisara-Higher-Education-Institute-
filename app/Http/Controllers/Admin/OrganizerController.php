<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassPaymentConfig;
use Illuminate\Support\Facades\DB;
use App\Exports\OrganizerExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class OrganizerController extends Controller
{
    public function index(Request $request)
    {
        $query = Organizer::query();

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('mobile', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('nic', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        // Status filter
        if ($request->is_active !== null && $request->is_active !== '') {
            $query->where('is_active', $request->is_active === 'true');
        }

        $organizers = $query->latest()->paginate(10);

        return view('admin.organizers.index', compact('organizers'));
    }

    public function create()
    {
        return view('admin.organizers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'nic' => 'nullable|string|max:20|unique:organizers,nic',
            'note' => 'nullable|string',
        ]);

        // 🔥 Generate Code (ORG001)
        $last = Organizer::withTrashed()->latest('id')->first();
        $number = $last ? ((int) substr($last->code, 3)) + 1 : 1;
        $code = 'ORG' . str_pad($number, 3, '0', STR_PAD_LEFT);

        Organizer::create([
            'code' => $code,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'nic' => $request->nic,
            'note' => $request->note,
            'is_active' => $request->has('is_active'),
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.organizers.index')
            ->with('success', 'Organizer created successfully.');
    }

    public function show(Organizer $organizer)
    {
        return view('admin.organizers.show', compact('organizer'));
    }

    public function edit(Organizer $organizer)
    {
        return view('admin.organizers.edit', compact('organizer'));
    }

    public function update(Request $request, Organizer $organizer)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'nic' => 'nullable|string|max:20|unique:organizers,nic,' . $organizer->id,
            'note' => 'nullable|string',
        ]);

        $organizer->update([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'nic' => $request->nic,
            'note' => $request->note,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()
            ->route('admin.organizers.index')
            ->with('success', 'Organizer updated successfully.');
    }

    public function destroy(Organizer $organizer)
    {
        // 🔥 check active classes
        $hasActiveClasses = ClassPaymentConfig::where('organizer_id', $organizer->id)
            ->whereHas('studentClass', function ($q) {
                $q->where('is_active', true);
            })
            ->exists();

        if ($hasActiveClasses) {
            return back()->with('error', 'Cannot delete organizer. Active classes exist.');
        }

        DB::transaction(function () use ($organizer) {

            // delete configs
            ClassPaymentConfig::where('organizer_id', $organizer->id)
                ->delete();

            $organizer->delete();
        });

        return redirect()
            ->route('admin.organizers.index')
            ->with('success', 'Organizer deleted successfully.');
    }

    // Toggle Active / Inactive
    public function toggleActive(Organizer $organizer)
    {
        // 🔥 check active classes
        $hasActiveClasses = ClassPaymentConfig::where('organizer_id', $organizer->id)
            ->where('is_active', true)
            ->whereHas('studentClass', function ($q) {
                $q->where('is_active', true);
            })
            ->exists();

        if ($organizer->is_active && $hasActiveClasses) {
            return back()->with('error', 'Cannot deactivate organizer. Active classes exist.');
        }

        DB::transaction(function () use ($organizer) {

            $newStatus = !$organizer->is_active;

            $organizer->update([
                'is_active' => $newStatus
            ]);

            // sync payment configs
            ClassPaymentConfig::where('organizer_id', $organizer->id)
                ->update([
                    'is_active' => $newStatus
                ]);
        });

        return back()->with('success', 'Status updated.');
    }

    public function exportExcel(Request $request)
    {
        $filename = 'organizers_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new OrganizerExport($request), $filename);
    }

    public function exportPdf(Request $request)
    {
        $query = Organizer::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('mobile', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('nic', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->is_active !== null && $request->is_active !== '') {
            $query->where('is_active', $request->is_active === 'true');
        }

        $organizers = $query->latest()->get();

        $pdf = Pdf::loadView('admin.organizers.pdf', compact('organizers'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('organizers_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
