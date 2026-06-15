<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemUser;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Exports\SystemUserExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class SystemUserController extends Controller
{
    public function index(): View
    {
        $systemUsers = SystemUser::with('user')
            ->whereHas('user', function ($q) {
                $q->where('email', '!=', 'admin@nexorait.lk');
            })
            ->latest()
            ->paginate(10);

        return view('admin.system-users.index', compact('systemUsers'));
    }
    public function create(): View
    {
        $userTypes = UserType::where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view(
            'admin.system-users.create',
            compact('userTypes')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_type_id' => ['required', 'exists:user_types,id'],

            'full_name' => ['required', 'string', 'max:255'],
            'mobile'    => ['required', 'string', 'max:20'],
            'nic'       => ['required', 'string', 'max:30'],
            'bday'      => ['nullable', 'date'],
            'gender'    => ['required', 'in:male,female,other'],
            'address1'  => ['required', 'string', 'max:255'],
            'address2'  => ['nullable', 'string', 'max:255'],
            'address3'  => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'note'      => ['nullable', 'string'],

            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:6'],
        ]);

        try {
            DB::transaction(function () use ($request, $validated) {

                $customId = $this->generateSystemUserCustomId();

                $user = User::create([
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'is_active' => $request->boolean('is_active'),
                    'user_type_id' => $validated['user_type_id'],
                ]);

                SystemUser::create([
                    'custom_id' => $customId,
                    'user_id' => $user->id,
                    'full_name' => $validated['full_name'],
                    'mobile' => $validated['mobile'],
                    'nic' => $validated['nic'],
                    'bday' => $validated['bday'] ?? null,
                    'gender' => $validated['gender'],
                    'address1' => $validated['address1'],
                    'address2' => $validated['address2'] ?? null,
                    'address3' => $validated['address3'] ?? null,
                    'is_active' => $request->boolean('is_active'),
                    'note' => $validated['note'] ?? null,
                ]);
            });

            return redirect()
                ->route('admin.system-users.index')
                ->with('success', 'System user created successfully.');
        } catch (\Throwable $e) {

            Log::error('System user create failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while saving system user.');
        }
    }

    public function edit(SystemUser $systemUser): View
    {
        $userTypes = UserType::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'admin.system-users.edit',
            compact('systemUser', 'userTypes')
        );
    }

    public function update(Request $request, SystemUser $systemUser): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'nic' => ['required', 'string', 'max:30'],
            'bday' => ['nullable', 'date'],
            'gender' => ['required', 'in:male,female,other'],
            'address1' => ['required', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'address3' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],

            // user table fields
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $systemUser->user_id,
            ],

            'password' => ['nullable', 'string', 'min:6'],
        ]);

        try {

            DB::transaction(function () use ($request, $validated, $systemUser) {

                // update related user
                if ($systemUser->user) {

                    $userData = [
                        'name' => $validated['full_name'],
                        'email' => $validated['email'],
                        'is_active' => $request->boolean('is_active'),
                    ];

                    // password only if entered
                    if (!empty($validated['password'])) {
                        $userData['password'] = $validated['password'];
                    }

                    $systemUser->user->update($userData);
                }

                // update system user
                $systemUser->update([
                    // custom_id NOT editable
                    'full_name' => $validated['full_name'],
                    'mobile' => $validated['mobile'],
                    'nic' => $validated['nic'],
                    'bday' => $validated['bday'] ?? null,
                    'gender' => $validated['gender'],
                    'address1' => $validated['address1'],
                    'address2' => $validated['address2'] ?? null,
                    'address3' => $validated['address3'] ?? null,
                    'is_active' => $request->boolean('is_active'),
                    'note' => $validated['note'] ?? null,
                ]);
            });

            return redirect()
                ->route('admin.system-users.index')
                ->with('success', 'System user updated successfully.');
        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while updating system user.');
        }
    }

    public function show(SystemUser $systemUser): View
    {
        return view(
            'admin.system-users.show',
            compact('systemUser')
        );
    }

    public function destroy(SystemUser $systemUser): RedirectResponse
    {
        $systemUser->delete();

        return redirect()
            ->route('admin.system-users.index')
            ->with('success', 'System user deleted successfully.');
    }

    private function generateSystemUserCustomId(): string
    {
        $lastSystemUser = SystemUser::withTrashed()
            ->where('custom_id', 'like', 'SA-U-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastSystemUser && $lastSystemUser->custom_id) {
            preg_match('/SA-U-(\d+)/', $lastSystemUser->custom_id, $matches);

            if (!empty($matches[1])) {
                $nextNumber = (int) $matches[1] + 1;
            }
        }

        return 'SA-U-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function exportExcel(Request $request)
    {
        $filename = 'system_users_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new SystemUserExport($request), $filename);
    }

    public function exportPdf(Request $request)
    {
        $query = SystemUser::with('user')
            ->whereHas('user', function ($q) {
                $q->where('email', '!=', 'admin@nexorait.lk');
            });

        if ($request->search) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('custom_id', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active === 'true');
        }

        $systemUsers = $query->latest()->get();

        $pdf = Pdf::loadView('admin.system-users.pdf', compact('systemUsers'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('system_users_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
