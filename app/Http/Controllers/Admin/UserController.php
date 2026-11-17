<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('user_code', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->withCount('patients')
            ->latest('user_id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['user', 'administrator'])],
            'password' => ['required', 'string', Password::defaults()],
        ]);

        User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(), // Auto verify jika dibuat oleh admin
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'role' => ['required', Rule::in(['user', 'administrator'])],
            'password' => ['nullable', 'string', Password::defaults()],
        ]);

        // Cegah admin mengubah rolenya sendiri menjadi user jika dia sedang login
        if ($request->user()->user_id === $user->user_id && $validated['role'] !== 'administrator') {
            return back()->withErrors(['role' => 'Anda tidak dapat menurunkan role akun Anda sendiri.']);
        }

        $payload = [
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return back()->with('success', 'Data user berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', "Password untuk akun {$user->username} berhasil direset.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->user_id === $user->user_id) {
            return back()->withErrors(['message' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
