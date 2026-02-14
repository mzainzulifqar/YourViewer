<nav class="bg-white border-b border-gray-200">
    <div class="max-w-[1400px] mx-auto px-6">
        <div class="flex items-center justify-between h-14">
            {{-- Left: Brand + Links --}}
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm shadow-blue-500/25">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    </div>
                    <span class="font-bold text-gray-900 text-sm">{{ config('app.name') }}</span>
                </a>
                <div class="flex items-center gap-1 text-sm">
                    <a href="{{ route('dashboard') }}"
                       class="px-3 py-1.5 rounded-md font-medium transition-colors {{ request()->routeIs('dashboard') || request()->is('analytics*') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                        Dashboard
                    </a>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.index') }}"
                           class="px-3 py-1.5 rounded-md font-medium transition-colors {{ request()->routeIs('admin.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                            Users
                        </a>
                    @endif
                </div>
            </div>

            {{-- Right: Profile + Logout --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('profile.edit') }}"
                   class="text-sm font-medium transition-colors {{ request()->routeIs('profile.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ auth()->user()->name }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>
