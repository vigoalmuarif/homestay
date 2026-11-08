<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users along with their roles.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasRole('admin')) {
            abort(403, 'Akses Ditolak. Hanya Administrator yang memiliki wewenang untuk mengelola pengguna.');
        }

        $search = $request->query('search');
        $roleFilter = $request->query('role', 'all');

        $query = User::with('roles')->latest();

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== 'all') {
            $query->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        $users = $query->paginate(10)->withQueryString();

        $roles = Role::orderBy('name')->get();

        $stats = [
            'total' => User::count(),
            'admin' => User::role('admin')->count(),
            'receptionist' => User::role('receptionist')->count(),
            'housekeeping' => User::role('housekeeping')->count(),
        ];

        return view('pages.users.index', [
            'title' => 'Kelola Petugas & Role',
            'users' => $users,
            'roles' => $roles,
            'stats' => $stats,
            'search' => $search,
            'roleFilter' => $roleFilter,
        ]);
    }

    /**
     * Store a newly created user with role in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasRole('admin')) {
            abort(403, 'Hanya Administrator yang berwenang menambahkan petugas.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar untuk akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role hak akses wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak terdaftar dalam sistem.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', "Petugas {$user->name} dengan role {$validated['role']} berhasil ditambahkan!");
    }

    /**
     * Update the specified user and their role in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasRole('admin')) {
            abort(403, 'Hanya Administrator yang berwenang memperbarui data petugas.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar untuk petugas lain.',
            'role.required' => 'Role hak akses wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',
            'password.min' => 'Password baru minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Safety check: Cannot demote the last admin
        if ($user->hasRole('admin') && $validated['role'] !== 'admin' && User::role('admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat mengubah role satu-satunya Administrator sistem.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', "Data petugas {$user->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->hasRole('admin')) {
            abort(403, 'Hanya Administrator yang berwenang menghapus petugas.');
        }

        // Safety check 1: Cannot delete own logged-in account
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang masuk.');
        }

        // Safety check 2: Cannot delete the last admin
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya akun Administrator sistem.');
        }

        try {
            $name = $user->name;
            $user->delete();

            return redirect()->route('users.index')->with('success', "Akun petugas {$name} berhasil dihapus.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus petugas. Petugas ini memiliki riwayat data operasional terkait.');
        }
    }
}

