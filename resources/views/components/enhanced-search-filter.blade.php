@props([
    'action' => '',
    'method' => 'GET',
    'title' => 'Pencarian & Filter',
    'type' => 'books', // books, members, all
    'collapsible' => true,
    'showQuickFilters' => true,
    'showSuggestions' => true,
])

<div class="bg-white shadow rounded-lg" x-data="enhancedSearchFilter('{{ $type }}')" x-init="init()">
    @if($collapsible)
        <div class="px-4 py-3 border-b border-gray-200">
            <button 
                type="button" 
                class="flex items-center justify-between w-full text-left"
                x-on:click="toggleCollapsed()"
            >
                <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
                <div class="flex items-center space-x-2">
                    <!-- Results count -->
                    <span x-show="resultsCount > 0" class="text-sm text-gray-500" x-text="`${resultsCount} hasil`"></span>
                    <!-- Chevron -->
                    <svg 
                        x-bind:class="{ 'rotate-180': !collapsed }"
                        class="w-5 h-5 text-gray-400 transform transition-transform duration-200" 
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </button>
        </div>
        <div x-show="!collapsed" x-transition class="px-4 py-4">
    @else
        <div class="px-4 py-4">
    @endif
    
    <form method="{{ $method }}" action="{{ $action }}" x-on:submit.prevent="performSearch()">
        <div class="space-y-4">
            <!-- Main Search Input -->
            <div class="relative">
                <label for="enhanced-search" class="block text-sm font-medium text-gray-700 mb-1">
                    Cari {{ $type === 'books' ? 'Buku' : ($type === 'members' ? 'Anggota' : 'Buku & Anggota') }}
                </label>
                <div class="relative">
                    <input
                        type="text"
                        id="enhanced-search"
                        name="search"
                        x-model="searchQuery"
                        x-on:input.debounce.300ms="performSearch()"
                        x-on:focus="showSuggestions = true"
                        x-on:blur="hideSuggestions()"
                        placeholder="Masukkan kata kunci pencarian..."
                        class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        autocomplete="off"
                    >
                    
                    <!-- Search Icon -->
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    
                    <!-- Clear Button -->
                    <button
                        type="button"
                        x-show="searchQuery.length > 0"
                        x-on:click="clearSearch()"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                    >
                        <svg class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Search Suggestions Dropdown -->
                <div 
                    x-show="showSuggestions && suggestions.length > 0"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                >
                    <template x-for="(suggestion, index) in suggestions" :key="index">
                        <div 
                            x-on:mousedown.prevent="applySuggestion(suggestion.text)"
                            class="cursor-pointer px-3 py-2 hover:bg-gray-100"
                        >
                            <div class="flex items-center space-x-2">
                                <svg x-show="suggestion.source === 'history'" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <svg x-show="suggestion.icon === 'book'" class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <svg x-show="suggestion.icon === 'user'" class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="text-sm text-gray-700" x-text="suggestion.text"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            @if($showQuickFilters)
            <!-- Quick Filters -->
            <div x-show="filterOptions && Object.keys(filterOptions).length > 0" class="space-y-3">
                <h4 class="text-sm font-medium text-gray-900">Filter Cepat</h4>
                
                <!-- Book Filters -->
                <div x-show="searchType === 'books' || searchType === 'all'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Category -->
                    <div x-show="filterOptions.categories && filterOptions.categories.length > 0">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Kategori</label>
                        <select 
                            x-model="filters.category_id"
                            x-on:change="performSearch()"
                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Semua</option>
                            <template x-for="category in filterOptions.categories" :key="category.value">
                                <option :value="category.value" x-text="category.label"></option>
                            </template>
                        </select>
                    </div>
                    
                    <!-- Status -->
                    <div x-show="filterOptions.statuses && filterOptions.statuses.length > 0">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select 
                            x-model="filters.status"
                            x-on:change="performSearch()"
                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Semua</option>
                            <template x-for="status in filterOptions.statuses" :key="status.value">
                                <option :value="status.value" x-text="status.label"></option>
                            </template>
                        </select>
                    </div>
                    
                    <!-- Author -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Penulis</label>
                        <input
                            type="text"
                            x-model="filters.author"
                            x-on:input.debounce.500ms="performSearch()"
                            placeholder="Nama penulis..."
                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    
                    <!-- Year -->
                    <div x-show="filterOptions.years && filterOptions.years.length > 0">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tahun</label>
                        <select 
                            x-model="filters.year_from"
                            x-on:change="performSearch()"
                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Semua</option>
                            <template x-for="year in filterOptions.years" :key="year.value">
                                <option :value="year.value" x-text="year.label"></option>
                            </template>
                        </select>
                    </div>
                </div>
                
                <!-- Member Filters -->
                <div x-show="searchType === 'members'" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Member Status -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status Anggota</label>
                        <select 
                            x-model="filters.member_status"
                            x-on:change="performSearch()"
                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Semua</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="suspended">Ditangguhkan</option>
                        </select>
                    </div>
                    
                    <!-- Join Date From -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Bergabung Dari</label>
                        <input
                            type="date"
                            x-model="filters.join_from"
                            x-on:change="performSearch()"
                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    
                    <!-- Join Date To -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Bergabung Sampai</label>
                        <input
                            type="date"
                            x-model="filters.join_to"
                            x-on:change="performSearch()"
                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Cari
                    </button>
                    
                    <button 
                        type="button"
                        x-on:click="resetFilters()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset
                    </button>
                    
                    @if($showSuggestions)
                    <button 
                        type="button"
                        x-on:click="clearSearchHistory()"
                        class="text-sm text-gray-500 hover:text-gray-700"
                        title="Hapus riwayat pencarian"
                    >
                        Hapus Riwayat
                    </button>
                    @endif
                </div>
                
                <div class="text-sm text-gray-600">
                    <span x-show="resultsCount > 0" x-text="`Menampilkan ${resultsCount} hasil`"></span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Mencari...
                    </span>
                </div>
            </div>
        </div>
    </form>
    
    @if($collapsible)
        </div>
    @else
        </div>
    @endif
