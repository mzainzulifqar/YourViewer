<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Property</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
@include('layouts.navigation')

<div class="max-w-[1400px] mx-auto px-6 py-8" x-cloak x-data="{
    search: '',
    accounts: @js($accounts),
    get query() { return this.search.trim() },
    get showResults() { return this.query.length >= 3 },
    matchesSearch(property) {
        const q = this.query.toLowerCase();
        return property.name.toLowerCase().includes(q) || property.id.toString().includes(q);
    },
    accountHasMatch(account) {
        return account.properties.some(p => this.matchesSearch(p));
    },
    get totalMatches() {
        if (!this.showResults) return 0;
        return this.accounts.reduce((sum, a) => sum + a.properties.filter(p => this.matchesSearch(p)).length, 0);
    }
}">

    {{-- Search bar at top --}}
    <div class="max-w-xl mx-auto">
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 transition-colors duration-200" :class="showResults ? 'text-blue-400' : 'text-gray-400'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input x-model="search" type="text" placeholder="Search properties..." autofocus
                   class="w-full pl-12 pr-4 py-3.5 text-base bg-white border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-all" />
        </div>
        <p class="text-xs text-gray-500 text-center mt-2.5 h-4 transition-opacity duration-200"
           :class="query.length > 0 && query.length < 3 ? 'opacity-100' : 'opacity-0'">
            Type at least 3 characters to search
        </p>
    </div>

    {{-- Empty state — shown when not searching --}}
    <div class="max-w-md mx-auto text-center pt-24 transition-opacity duration-300"
         :class="showResults ? 'opacity-0 pointer-events-none absolute' : 'opacity-100'">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Select a Property</h2>
        <p class="text-sm text-gray-400">Start typing to find a GA4 property.</p>
    </div>

    {{-- Results --}}
    <div class="pt-4 pb-12 transition-opacity duration-300"
         :class="showResults ? 'opacity-100' : 'opacity-0 pointer-events-none h-0 overflow-hidden'">

        <template x-for="account in accounts" :key="account.name">
            <div class="mb-10" x-show="accountHasMatch(account)">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide" x-text="account.displayName"></h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="property in account.properties" :key="property.id">
                        <a :href="'/analytics/' + property.id"
                           x-show="matchesSearch(property)"
                           class="flex items-start justify-between bg-white border border-gray-200 rounded-lg px-5 py-5 hover:border-blue-400 hover:shadow-md transition-all group">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0 group-hover:bg-blue-100 transition-colors">
                                        <svg class="w-4.5 h-4.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors truncate" x-text="property.name"></p>
                                        <p class="text-xs text-gray-400 mt-0.5" x-text="'ID: ' + property.id"></p>
                                    </div>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-200 group-hover:text-blue-500 transition-colors shrink-0 mt-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    </template>
                </div>
            </div>
        </template>

        {{-- No search results --}}
        <div x-show="showResults && totalMatches === 0" class="text-center py-12">
            <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <p class="text-sm text-gray-400">No properties match your search.</p>
        </div>
    </div>

</div>

</body>
</html>
