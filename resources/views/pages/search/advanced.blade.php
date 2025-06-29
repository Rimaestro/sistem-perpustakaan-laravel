<x-app-layout title="Pencarian Lanjutan">
    <div class="space-y-6" x-data="advancedSearch()" x-init="init()">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pencarian Lanjutan</h1>
                <p class="mt-1 text-sm text-gray-600">Cari buku dan anggota dengan filter yang lebih detail</p>
            </div>
        </div>

        <!-- Search Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filter Pencarian</h3>
            </div>
            
            <div class="px-6 py-4 space-y-6">
                <!-- Search Type Toggle -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Jenis Pencarian</label>
                    <div class="mt-2 flex space-x-4">
                        <label class="inline-flex items-center">
                            <input 
                                type="radio" 
                                x-model="searchType" 
                                value="books" 
                                x-on:change="loadFilterOptions()"
                                class="form-radio h-4 w-4 text-blue-600"
                            >
                            <span class="ml-2 text-sm text-gray-700">Buku</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input 
                                type="radio" 
                                x-model="searchType" 
                                value="members" 
                                x-on:change="loadFilterOptions()"
                                class="form-radio h-4 w-4 text-blue-600"
                            >
                            <span class="ml-2 text-sm text-gray-700">Anggota</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input 
                                type="radio" 
                                x-model="searchType" 
                                value="all" 
                                x-on:change="loadFilterOptions()"
                                class="form-radio h-4 w-4 text-blue-600"
                            >
                            <span class="ml-2 text-sm text-gray-700">Semua</span>
                        </label>
                    </div>
                </div>

                <!-- Main Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        Kata Kunci Pencarian
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="search"
                            x-model="searchQuery"
                            x-on:input.debounce.500ms="performSearch()"
                            placeholder="Masukkan kata kunci..."
                            class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Book Filters -->
                <div x-show="searchType === 'books' || searchType === 'all'" class="space-y-4">
                    <h4 class="text-md font-medium text-gray-900">Filter Buku</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select 
                                x-model="filters.category_id"
                                x-on:change="performSearch()"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                                <option value="">Semua Kategori</option>
                                <template x-for="category in filterOptions.categories" :key="category.value">
                                    <option :value="category.value" x-text="category.label"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Author Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Penulis</label>
                            <input
                                type="text"
                                x-model="filters.author"
                                x-on:input.debounce.500ms="performSearch()"
                                placeholder="Nama penulis..."
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select 
                                x-model="filters.status"
                                x-on:change="performSearch()"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                                <option value="">Semua Status</option>
                                <template x-for="status in filterOptions.statuses" :key="status.value">
                                    <option :value="status.value" x-text="status.label"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Year Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Dari</label>
                            <select 
                                x-model="filters.year_from"
                                x-on:change="performSearch()"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                                <option value="">Tahun Awal</option>
                                <template x-for="year in filterOptions.years" :key="year.value">
                                    <option :value="year.value" x-text="year.label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Sampai</label>
                            <select 
                                x-model="filters.year_to"
                                x-on:change="performSearch()"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                                <option value="">Tahun Akhir</option>
                                <template x-for="year in filterOptions.years" :key="year.value">
                                    <option :value="year.value" x-text="year.label"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Member Filters -->
                <div x-show="searchType === 'members' || searchType === 'all'" class="space-y-4">
                    <h4 class="text-md font-medium text-gray-900">Filter Anggota</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Member Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Anggota</label>
                            <select 
                                x-model="filters.member_status"
                                x-on:change="performSearch()"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                                <option value="suspended">Ditangguhkan</option>
                            </select>
                        </div>

                        <!-- Join Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bergabung Dari</label>
                            <input
                                type="date"
                                x-model="filters.join_from"
                                x-on:change="performSearch()"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bergabung Sampai</label>
                            <input
                                type="date"
                                x-model="filters.join_to"
                                x-on:change="performSearch()"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <button 
                        type="button"
                        x-on:click="resetFilters()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset Filter
                    </button>
                    
                    <div class="text-sm text-gray-600" x-show="searchResults.length > 0">
                        Ditemukan <span x-text="totalResults"></span> hasil
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div x-show="searchResults.length > 0 || loading" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Hasil Pencarian</h3>
            </div>
            
            <!-- Loading State -->
            <div x-show="loading" class="px-6 py-8 text-center">
                <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600">Mencari...</p>
            </div>
            
            <!-- Results List -->
            <div x-show="!loading && searchResults.length > 0" class="divide-y divide-gray-200">
                <template x-for="result in searchResults" :key="result.type + '-' + result.id">
                    <div class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center space-x-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <template x-if="result.type === 'book'">
                                    <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                </template>
                                <template x-if="result.type === 'member'">
                                    <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900 truncate" x-text="result.title"></h4>
                                    <template x-if="result.status">
                                        <span 
                                            :class="{
                                                'bg-green-100 text-green-800': result.status === 'available' || result.status === 'active',
                                                'bg-red-100 text-red-800': result.status === 'borrowed' || result.status === 'inactive',
                                                'bg-yellow-100 text-yellow-800': result.status === 'maintenance' || result.status === 'suspended',
                                                'bg-gray-100 text-gray-800': result.status === 'lost'
                                            }"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            x-text="getStatusLabel(result.type, result.status)"
                                        ></span>
                                    </template>
                                </div>
                                <p class="text-sm text-gray-500" x-text="result.subtitle"></p>
                                <template x-if="result.category">
                                    <p class="text-xs text-gray-400" x-text="result.category"></p>
                                </template>
                            </div>
                            
                            <!-- Action -->
                            <div class="flex-shrink-0">
                                <a 
                                    :href="result.url"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- No Results -->
            <div x-show="!loading && searchResults.length === 0 && (searchQuery.length > 0 || hasActiveFilters())" class="px-6 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada hasil ditemukan</h3>
                <p class="text-sm text-gray-500">Coba ubah kata kunci atau filter pencarian Anda.</p>
            </div>
        </div>
    </div>

    <script>
    function advancedSearch() {
        return {
            searchType: 'books',
            searchQuery: '{{ request("search", "") }}',
            filters: {
                category_id: '',
                author: '',
                status: '',
                year_from: '',
                year_to: '',
                member_status: '',
                join_from: '',
                join_to: ''
            },
            filterOptions: {
                categories: [],
                authors: [],
                years: [],
                statuses: []
            },
            searchResults: [],
            totalResults: 0,
            loading: false,

            init() {
                // Set initial search type based on URL or default
                const urlParams = new URLSearchParams(window.location.search);
                this.searchType = urlParams.get('type') || 'books';

                // Load filter options
                this.loadFilterOptions();

                // Perform initial search if there's a query
                if (this.searchQuery.length > 0) {
                    this.performSearch();
                }
            },

            async loadFilterOptions() {
                try {
                    const response = await fetch(`/api/search/filter-options?type=${this.searchType}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        this.filterOptions = await response.json();
                    }
                } catch (error) {
                    console.error('Error loading filter options:', error);
                }
            },

            async performSearch() {
                if (this.searchQuery.length === 0 && !this.hasActiveFilters()) {
                    this.searchResults = [];
                    return;
                }

                this.loading = true;

                try {
                    const params = new URLSearchParams({
                        type: this.searchType,
                        search: this.searchQuery,
                        ...this.getActiveFilters()
                    });

                    const response = await fetch(`/api/search/advanced?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.searchResults = data.data || [];
                        this.totalResults = data.pagination?.total || this.searchResults.length;
                    }
                } catch (error) {
                    console.error('Search error:', error);
                } finally {
                    this.loading = false;
                }
            },

            getActiveFilters() {
                const activeFilters = {};
                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key] && this.filters[key] !== '') {
                        activeFilters[`filters[${key}]`] = this.filters[key];
                    }
                });
                return activeFilters;
            },

            hasActiveFilters() {
                return Object.values(this.filters).some(value => value && value !== '');
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
                this.searchResults = [];
                this.totalResults = 0;
            },

            getStatusLabel(type, status) {
                if (type === 'book') {
                    const labels = {
                        'available': 'Tersedia',
                        'borrowed': 'Dipinjam',
                        'maintenance': 'Maintenance',
                        'lost': 'Hilang'
                    };
                    return labels[status] || status;
                } else if (type === 'member') {
                    const labels = {
                        'active': 'Aktif',
                        'inactive': 'Tidak Aktif',
                        'suspended': 'Ditangguhkan'
                    };
                    return labels[status] || status;
                }
                return status;
            }
        }
    }
    </script>
</x-app-layout>
