// Enhanced Search System
// Provides advanced search functionality with filters, suggestions, and analytics

class EnhancedSearch {
    constructor(options = {}) {
        this.options = {
            apiBaseUrl: '/api/search',
            debounceDelay: 300,
            minSearchLength: 2,
            maxResults: 10,
            enableAnalytics: true,
            enableHistory: true,
            ...options
        };
        
        this.searchHistory = this.loadSearchHistory();
        this.popularSearches = [];
        this.currentRequest = null;
        
        this.init();
    }
    
    init() {
        this.loadPopularSearches();
        this.bindGlobalEvents();
    }
    
    // Load popular searches from cache
    async loadPopularSearches() {
        try {
            const response = await fetch(`${this.options.apiBaseUrl}/suggestions`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                this.popularSearches = await response.json();
            }
        } catch (error) {
            console.error('Error loading popular searches:', error);
        }
    }
    
    // Enhanced search with filters and analytics
    async search(query, filters = {}, type = 'all') {
        if (query.length < this.options.minSearchLength && Object.keys(filters).length === 0) {
            return [];
        }
        
        // Cancel previous request
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
        
        const controller = new AbortController();
        this.currentRequest = controller;
        
        try {
            const params = new URLSearchParams({
                q: query,
                type: type,
                limit: this.options.maxResults,
                ...filters
            });
            
            const response = await fetch(`${this.options.apiBaseUrl}/global?${params}`, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (!response.ok) throw new Error('Search failed');
            
            const results = await response.json();
            
            // Track search analytics
            if (this.options.enableAnalytics && query.length >= this.options.minSearchLength) {
                this.trackSearch(query, type, results.length);
            }
            
            // Add to search history
            if (this.options.enableHistory && query.length >= this.options.minSearchLength) {
                this.addToHistory(query, type);
            }
            
            return results;
            
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Search error:', error);
            }
            return [];
        }
    }
    
    // Get search suggestions based on query
    async getSuggestions(query = '', type = 'all') {
        try {
            const params = new URLSearchParams({
                q: query,
                type: type
            });
            
            const response = await fetch(`${this.options.apiBaseUrl}/suggestions?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                const suggestions = await response.json();
                
                // Combine with search history
                const historySuggestions = this.getHistorySuggestions(query, type);
                
                return this.mergeSuggestions(suggestions, historySuggestions);
            }
        } catch (error) {
            console.error('Error getting suggestions:', error);
        }
        
        return [];
    }
    
    // Get filter options for advanced search
    async getFilterOptions(type = 'books') {
        try {
            const response = await fetch(`${this.options.apiBaseUrl}/filter-options?type=${type}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                return await response.json();
            }
        } catch (error) {
            console.error('Error getting filter options:', error);
        }
        
        return {};
    }
    
    // Advanced search with pagination
    async advancedSearch(query, filters = {}, type = 'books', page = 1, perPage = 15) {
        try {
            const params = new URLSearchParams({
                search: query,
                type: type,
                page: page,
                per_page: perPage,
                ...this.flattenFilters(filters)
            });
            
            const response = await fetch(`${this.options.apiBaseUrl}/advanced?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                
                // Track search analytics
                if (this.options.enableAnalytics && query.length >= this.options.minSearchLength) {
                    this.trackSearch(query, type, data.pagination?.total || 0);
                }
                
                return data;
            }
        } catch (error) {
            console.error('Advanced search error:', error);
        }
        
        return { data: [], pagination: null };
    }
    
    // Flatten filters for URL parameters
    flattenFilters(filters) {
        const flattened = {};
        Object.keys(filters).forEach(key => {
            if (filters[key] && filters[key] !== '') {
                flattened[`filters[${key}]`] = filters[key];
            }
        });
        return flattened;
    }
    
    // Search history management
    addToHistory(query, type) {
        const historyItem = {
            query: query,
            type: type,
            timestamp: Date.now()
        };
        
        // Remove duplicates
        this.searchHistory = this.searchHistory.filter(item => 
            !(item.query === query && item.type === type)
        );
        
        // Add to beginning
        this.searchHistory.unshift(historyItem);
        
        // Limit history size
        this.searchHistory = this.searchHistory.slice(0, 50);
        
        // Save to localStorage
        this.saveSearchHistory();
    }
    
    getHistorySuggestions(query, type) {
        return this.searchHistory
            .filter(item => {
                const matchesType = type === 'all' || item.type === type;
                const matchesQuery = query === '' || item.query.toLowerCase().includes(query.toLowerCase());
                return matchesType && matchesQuery;
            })
            .slice(0, 5)
            .map(item => ({
                text: item.query,
                type: item.type,
                icon: item.type === 'books' ? 'book' : 'user',
                source: 'history'
            }));
    }
    
    loadSearchHistory() {
        try {
            const history = localStorage.getItem('library_search_history');
            return history ? JSON.parse(history) : [];
        } catch (error) {
            console.error('Error loading search history:', error);
            return [];
        }
    }
    
    saveSearchHistory() {
        try {
            localStorage.setItem('library_search_history', JSON.stringify(this.searchHistory));
        } catch (error) {
            console.error('Error saving search history:', error);
        }
    }
    
    clearSearchHistory() {
        this.searchHistory = [];
        this.saveSearchHistory();
    }
    
    // Merge suggestions from different sources
    mergeSuggestions(apiSuggestions, historySuggestions) {
        const merged = [...historySuggestions];
        
        // Add API suggestions that aren't already in history
        apiSuggestions.forEach(suggestion => {
            if (!merged.some(item => item.text === suggestion.text)) {
                merged.push(suggestion);
            }
        });
        
        return merged.slice(0, 10);
    }
    
    // Search analytics tracking
    trackSearch(query, type, resultCount) {
        if (!this.options.enableAnalytics) return;
        
        const analyticsData = {
            query: query,
            type: type,
            result_count: resultCount,
            timestamp: Date.now(),
            user_agent: navigator.userAgent,
            page_url: window.location.href
        };
        
        // Store in localStorage for now (could be sent to server)
        this.saveAnalytics(analyticsData);
    }
    
    saveAnalytics(data) {
        try {
            let analytics = JSON.parse(localStorage.getItem('library_search_analytics') || '[]');
            analytics.push(data);
            
            // Keep only last 100 entries
            analytics = analytics.slice(-100);
            
            localStorage.setItem('library_search_analytics', JSON.stringify(analytics));
        } catch (error) {
            console.error('Error saving analytics:', error);
        }
    }
    
    getAnalytics() {
        try {
            return JSON.parse(localStorage.getItem('library_search_analytics') || '[]');
        } catch (error) {
            console.error('Error getting analytics:', error);
            return [];
        }
    }
    
    // Utility methods
    highlightMatch(text, query) {
        if (!query || query.length < 2) return text;
        
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
    }
    
    debounce(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }
    
    // Global event bindings
    bindGlobalEvents() {
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K for global search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.focusGlobalSearch();
            }
        });
    }
    
    focusGlobalSearch() {
        const searchInput = document.querySelector('[x-data*="globalSearch"] input');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Cleanup
    destroy() {
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
    }
}

// Initialize enhanced search system
document.addEventListener('DOMContentLoaded', function() {
    window.enhancedSearch = new EnhancedSearch();
});

// Export for use in other modules
window.EnhancedSearch = EnhancedSearch;
