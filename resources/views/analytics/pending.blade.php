<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
@include('layouts.navigation')

<div class="max-w-lg mx-auto px-6 py-24 text-center">
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xl shadow-gray-200/50 p-10">
        <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Account Pending Approval</h1>
        <p class="text-sm text-gray-500 leading-relaxed">Your account has been created successfully. An administrator will assign you access to the appropriate analytics accounts shortly.</p>
        <p class="text-sm text-gray-400 mt-4">You'll be able to view your analytics once access has been granted.</p>
    </div>
</div>

</body>
</html>
