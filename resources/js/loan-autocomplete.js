// Loan Autocomplete for Books and Members
class LoanAutocomplete {
    constructor(inputElement, options = {}) {
        this.input = inputElement;
        this.options = {
            minLength: 2,
            delay: 300,
            maxResults: 10,
            type: 'books', // 'books' or 'members'
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
        
        const items = this.dropdown.querySelectorAll('.autocomplete-item');
        const current = this.dropdown.querySelector('.autocomplete-item.highlighted');
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
        
        const apiUrl = this.options.type === 'books' 
            ? '/api/loans/search-books' 
            : '/api/loans/search-members';
        
        try {
            const response = await fetch(`${apiUrl}?q=${encodeURIComponent(query)}&limit=${this.options.maxResults}`, {
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
            const emptyMessage = this.options.type === 'books' 
                ? 'Tidak ada buku ditemukan' 
                : 'Tidak ada anggota ditemukan';
            this.dropdown.innerHTML = `<div class="px-4 py-2 text-sm text-gray-500">${emptyMessage}</div>`;
            this.show();
            return;
        }
        
        const html = results.map((item, index) => {
            if (this.options.type === 'books') {
                return this.renderBookItem(item, index === 0);
            } else {
                return this.renderMemberItem(item, index === 0);
            }
        }).join('');
        
        this.dropdown.innerHTML = html;
        
        // Bind click events
        this.dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', () => this.selectItem(item));
        });
        
        this.show();
    }
    
    renderBookItem(book, isFirst) {
        return `
            <div class="autocomplete-item px-4 py-3 cursor-pointer hover:bg-gray-100 ${isFirst ? 'highlighted bg-gray-100' : ''}" 
                 data-id="${book.id}" 
                 data-title="${book.title}"
                 data-author="${book.author}"
                 data-barcode="${book.barcode}"
                 data-available="${book.available_quantity}">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900">${this.highlightMatch(book.title, this.input.value)}</div>
                        <div class="text-sm text-gray-500">${this.highlightMatch(book.author, this.input.value)}</div>
                        <div class="text-xs text-gray-400">
                            ${book.category} • Tersedia: ${book.available_quantity}
                            ${book.barcode ? ` • ${book.barcode}` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    renderMemberItem(member, isFirst) {
        const canBorrowClass = member.can_borrow ? 'text-green-600' : 'text-red-600';
        const canBorrowText = member.can_borrow ? 'Dapat meminjam' : 'Tidak dapat meminjam';
        
        return `
            <div class="autocomplete-item px-4 py-3 cursor-pointer hover:bg-gray-100 ${isFirst ? 'highlighted bg-gray-100' : ''}" 
                 data-id="${member.id}" 
                 data-member-id="${member.member_id}"
                 data-name="${member.name}"
                 data-email="${member.email}"
                 data-can-borrow="${member.can_borrow}">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900">${this.highlightMatch(member.name, this.input.value)}</div>
                        <div class="text-sm text-gray-500">${this.highlightMatch(member.member_id, this.input.value)} • ${this.highlightMatch(member.email, this.input.value)}</div>
                        <div class="text-xs ${canBorrowClass}">
                            ${canBorrowText} • Pinjaman aktif: ${member.active_loans_count}/3
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    highlightMatch(text, query) {
        if (!query) return text;
        
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
    }
    
    highlightItem(item) {
        // Remove previous highlight
        this.dropdown.querySelectorAll('.autocomplete-item').forEach(el => {
            el.classList.remove('highlighted', 'bg-gray-100');
        });
        
        // Add highlight to current item
        if (item) {
            item.classList.add('highlighted', 'bg-gray-100');
            item.scrollIntoView({ block: 'nearest' });
        }
    }
    
    selectItem(item) {
        const data = {};
        
        // Extract data attributes
        for (const attr of item.attributes) {
            if (attr.name.startsWith('data-')) {
                const key = attr.name.replace('data-', '').replace(/-/g, '_');
                data[key] = attr.value;
            }
        }
        
        // Set input value
        if (this.options.type === 'books') {
            this.input.value = data.title;
        } else {
            this.input.value = data.name;
        }
        
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
    // Book autocomplete
    const bookInputs = document.querySelectorAll('[data-autocomplete="loan-books"]');
    bookInputs.forEach(input => {
        new LoanAutocomplete(input, {
            type: 'books',
            onSelect: (book) => {
                // Set hidden book_id field
                const bookIdInput = document.getElementById('book_id');
                if (bookIdInput) {
                    bookIdInput.value = book.id;
                }
                
                // Show book info
                showBookInfo(book);
                
                // Trigger validation
                validateBookSelection(book);
            }
        });
    });
    
    // Member autocomplete
    const memberInputs = document.querySelectorAll('[data-autocomplete="loan-members"]');
    memberInputs.forEach(input => {
        new LoanAutocomplete(input, {
            type: 'members',
            onSelect: (member) => {
                // Set hidden member_id field
                const memberIdInput = document.getElementById('member_id');
                if (memberIdInput) {
                    memberIdInput.value = member.id;
                }
                
                // Show member info
                showMemberInfo(member);
                
                // Trigger validation
                validateMemberSelection(member);
            }
        });
    });
});

// Helper functions for loan form
function showBookInfo(book) {
    const bookInfo = document.getElementById('book_info');
    const bookTitle = document.getElementById('book_display_title');
    const bookDetails = document.getElementById('book_display_info');
    
    if (bookInfo && bookTitle && bookDetails) {
        bookTitle.textContent = book.title;
        bookDetails.textContent = `${book.author} • ${book.category} • Tersedia: ${book.available_quantity}`;
        bookInfo.classList.remove('hidden');
    }
}

function showMemberInfo(member) {
    const memberInfo = document.getElementById('member_info');
    const memberName = document.getElementById('member_display_name');
    const memberDetails = document.getElementById('member_display_info');
    
    if (memberInfo && memberName && memberDetails) {
        memberName.textContent = member.name;
        memberDetails.textContent = `${member.member_id} • ${member.email} • Pinjaman aktif: ${member.active_loans_count}/3`;
        memberInfo.classList.remove('hidden');
    }
}

function validateBookSelection(book) {
    const validationSummary = document.getElementById('validation_summary');
    const validationErrors = document.getElementById('validation_errors');
    const submitBtn = document.getElementById('submit_btn');
    
    const errors = [];
    
    if (parseInt(book.available) <= 0) {
        errors.push('Buku tidak tersedia untuk dipinjam');
    }
    
    if (errors.length > 0) {
        validationErrors.innerHTML = errors.map(error => `<li>${error}</li>`).join('');
        validationSummary.classList.remove('hidden');
        if (submitBtn) submitBtn.disabled = true;
    } else {
        validationSummary.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = false;
    }
}

function validateMemberSelection(member) {
    const validationSummary = document.getElementById('validation_summary');
    const validationErrors = document.getElementById('validation_errors');
    const submitBtn = document.getElementById('submit_btn');
    
    const errors = [];
    
    if (!member.can_borrow) {
        if (parseInt(member.active_loans_count) >= 3) {
            errors.push('Anggota sudah mencapai batas maksimal peminjaman (3 buku)');
        } else {
            errors.push('Anggota tidak dapat meminjam buku saat ini');
        }
    }
    
    if (errors.length > 0) {
        validationErrors.innerHTML = errors.map(error => `<li>${error}</li>`).join('');
        validationSummary.classList.remove('hidden');
        if (submitBtn) submitBtn.disabled = true;
    } else {
        validationSummary.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = false;
    }
}

// Export for use in other modules
window.LoanAutocomplete = LoanAutocomplete;
