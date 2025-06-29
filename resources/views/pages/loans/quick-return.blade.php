<x-app-layout title="Pengembalian Cepat">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengembalian Cepat</h1>
                <p class="mt-1 text-sm text-gray-600">Proses pengembalian dengan scan barcode</p>
            </div>
            <x-button href="{{ route('loans.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </x-button>
        </div>

        <!-- Quick Return Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <!-- Step 1: Scan Book Barcode -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-medium">1</span>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Scan Barcode Buku</h3>
                    </div>

                    <div class="ml-10">
                        <x-barcode-scanner
                            input-id="book-barcode"
                            placeholder="Scan barcode buku yang akan dikembalikan..."
                            :on-scan="'handleBookScan'"
                            class="text-lg"
                        />
                        
                        <!-- Loading State -->
                        <div id="loading" class="mt-4 hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div class="flex items-center">
                                    <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-blue-700">Mencari data peminjaman...</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Loan Info Display -->
                        <div id="loan-info" class="mt-4 hidden">
                            <div class="bg-green-50 border border-green-200 rounded-md p-6">
                                <div class="flex items-start space-x-4">
                                    <svg class="h-6 w-6 text-green-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-lg font-medium text-green-900 mb-3">Peminjaman Ditemukan</h4>
                                        
                                        <!-- Book Info -->
                                        <div class="mb-4">
                                            <h5 class="text-sm font-medium text-green-800 mb-2">Informasi Buku</h5>
                                            <div class="bg-white rounded-md p-3">
                                                <p class="font-medium text-gray-900" id="book-title"></p>
                                                <p class="text-sm text-gray-600" id="book-author"></p>
                                                <p class="text-xs text-gray-500 font-mono" id="book-barcode"></p>
                                            </div>
                                        </div>

                                        <!-- Member Info -->
                                        <div class="mb-4">
                                            <h5 class="text-sm font-medium text-green-800 mb-2">Informasi Peminjam</h5>
                                            <div class="bg-white rounded-md p-3">
                                                <p class="font-medium text-gray-900" id="member-name"></p>
                                                <p class="text-sm text-gray-600" id="member-id"></p>
                                            </div>
                                        </div>

                                        <!-- Loan Details -->
                                        <div class="mb-4">
                                            <h5 class="text-sm font-medium text-green-800 mb-2">Detail Peminjaman</h5>
                                            <div class="bg-white rounded-md p-3">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-gray-500">Tanggal Pinjam:</span>
                                                        <span class="text-gray-900 font-medium" id="loan-date"></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Jatuh Tempo:</span>
                                                        <span class="text-gray-900 font-medium" id="due-date"></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Status:</span>
                                                        <span id="loan-status"></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Denda:</span>
                                                        <span class="text-gray-900 font-medium" id="fine-amount"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Display -->
                        <div id="error-display" class="mt-4 hidden">
                            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                                        <p class="mt-1 text-sm text-red-700" id="error-message"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Return Form -->
                <div id="return-form" class="mt-8 space-y-6 hidden">
                    <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-medium">2</span>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Konfirmasi Pengembalian</h3>
                    </div>

                    <form id="quick-return-form" class="ml-10 space-y-4">
                        @csrf
                        <input type="hidden" id="loan-id" name="loan_id">

                        <!-- Return Date -->
                        <div>
                            <label for="return_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Pengembalian <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="return_date"
                                name="return_date"
                                value="{{ date('Y-m-d') }}"
                                required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                        </div>

                        <!-- Fine Amount (if overdue) -->
                        <div id="fine-section" class="hidden">
                            <label for="fine_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                Denda (Rp)
                            </label>
                            <input
                                type="number"
                                id="fine_amount"
                                name="fine_amount"
                                min="0"
                                step="1000"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                            <p class="mt-1 text-xs text-gray-500" id="fine-calculation"></p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Catatan Pengembalian
                            </label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Kondisi buku, catatan tambahan..."
                            ></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-4 pt-4">
                            <x-button type="button" id="process-return-btn" variant="primary" class="flex-1 justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Proses Pengembalian
                            </x-button>
                            <x-button type="button" id="reset-form-btn" variant="secondary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Petunjuk Penggunaan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">1</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Scan Barcode Buku</h4>
                        <p class="text-sm text-gray-600 mt-1">Scan barcode pada buku yang akan dikembalikan. Sistem akan mencari data peminjaman aktif.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">2</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Konfirmasi Pengembalian</h4>
                        <p class="text-sm text-gray-600 mt-1">Periksa detail peminjaman, atur tanggal pengembalian dan denda jika ada, lalu proses pengembalian.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Return JavaScript -->
    <script>
        let currentLoan = null;

        function handleBookScan(barcode) {
            searchLoanByBarcode(barcode);
        }

        async function searchLoanByBarcode(barcode) {
            showLoading();
            
            try {
                const response = await fetch(`/api/loans/find-by-barcode?barcode=${encodeURIComponent(barcode)}`);
                
                if (response.ok) {
                    const data = await response.json();
                    currentLoan = data;
                    showLoanInfo(data);
                    showReturnForm();
                } else {
                    const errorData = await response.json();
                    showError(errorData.error || 'Terjadi kesalahan');
                }
            } catch (error) {
                showError('Terjadi kesalahan saat mencari data peminjaman');
            } finally {
                hideLoading();
            }
        }

        function showLoading() {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('loan-info').classList.add('hidden');
            document.getElementById('error-display').classList.add('hidden');
            document.getElementById('return-form').classList.add('hidden');
        }

        function hideLoading() {
            document.getElementById('loading').classList.add('hidden');
        }

        function showLoanInfo(data) {
            // Book info
            document.getElementById('book-title').textContent = data.book.title;
            document.getElementById('book-author').textContent = data.book.author;
            document.getElementById('book-barcode').textContent = data.book.barcode;

            // Member info
            document.getElementById('member-name').textContent = data.member.name;
            document.getElementById('member-id').textContent = data.member.member_id;

            // Loan details
            document.getElementById('loan-date').textContent = data.loan.loan_date;
            document.getElementById('due-date').textContent = data.loan.due_date;
            
            // Status
            const statusElement = document.getElementById('loan-status');
            if (data.loan.is_overdue) {
                statusElement.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Terlambat</span>';
            } else {
                statusElement.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Aktif</span>';
            }

            // Fine
            const fineAmount = data.loan.fine_amount || 0;
            document.getElementById('fine-amount').textContent = fineAmount > 0 ? `Rp ${new Intl.NumberFormat('id-ID').format(fineAmount)}` : 'Tidak ada';

            document.getElementById('loan-info').classList.remove('hidden');
            document.getElementById('error-display').classList.add('hidden');
        }

        function showReturnForm() {
            document.getElementById('loan-id').value = currentLoan.loan.id;
            
            // Show fine section if overdue
            if (currentLoan.loan.is_overdue) {
                document.getElementById('fine-section').classList.remove('hidden');
                document.getElementById('fine_amount').value = currentLoan.loan.fine_amount;
                document.getElementById('fine-calculation').textContent = `Denda otomatis: Rp ${new Intl.NumberFormat('id-ID').format(currentLoan.loan.fine_amount)}`;
            } else {
                document.getElementById('fine-section').classList.add('hidden');
            }

            document.getElementById('return-form').classList.remove('hidden');
        }

        function showError(message) {
            document.getElementById('error-message').textContent = message;
            document.getElementById('error-display').classList.remove('hidden');
            document.getElementById('loan-info').classList.add('hidden');
            document.getElementById('return-form').classList.add('hidden');
        }

        // Process return
        document.getElementById('process-return-btn').addEventListener('click', async function() {
            if (!currentLoan) {
                alert('Silakan scan barcode buku terlebih dahulu');
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('return_date', document.getElementById('return_date').value);
            formData.append('notes', document.getElementById('notes').value);
            
            const fineAmount = document.getElementById('fine_amount').value;
            if (fineAmount) {
                formData.append('fine_amount', fineAmount);
            }

            try {
                const response = await fetch(`/loans/${currentLoan.loan.id}/return`, {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    alert('Pengembalian berhasil diproses!');
                    resetForm();
                } else {
                    const errorText = await response.text();
                    alert('Terjadi kesalahan: ' + errorText);
                }
            } catch (error) {
                alert('Terjadi kesalahan saat memproses pengembalian');
            }
        });

        // Reset form
        document.getElementById('reset-form-btn').addEventListener('click', resetForm);

        function resetForm() {
            currentLoan = null;
            
            // Reset inputs
            document.getElementById('book-barcode').value = '';
            document.getElementById('return_date').value = new Date().toISOString().split('T')[0];
            document.getElementById('fine_amount').value = '';
            document.getElementById('notes').value = '';
            
            // Hide displays
            document.getElementById('loan-info').classList.add('hidden');
            document.getElementById('error-display').classList.add('hidden');
            document.getElementById('return-form').classList.add('hidden');
            document.getElementById('fine-section').classList.add('hidden');
        }
    </script>
</x-app-layout>
