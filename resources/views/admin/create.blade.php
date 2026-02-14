<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add User</title>
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
        <h2 class="text-xl font-bold text-gray-900 mb-6">Add User</h2>

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="mb-5">
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" type="text" :value="old('name')" required autofocus placeholder="Full name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mb-5">
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" :value="old('email')" required placeholder="user@example.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-5">
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" name="password" type="password" required placeholder="Min 8 characters" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-500 focus:ring-blue-400 accent-blue-500">
                    <span class="ms-2 text-sm font-medium text-gray-700">Admin privileges</span>
                </label>
            </div>

            <x-primary-button>Create User</x-primary-button>
        </form>
    </div>
</div>

</body>
</html>
