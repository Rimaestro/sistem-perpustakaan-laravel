<x-app-layout title="Detail Peminjaman">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Peminjaman</h1>
                <p class="mt-1 text-sm text-gray-600">Informasi lengkap transaksi peminjaman</p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                @if($loan->status !== 'returned')
                    <x-button href="{{ route('loans.edit', $loan) }}" variant="secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </x-button>
                @endif
                <x-button href="{{ route('loans.index') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </x-button>
            </div>
        </div>

        <!-- Status Alert -->
        @if($loan->isOverdue() && $loan->status !== 'returned')
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Peminjaman Terlambat</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>Buku ini sudah terlambat {{ $loan->due_date->diffInDays(now()) }} hari. Denda yang dikenakan: <strong>Rp {{ number_format($loan->calculateFine(), 0, ',', '.') }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($loan->status === 'returned')
            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">Buku Sudah Dikembalikan</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>Buku dikembalikan pada {{ $loan->return_date->format('d F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Loan Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Peminjaman</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ID Transaksi</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ str_pad($loan->id, 6, '0', STR_PAD_LEFT) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    @if($loan->status === 'active')
                                        @if($loan->isOverdue())
                                            <x-status-badge status="overdue" color="red">Terlambat</x-status-badge>
                                        @else
                                            <x-status-badge status="active" color="blue">Aktif</x-status-badge>
                                        @endif
                                    @elseif($loan->status === 'returned')
                                        <x-status-badge status="returned" color="green">Dikembalikan</x-status-badge>
                                    @else
                                        <x-status-badge status="overdue" color="red">Terlambat</x-status-badge>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tanggal Peminjaman</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $loan->loan_date->format('d F Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tanggal Jatuh Tempo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $loan->due_date->format('d F Y') }}</dd>
                            </div>
                            @if($loan->return_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tanggal Pengembalian</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $loan->return_date->format('d F Y') }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Denda</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($loan->fine_amount > 0)
                                        <span class="text-red-600 font-medium">
                                            Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-green-600">Tidak ada denda</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Diproses Oleh</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $loan->processedBy->name }}</dd>
                            </div>
                            @if($loan->status !== 'returned')
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Sisa Waktu</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($loan->days_remaining >= 0)
                                            <span class="text-green-600">{{ $loan->days_remaining }} hari lagi</span>
                                        @else
                                            <span class="text-red-600">Terlambat {{ abs($loan->days_remaining) }} hari</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        </dl>
                        @if($loan->notes)
                            <div class="mt-4">
                                <dt class="text-sm font-medium text-gray-500">Catatan</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $loan->notes }}</dd>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Book Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Buku</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-20 bg-gray-200 rounded-md flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900">{{ $loan->book->title }}</h4>
                                <p class="text-sm text-gray-600">oleh {{ $loan->book->author }}</p>
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-500">ISBN:</span>
                                        <span class="text-gray-900 font-mono">{{ $loan->book->isbn ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Barcode:</span>
                                        <span class="text-gray-900 font-mono">{{ $loan->book->barcode }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Kategori:</span>
                                        <span class="text-gray-900">{{ $loan->book->category->name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Tahun Terbit:</span>
                                        <span class="text-gray-900">{{ $loan->book->publication_year ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <x-button href="{{ route('books.show', $loan->book) }}" variant="secondary" size="sm">
                                        Lihat Detail Buku
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Member Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Anggota</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900">{{ $loan->member->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $loan->member->member_id }}</p>
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-500">Email:</span>
                                        <span class="text-gray-900">{{ $loan->member->email }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Telepon:</span>
                                        <span class="text-gray-900">{{ $loan->member->phone ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Status:</span>
                                        <x-status-badge :status="$loan->member->status" :color="$loan->member->status_color">
                                            {{ $loan->member->status_label }}
                                        </x-status-badge>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Pinjaman Aktif:</span>
                                        <span class="text-gray-900">{{ $loan->member->active_loans_count }} buku</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <x-button href="{{ route('members.show', $loan->member) }}" variant="secondary" size="sm">
                                        Lihat Detail Anggota
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                @if($loan->status !== 'returned')
                    <!-- Return Book Form -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Pengembalian Buku</h3>
                        </div>
                        <form method="POST" action="{{ route('loans.return', $loan) }}" class="px-6 py-4 space-y-4">
                            @csrf
                            
                            <div>
                                <label for="return_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Pengembalian <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    name="return_date"
                                    id="return_date"
                                    value="{{ date('Y-m-d') }}"
                                    required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                            </div>

                            @if($loan->isOverdue())
                                <div>
                                    <label for="fine_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                        Denda
                                    </label>
                                    <input
                                        type="number"
                                        name="fine_amount"
                                        id="fine_amount"
                                        value="{{ $loan->calculateFine() }}"
                                        min="0"
                                        step="1000"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Denda otomatis: Rp {{ number_format($loan->calculateFine(), 0, ',', '.') }}</p>
                                </div>
                            @endif

                            <div>
                                <label for="return_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Catatan Pengembalian
                                </label>
                                <textarea
                                    name="notes"
                                    id="return_notes"
                                    rows="3"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Kondisi buku, catatan tambahan..."
                                ></textarea>
                            </div>

                            <x-button type="submit" variant="primary" class="w-full justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Proses Pengembalian
                            </x-button>
                        </form>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Aksi Cepat</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <x-button href="{{ route('members.loan-history', $loan->member) }}" variant="secondary" class="w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Riwayat Anggota
                        </x-button>
                        <x-button href="{{ route('loans.create') }}" variant="secondary" class="w-full justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Peminjaman Baru
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