</div>

<script>
function enhancedSearchFilter(type = 'books') {
    return {
        searchType: type,
        searchQuery: '{{ request("search", "") }}',
        filters: {
            category_id: '{{ request("category_id", "") }}',
            author: '{{ request("author", "") }}',
            status: '{{ request("status", "") }}',
            year_from: '{{ request("year_from", "") }}',
            year_to: '{{ request("year_to", "") }}',
            member_status: '{{ request("member_status", "") }}',
            join_from: '{{ request("join_from", "") }}',
            join_to: '{{ request("join_to", "") }}'
        },
        filterOptions: {},
        suggestions: [],
        showSuggestions: false,
        collapsed: false,
        loading: false,
        resultsCount: 0,
        
        init() {
            this.loadFilterOptions();
            this.loadSuggestions();
            
            // Auto-expand if there are active filters
            this.collapsed = !this.hasActiveFilters();
        },
        
        async loadFilterOptions() {
            if (window.enhancedSearch) {
                this.filterOptions = await window.enhancedSearch.getFilterOptions(this.searchType);
            }
        },
        
        async loadSuggestions() {
            if (window.enhancedSearch) {
                this.suggestions = await window.enhancedSearch.getSuggestions(this.searchQuery, this.searchType);
            }
        },
        
        async performSearch() {
            this.loading = true;
            
            // Trigger form submission or custom search logic
            const form = this.$el.querySelector('form');
            if (form.action) {
                form.submit();
            } else {
                // Custom search logic here
                this.loading = false;
            }
        },
        
        applySuggestion(text) {
            this.searchQuery = text;
            this.showSuggestions = false;
            this.performSearch();
        },
        
        clearSearch() {
            this.searchQuery = '';
            this.performSearch();
        },
        
        resetFilters() {
            this.searchQuery = '';
            this.filters = {
                category_id: '',
                author: '',
                status: '',
                year_from: '',
                year_to: '',
                member_status: '',
                join_from: '',
                join_to: ''
            };
            this.performSearch();
        },
        
        clearSearchHistory() {
            if (window.enhancedSearch) {
                window.enhancedSearch.clearSearchHistory();
                this.loadSuggestions();
            }
        },
        
        toggleCollapsed() {
            this.collapsed = !this.collapsed;
        },
        
        hideSuggestions() {
            setTimeout(() => {
                this.showSuggestions = false;
            }, 200);
        },
        
        hasActiveFilters() {
            return this.searchQuery.length > 0 || Object.values(this.filters).some(value => value && value !== '');
        }
    }
}
</script>
