<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit User</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
@include('layouts.navigation')

<div class="max-w-2xl mx-auto px-6 py-8">

    {{-- Back --}}
    <a href="{{ route('admin.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Back to users
    </a>

    {{-- Card --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xl shadow-gray-200/50 p-7">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Edit User</h2>

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus placeholder="Full name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mb-5">
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required placeholder="user@example.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-5">
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" name="password" type="password" placeholder="Leave blank to keep current" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-500 focus:ring-blue-400 accent-blue-500">
                    <span class="ms-2 text-sm font-medium text-gray-700">Admin privileges</span>
                </label>
            </div>

            {{-- Assigned Accounts --}}
            @if(!empty($accounts))
                <div class="mb-6">
                    <x-input-label value="Assigned Accounts" class="mb-3" />
                    <p class="text-xs text-gray-400 mb-3">Select which GA4 accounts this user can access. Admins can always access all accounts.</p>
                    <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
                        @foreach($accounts as $account)
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox"
                                       name="assigned_accounts[]"
                                       value="{{ $account['name'] }}"
                                       {{ in_array($account['name'], old('assigned_accounts', $assignedAccountIds)) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-blue-500 focus:ring-blue-400 accent-blue-500">
                                <span class="text-sm text-gray-700">{{ $account['displayName'] }}</span>
                                <span class="text-xs text-gray-400">({{ count($account['properties']) }} {{ Str::plural('property', count($account['properties'])) }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <x-primary-button>Update User</x-primary-button>
        </form>
    </div>
</div>

</body>
</html>
