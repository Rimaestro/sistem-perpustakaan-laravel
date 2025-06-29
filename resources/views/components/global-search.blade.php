@props([
    'placeholder' => 'Cari buku, anggota...',
    'showSuggestions' => true,
    'showFilters' => false,
])

<div class="relative" x-data="globalSearch()" x-init="init()">
    <!-- Search Input -->
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input
            type="text"
            x-model="query"
            x-on:input.debounce.300ms="search()"
            x-on:focus="showDropdown = true"
            x-on:keydown.escape="hideDropdown()"
            x-on:keydown.arrow-down.prevent="navigateDown()"
            x-on:keydown.arrow-up.prevent="navigateUp()"
            x-on:keydown.enter.prevent="selectCurrent()"
            placeholder="{{ $placeholder }}"
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            autocomplete="off"
        >
        
        <!-- Loading Spinner -->
        <div x-show="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    <!-- Search Dropdown -->
    <div 
        x-show="showDropdown && (results.length > 0 || suggestions.length > 0 || query.length >= 2)"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-96 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
        x-on:click.away="hideDropdown()"
    >
        <!-- Search Results -->
        <template x-if="results.length > 0">
            <div>
                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-gray-50">
                    Hasil Pencarian
                </div>
                <template x-for="(result, index) in results" :key="result.type + '-' + result.id">
                    <div 
                        x-on:click="selectResult(result)"
                        :class="{'bg-blue-50': selectedIndex === index}"
                        class="cursor-pointer px-3 py-2 hover:bg-gray-100 border-b border-gray-100 last:border-b-0"
                    >
                        <div class="flex items-center space-x-3">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <template x-if="result.icon === 'book'">
                                    <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </template>
                                <template x-if="result.icon === 'user'">
                                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </template>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900" x-text="result.title"></div>
                                <div class="text-sm text-gray-500" x-text="result.subtitle"></div>
                                <template x-if="result.category">
                                    <div class="text-xs text-gray-400" x-text="result.category"></div>
                                </template>
                            </div>
                            
                            <!-- Status Badge -->
                            <template x-if="result.status">
                                <div class="flex-shrink-0">
                                    <span 
                                        :class="{
                                            'bg-green-100 text-green-800': result.status === 'available' || result.status === 'active',
                                            'bg-red-100 text-red-800': result.status === 'borrowed' || result.status === 'inactive',
                                            'bg-yellow-100 text-yellow-800': result.status === 'maintenance' || result.status === 'suspended',
                                            'bg-gray-100 text-gray-800': result.status === 'lost'
                                        }"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                    >
                                        <template x-if="result.type === 'book'">
                                            <span x-text="getBookStatusLabel(result.status)"></span>
                                        </template>
                                        <template x-if="result.type === 'member'">
                                            <span x-text="getMemberStatusLabel(result.status)"></span>
                                        </template>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Search Suggestions -->
        <template x-if="suggestions.length > 0 && query.length < 2">
            <div>
                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide bg-gray-50">
                    Pencarian Populer
                </div>
                <template x-for="(suggestion, index) in suggestions" :key="'suggestion-' + index">
                    <div 
                        x-on:click="applySuggestion(suggestion.text)"
                        class="cursor-pointer px-3 py-2 hover:bg-gray-100 border-b border-gray-100 last:border-b-0"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <template x-if="suggestion.icon === 'book'">
                                    <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </template>
                                <template x-if="suggestion.icon === 'user'">
                                    <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </template>
                            </div>
                            <div class="text-sm text-gray-700" x-text="suggestion.text"></div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- No Results -->
        <template x-if="query.length >= 2 && results.length === 0 && !loading">
            <div class="px-3 py-4 text-center text-sm text-gray-500">
                <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <p>Tidak ada hasil untuk "<span x-text="query"></span>"</p>
                <p class="text-xs text-gray-400 mt-1">Coba kata kunci yang berbeda</p>
            </div>
        </template>

        <!-- Advanced Search Link -->
        <template x-if="query.length >= 2">
            <div class="border-t border-gray-100 px-3 py-2">
                <a 
                    :href="'/search?search=' + encodeURIComponent(query)"
                    class="flex items-center text-sm text-blue-600 hover:text-blue-800"
                >
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Pencarian lanjutan untuk "<span x-text="query"></span>"
                </a>
            </div>
        </template>
    </div>
</div>

<script>
function globalSearch() {
    return {
        query: '',
        results: [],
        suggestions: [],
        showDropdown: false,
        loading: false,
        selectedIndex: -1,
        
        init() {
            if ({{ $showSuggestions ? 'true' : 'false' }}) {
                this.loadSuggestions();
            }
        },
        
        async search() {
            if (this.query.length < 2) {
                this.results = [];
                this.showDropdown = this.suggestions.length > 0;
                return;
            }
            
            this.loading = true;
            this.selectedIndex = -1;
            
            try {
                const response = await fetch(`/api/search/global?q=${encodeURIComponent(this.query)}&limit=8`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    this.results = await response.json();
                    this.showDropdown = true;
                }
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async loadSuggestions() {
            try {
                const response = await fetch('/api/search/suggestions', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    this.suggestions = await response.json();
                }
            } catch (error) {
                console.error('Suggestions error:', error);
            }
        },
        
        selectResult(result) {
            window.location.href = result.url;
        },
        
        applySuggestion(text) {
            this.query = text;
            this.search();
        },
        
        hideDropdown() {
            this.showDropdown = false;
            this.selectedIndex = -1;
        },
        
        navigateDown() {
            if (this.selectedIndex < this.results.length - 1) {
                this.selectedIndex++;
            }
        },
        
        navigateUp() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
            }
        },
        
        selectCurrent() {
            if (this.selectedIndex >= 0 && this.results[this.selectedIndex]) {
                this.selectResult(this.results[this.selectedIndex]);
            } else if (this.query.length >= 2) {
                window.location.href = `/search?search=${encodeURIComponent(this.query)}`;
            }
        },
        
        getBookStatusLabel(status) {
            const labels = {
                'available': 'Tersedia',
                'borrowed': 'Dipinjam',
                'maintenance': 'Maintenance',
                'lost': 'Hilang'
            };
            return labels[status] || status;
        },
        
        getMemberStatusLabel(status) {
            const labels = {
                'active': 'Aktif',
                'inactive': 'Tidak Aktif',
                'suspended': 'Ditangguhkan'
            };
            return labels[status] || status;
        }
    }
}
</script>
