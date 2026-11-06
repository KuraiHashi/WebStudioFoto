<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('TemplateAdmin.account', [
            'user' => $request->user(),
            'admins' => $request->user()->isSuperAdmin()
                ? User::query()->where('role', User::ROLE_ADMIN)->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'current_password' => ['required', 'current_password'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
        ], [
            'password.min' => 'Password baru harus terdiri dari minimal 12 karakter.',
        ]);

        $user->email = $validated['email'];

        if (filled($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        $user->must_change_password = false;
        $user->save();
        $request->session()->regenerate();

        return redirect()->route('admin.account.edit')->with('success', 'Email dan password akun berhasil diperbarui.');
    }

    public function updateAdmin(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);
        abort_unless($user->role === User::ROLE_ADMIN, 404);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ], [
            'password.min' => 'Password baru harus terdiri dari minimal 12 karakter.',
        ]);

        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('admin.account.edit')->with('success', 'Akun admin berhasil diperbarui.');
    }
}
