<x-app-layout title="Tambah Peminjaman">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Peminjaman Baru</h1>
                <p class="mt-1 text-sm text-gray-600">Catat transaksi peminjaman buku</p>
            </div>
            <x-button href="{{ route('loans.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </x-button>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form method="POST" action="{{ route('loans.store') }}" class="p-6 space-y-6" id="loan-form">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Member Selection -->
                        <div>
                            <label for="member_search" class="block text-sm font-medium text-gray-700 mb-1">
                                Anggota <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="member_search"
                                    placeholder="Cari anggota (nama, ID anggota, email)..."
                                    data-autocomplete="loan-members"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('member_id') border-red-300 @enderror"
                                >
                                <input type="hidden" name="member_id" id="member_id" value="{{ old('member_id') }}">
                            </div>
                            @error('member_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <!-- Member Info Display -->
                            <div id="member_info" class="mt-2 hidden">
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-blue-900" id="member_display_name"></p>
                                            <p class="text-xs text-blue-700" id="member_display_info"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Book Selection -->
                        <div>
                            <label for="book_search" class="block text-sm font-medium text-gray-700 mb-1">
                                Buku <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="book_search"
                                    placeholder="Cari buku (judul, penulis, ISBN, barcode)..."
                                    data-autocomplete="loan-books"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('book_id') border-red-300 @enderror"
                                >
                                <input type="hidden" name="book_id" id="book_id" value="{{ old('book_id') }}">
                            </div>
                            @error('book_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <!-- Book Info Display -->
                            <div id="book_info" class="mt-2 hidden">
                                <div class="bg-green-50 border border-green-200 rounded-md p-3">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-green-900" id="book_display_title"></p>
                                            <p class="text-xs text-green-700" id="book_display_info"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barcode Scanner -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Scan Barcode Buku
                            </label>
                            <x-barcode-scanner
                                input-id="barcode-scanner"
                                placeholder="Scan atau ketik barcode buku..."
                                :on-scan="'function(barcode) {
                                    document.getElementById(\'book_search\').value = barcode;
                                    searchBooks(barcode);
                                }'"
                            />
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Loan Date -->
                        <div>
                            <label for="loan_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Peminjaman <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                name="loan_date"
                                id="loan_date"
                                value="{{ old('loan_date', date('Y-m-d')) }}"
                                required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('loan_date') border-red-300 @enderror"
                            >
                            @error('loan_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Jatuh Tempo
                            </label>
                            <input
                                type="date"
                                name="due_date"
                                id="due_date"
                                value="{{ old('due_date') }}"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('due_date') border-red-300 @enderror"
                            >
                            <p class="mt-1 text-xs text-gray-500">Kosongkan untuk otomatis 7 hari dari tanggal peminjaman</p>
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Catatan
                            </label>
                            <textarea
                                name="notes"
                                id="notes"
                                rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('notes') border-red-300 @enderror"
                                placeholder="Catatan tambahan (opsional)"
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Validation Summary -->
                <div id="validation_summary" class="hidden">
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Tidak dapat memproses peminjaman</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside" id="validation_errors"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Section -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Informasi Peminjaman</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Maksimal peminjaman per anggota: 3 buku</li>
                                    <li>Durasi peminjaman default: 7 hari</li>
                                    <li>Denda keterlambatan: Rp 1.000 per hari</li>
                                    <li>Anggota dengan buku terlambat tidak dapat meminjam</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <x-button href="{{ route('loans.index') }}" variant="secondary">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" id="submit_btn">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Peminjaman
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Auto-calculate due date -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loanDateInput = document.getElementById('loan_date');
            const dueDateInput = document.getElementById('due_date');

            loanDateInput.addEventListener('change', function() {
                if (this.value && !dueDateInput.value) {
                    const loanDate = new Date(this.value);
                    loanDate.setDate(loanDate.getDate() + 7);
                    dueDateInput.value = loanDate.toISOString().split('T')[0];
                }
            });

            // Trigger calculation on page load
            if (loanDateInput.value && !dueDateInput.value) {
                loanDateInput.dispatchEvent(new Event('change'));
            }
        });
    </script>
</x-app-layout>
