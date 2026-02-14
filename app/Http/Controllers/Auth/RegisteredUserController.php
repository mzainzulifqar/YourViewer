<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, function ($attribute, $value, $fail) {
                if (!str_ends_with($value, '@bbrown.com')) {
                    $fail('You are not authorized to register.');
                }
            }],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Auto-assign default GA accounts
        $this->assignDefaultAccounts($user);

        event(new Registered($user));

        Auth::login($user);

        return redirect('/');
    }

    private function assignDefaultAccounts(User $user): void
    {
        $defaults = [
            'accounts/182759207' => 'mba-ga-126-150@mojosolo.com',
            'accounts/312592881' => 'Live MBA Apps 01',
            'accounts/374917289' => 'Live MBA Apps 02',
            'accounts/374920471' => 'Live MBA Apps 03',
            'accounts/374909137' => 'Live MBA Apps 04',
            'accounts/67381713'  => 'myBenefitsApp',
        ];

        $rows = [];
        foreach ($defaults as $id => $name) {
            $rows[] = [
                'user_id' => $user->id,
                'ga_account_id' => $id,
                'ga_account_name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('account_user')->insert($rows);
    }
}
