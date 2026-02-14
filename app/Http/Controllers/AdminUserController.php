<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Analytics\Ga4Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::orderByDesc('is_admin')
        // ->orderBy('name')
        ->latest()
        ->get();

        return view('admin.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
            'is_admin' => ['boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()->route('admin.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $accounts = Ga4Service::listProperties();
        $assignedAccountIds = $user->assignedAccountIds();

        return view('admin.edit', compact('user', 'accounts', 'assignedAccountIds'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Rules\Password::defaults()],
            'is_admin' => ['boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_admin = $request->boolean('is_admin');

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync assigned accounts
        $selectedAccountIds = $request->input('assigned_accounts', []);
        $allAccounts = Ga4Service::listProperties();

        // Build a lookup of account ID → display name
        $accountNames = [];
        foreach ($allAccounts as $account) {
            $accountNames[$account['name']] = $account['displayName'];
        }

        DB::table('account_user')->where('user_id', $user->id)->delete();

        $rows = [];
        foreach ($selectedAccountIds as $accountId) {
            $rows[] = [
                'user_id' => $user->id,
                'ga_account_id' => $accountId,
                'ga_account_name' => $accountNames[$accountId] ?? $accountId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rows)) {
            DB::table('account_user')->insert($rows);
        }

        return redirect()->route('admin.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.index')->with('success', 'User deleted successfully.');
    }
}
