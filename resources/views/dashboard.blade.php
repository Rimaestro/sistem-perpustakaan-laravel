<x-app-layout title="Dashboard">
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Selamat datang di Sistem Perpustakaan SMA Negeri 1 Sampang</p>
        </div>

        <!-- Overdue Alerts (for admin and staff only) -->
        @if(auth()->user()->hasAnyRole(['admin', 'staff']))
            <x-overdue-alerts :limit="5" />
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="dashboard-stats">
            <!-- Total Books -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Buku</p>
                        <p class="text-2xl font-bold text-gray-900" id="total-books">-</p>
                    </div>
                </div>
            </div>

            <!-- Active Loans -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Peminjaman Aktif</p>
                        <p class="text-2xl font-bold text-gray-900" id="active-loans">-</p>
                    </div>
                </div>
            </div>

            <!-- Total Members -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Anggota</p>
                        <p class="text-2xl font-bold text-gray-900" id="total-members">-</p>
                    </div>
                </div>
            </div>

            <!-- Overdue Loans -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Terlambat</p>
                        <p class="text-2xl font-bold text-gray-900" id="overdue-loans">-</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Quick Actions (for admin and staff) -->
        @if(auth()->user()->hasAnyRole(['admin', 'staff']))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-button href="{{ route('loans.quick-loan') }}" variant="primary" class="justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Peminjaman Cepat
                    </x-button>
                    <x-button href="{{ route('loans.quick-return') }}" variant="secondary" class="justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        Pengembalian Cepat
                    </x-button>
                    <x-button href="{{ route('books.create') }}" variant="secondary" class="justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Tambah Buku
                    </x-button>
                    <x-button href="{{ route('members.create') }}" variant="secondary" class="justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Tambah Anggota
                    </x-button>
                </div>
            </div>
        @endif

        <!-- Today's Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Today's Statistics -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Aktivitas Hari Ini</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Peminjaman Baru</span>
                        <span class="text-sm font-medium text-gray-900" id="loans-today">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Pengembalian</span>
                        <span class="text-sm font-medium text-gray-900" id="returns-today">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total Denda</span>
                        <span class="text-sm font-medium text-gray-900" id="total-fines">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Buku Stok Rendah</span>
                        <span class="text-sm font-medium text-gray-900" id="books-low-stock">-</span>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Akun</h3>
                <dl class="space-y-4">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Nama</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Email</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ auth()->user()->email }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Role</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ auth()->user()->role === 'admin' ? 'bg-red-100 text-red-800' :
                                   (auth()->user()->role === 'staff' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Bergabung</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ auth()->user()->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

    </div>

    <!-- Load Dashboard Statistics -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStatistics();

            // Auto-refresh every 5 minutes
            setInterval(loadDashboardStatistics, 5 * 60 * 1000);
        });

        function loadDashboardStatistics() {
            fetch('/api/dashboard/statistics')
                .then(response => response.json())
                .then(data => {
                    // Update statistics
                    document.getElementById('total-books').textContent = data.total_books || 0;
                    document.getElementById('active-loans').textContent = data.active_loans || 0;
                    document.getElementById('total-members').textContent = data.total_members || 0;
                    document.getElementById('overdue-loans').textContent = data.overdue_loans || 0;
                    document.getElementById('loans-today').textContent = data.loans_today || 0;
                    document.getElementById('returns-today').textContent = data.returns_today || 0;
                    document.getElementById('books-low-stock').textContent = data.books_low_stock || 0;

                    // Format total fines
                    const totalFines = data.total_fines || 0;
                    document.getElementById('total-fines').textContent = 'Rp ' + new Intl.NumberFormat('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(totalFines);
                })
                .catch(error => {
                    console.error('Error loading dashboard statistics:', error);
                });
        }
    </script>
</x-app-layout>
