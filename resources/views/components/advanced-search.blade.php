@props([
    'action' => '',
    'method' => 'GET',
    'title' => 'Pencarian Lanjutan',
    'collapsible' => true,
])

<div class="bg-white shadow rounded-lg">
    @if($collapsible)
        <div class="px-4 py-3 border-b border-gray-200">
            <button 
                type="button" 
                class="flex items-center justify-between w-full text-left"
                onclick="toggleAdvancedSearch()"
            >
                <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
                <svg id="search-chevron" class="w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
        <div id="advanced-search-content" class="px-4 py-4 {{ request()->hasAny(['search', 'category_id', 'status', 'year', 'author']) ? '' : 'hidden' }}">
    @else
        <div class="px-4 py-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $title }}</h3>
    @endif
        
        <form method="{{ $method }}" action="{{ $action }}" class="space-y-4">
            {{ $slot }}
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-4 border-t border-gray-200">
                <div class="flex space-x-2">
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Cari
                    </button>
                    <a 
                        href="{{ $action }}" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Reset
                    </a>
                </div>
                
                <!-- Results count -->
                @if(isset($resultsCount))
                    <div class="mt-4 sm:mt-0">
                        <span class="text-sm text-gray-500">
                            Ditemukan {{ $resultsCount }} hasil
                        </span>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

@if($collapsible)
<script>
function toggleAdvancedSearch() {
    const content = document.getElementById('advanced-search-content');
    const chevron = document.getElementById('search-chevron');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        chevron.classList.remove('rotate-180');
    }
}

// Auto-expand if there are search parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const hasSearchParams = urlParams.has('search') || urlParams.has('category_id') || urlParams.has('status') || urlParams.has('year') || urlParams.has('author');
    
    if (hasSearchParams) {
        const content = document.getElementById('advanced-search-content');
        const chevron = document.getElementById('search-chevron');
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
    }
});
</script>
@endif
