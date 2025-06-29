// Member Search Autocomplete
class MemberSearchAutocomplete {
    constructor(inputElement, options = {}) {
        this.input = inputElement;
        this.options = {
            minLength: 2,
            delay: 300,
            maxResults: 10,
            apiUrl: '/api/members/search',
            onSelect: null,
            ...options
        };
        
        this.timeout = null;
        this.currentRequest = null;
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        this.createDropdown();
        this.bindEvents();
    }
    
    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto hidden';
        this.dropdown.style.top = '100%';
        this.dropdown.style.left = '0';
        
        // Make input container relative
        this.input.parentElement.style.position = 'relative';
        this.input.parentElement.appendChild(this.dropdown);
    }
    
    bindEvents() {
        this.input.addEventListener('input', (e) => {
            this.handleInput(e.target.value);
        });
        
        this.input.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
        
        this.input.addEventListener('blur', (e) => {
            // Delay hiding to allow click on dropdown
            setTimeout(() => this.hide(), 150);
        });
        
        this.input.addEventListener('focus', (e) => {
            if (e.target.value.length >= this.options.minLength) {
                this.handleInput(e.target.value);
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target) && !this.dropdown.contains(e.target)) {
                this.hide();
            }
        });
    }
    
    handleInput(value) {
        clearTimeout(this.timeout);
        
        if (value.length < this.options.minLength) {
            this.hide();
            return;
        }
        
        this.timeout = setTimeout(() => {
            this.search(value);
        }, this.options.delay);
    }
    
    handleKeydown(e) {
        if (!this.isOpen) return;
        
        const items = this.dropdown.querySelectorAll('.search-item');
        const current = this.dropdown.querySelector('.search-item.highlighted');
        let index = Array.from(items).indexOf(current);
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                index = Math.min(index + 1, items.length - 1);
                this.highlightItem(items[index]);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                index = Math.max(index - 1, 0);
                this.highlightItem(items[index]);
                break;
                
            case 'Enter':
                e.preventDefault();
                if (current) {
                    this.selectItem(current);
                }
                break;
                
            case 'Escape':
                this.hide();
                break;
        }
    }
    
    async search(query) {
        // Cancel previous request
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
        
        const controller = new AbortController();
        this.currentRequest = controller;
        
        try {
            const response = await fetch(`${this.options.apiUrl}?q=${encodeURIComponent(query)}&limit=${this.options.maxResults}`, {
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (!response.ok) throw new Error('Search failed');
            
            const results = await response.json();
            this.displayResults(results);
            
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Search error:', error);
                this.hide();
            }
        } finally {
            this.currentRequest = null;
        }
    }
    
    displayResults(results) {
        if (results.length === 0) {
            this.dropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada anggota ditemukan</div>';
            this.show();
            return;
        }
        
        const html = results.map((item, index) => `
            <div class="search-item px-4 py-2 cursor-pointer hover:bg-gray-100 ${index === 0 ? 'highlighted bg-gray-100' : ''}" 
                 data-id="${item.id}" 
                 data-member-id="${item.member_id}"
                 data-name="${item.name}"
                 data-email="${item.email}">
                <div class="font-medium text-gray-900">${this.highlightMatch(item.name, this.input.value)}</div>
                <div class="text-sm text-gray-500">${this.highlightMatch(item.member_id, this.input.value)} • ${this.highlightMatch(item.email, this.input.value)}</div>
            </div>
        `).join('');
        
        this.dropdown.innerHTML = html;
        
        // Bind click events
        this.dropdown.querySelectorAll('.search-item').forEach(item => {
            item.addEventListener('click', () => this.selectItem(item));
        });
        
        this.show();
    }
    
    highlightMatch(text, query) {
        if (!query) return text;
        
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
    }
    
    highlightItem(item) {
        // Remove previous highlight
        this.dropdown.querySelectorAll('.search-item').forEach(el => {
            el.classList.remove('highlighted', 'bg-gray-100');
        });
        
        // Add highlight to current item
        if (item) {
            item.classList.add('highlighted', 'bg-gray-100');
            item.scrollIntoView({ block: 'nearest' });
        }
    }
    
    selectItem(item) {
        const data = {
            id: item.dataset.id,
            member_id: item.dataset.memberId,
            name: item.dataset.name,
            email: item.dataset.email
        };
        
        // Set input value to member name
        this.input.value = data.name;
        
        // Call onSelect callback if provided
        if (this.options.onSelect) {
            this.options.onSelect(data);
        }
        
        this.hide();
    }
    
    show() {
        this.dropdown.classList.remove('hidden');
        this.isOpen = true;
    }
    
    hide() {
        this.dropdown.classList.add('hidden');
        this.isOpen = false;
    }
    
    destroy() {
        if (this.dropdown && this.dropdown.parentElement) {
            this.dropdown.parentElement.removeChild(this.dropdown);
        }
        
        clearTimeout(this.timeout);
        
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
    }
}

// Initialize autocomplete on page load
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('[data-autocomplete="members"]');
    
    searchInputs.forEach(input => {
        new MemberSearchAutocomplete(input, {
            onSelect: (data) => {
                console.log('Selected member:', data);
                // You can add custom logic here
                // For example, redirect to member detail page
                // window.location.href = `/members/${data.id}`;
            }
        });
    });
});

// Export for use in other modules
window.MemberSearchAutocomplete = MemberSearchAutocomplete;
