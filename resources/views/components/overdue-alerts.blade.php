@props(['limit' => 5])

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" id="overdue-alerts">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900">Peminjaman Terlambat</h3>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" id="overdue-count">
                0
            </span>
            <button onclick="refreshOverdueAlerts()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <div id="overdue-content" class="max-h-80 overflow-y-auto">
        <!-- Loading state -->
        <div id="overdue-loading" class="px-6 py-8 text-center">
            <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm text-gray-500">Memuat data...</p>
        </div>

        <!-- Empty state -->
        <div id="overdue-empty" class="px-6 py-8 text-center hidden">
            <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-sm font-medium text-gray-900">Tidak ada peminjaman terlambat</h3>
            <p class="text-sm text-gray-500 mt-1">Semua buku dikembalikan tepat waktu!</p>
        </div>

        <!-- Overdue list -->
        <div id="overdue-list" class="divide-y divide-gray-200 hidden">
            <!-- Items will be populated by JavaScript -->
        </div>
    </div>

    <div id="overdue-footer" class="px-6 py-3 bg-gray-50 border-t border-gray-200 hidden">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Menampilkan <span id="shown-count">0</span> dari <span id="total-count">0</span> peminjaman terlambat
            </p>
            <a href="{{ route('loans.index', ['status' => 'overdue']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                Lihat semua →
            </a>
        </div>
    </div>
</div>

<script>
let overdueRefreshInterval;

function loadOverdueAlerts() {
    const loading = document.getElementById('overdue-loading');
    const empty = document.getElementById('overdue-empty');
    const list = document.getElementById('overdue-list');
    const footer = document.getElementById('overdue-footer');
    const count = document.getElementById('overdue-count');

    // Show loading
    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.classList.add('hidden');
    footer.classList.add('hidden');

    fetch('/api/loans/overdue')
        .then(response => response.json())
        .then(data => {
            loading.classList.add('hidden');
            
            if (data.length === 0) {
                empty.classList.remove('hidden');
                count.textContent = '0';
                count.classList.remove('bg-red-100', 'text-red-800');
                count.classList.add('bg-green-100', 'text-green-800');
            } else {
                list.classList.remove('hidden');
                footer.classList.remove('hidden');
                count.textContent = data.length;
                count.classList.remove('bg-green-100', 'text-green-800');
                count.classList.add('bg-red-100', 'text-red-800');
                
                renderOverdueList(data);
                
                document.getElementById('shown-count').textContent = Math.min(data.length, {{ $limit }});
                document.getElementById('total-count').textContent = data.length;
            }
        })
        .catch(error => {
            console.error('Error loading overdue alerts:', error);
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
            document.getElementById('overdue-empty').innerHTML = `
                <svg class="h-12 w-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-sm font-medium text-gray-900">Gagal memuat data</h3>
                <p class="text-sm text-gray-500 mt-1">Terjadi kesalahan saat memuat data peminjaman terlambat.</p>
            `;
        });
}

function renderOverdueList(overdueLoans) {
    const list = document.getElementById('overdue-list');
    const limit = {{ $limit }};
    const itemsToShow = overdueLoans.slice(0, limit);
    
    list.innerHTML = itemsToShow.map(loan => `
        <div class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                ${loan.book_title}
                            </p>
                            <p class="text-sm text-gray-500 truncate">
                                ${loan.member_name} (${loan.member_id})
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-red-600 font-medium">
                            ${Math.floor(loan.days_overdue)} hari
                        </p>
                        <p class="text-xs text-gray-500">
                            Jatuh tempo: ${loan.due_date_formatted || formatDate(loan.due_date)}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">
                            Rp ${new Intl.NumberFormat('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(loan.fine_amount)}
                        </p>
                        <p class="text-xs text-gray-500">Denda</p>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function refreshOverdueAlerts() {
    loadOverdueAlerts();
}

// Auto-refresh every 5 minutes
function startOverdueRefresh() {
    loadOverdueAlerts(); // Initial load
    overdueRefreshInterval = setInterval(loadOverdueAlerts, 5 * 60 * 1000); // 5 minutes
}

function stopOverdueRefresh() {
    if (overdueRefreshInterval) {
        clearInterval(overdueRefreshInterval);
    }
}

// Start when component loads
document.addEventListener('DOMContentLoaded', function() {
    startOverdueRefresh();
});

// Stop when page unloads
window.addEventListener('beforeunload', function() {
    stopOverdueRefresh();
});
</script>
