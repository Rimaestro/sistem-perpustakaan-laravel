<x-app-layout title="Peminjaman Cepat">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Peminjaman Cepat</h1>
                <p class="mt-1 text-sm text-gray-600">Proses peminjaman dengan scan barcode</p>
            </div>
            <div class="flex items-center space-x-3">
                <x-button href="{{ route('loans.create') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Form Manual
                </x-button>
                <x-button href="{{ route('loans.index') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </x-button>
            </div>
        </div>

        <!-- Quick Loan Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <form id="quick-loan-form" class="space-y-6">
                    @csrf

                    <!-- Step 1: Scan Member Card -->
                    <div id="step-member" class="space-y-4">
                        <div class="flex items-center space-x-2">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium">1</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Scan Kartu Anggota</h3>
                        </div>

                        <div class="ml-10">
                            <x-barcode-scanner
                                input-id="member-barcode"
                                placeholder="Scan kartu anggota atau ketik ID anggota..."
                                :on-scan="'handleMemberScan'"
                                class="text-lg"
                            />
                            
                            <!-- Member Info Display -->
                            <div id="member-info" class="mt-4 hidden">
                                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <h4 class="text-lg font-medium text-green-900" id="member-name"></h4>
                                            <p class="text-sm text-green-700" id="member-details"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Member Error -->
                            <div id="member-error" class="mt-4 hidden">
                                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                                            <p class="mt-1 text-sm text-red-700" id="member-error-message"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Scan Book Barcode -->
                    <div id="step-book" class="space-y-4 opacity-50 pointer-events-none">
                        <div class="flex items-center space-x-2">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center" id="step-book-indicator">
                                <span class="text-white text-sm font-medium">2</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Scan Barcode Buku</h3>
                        </div>

                        <div class="ml-10">
                            <x-barcode-scanner
                                input-id="book-barcode"
                                placeholder="Scan barcode buku..."
                                :on-scan="'handleBookScan'"
                                class="text-lg"
                            />
                            
                            <!-- Book Info Display -->
                            <div id="book-info" class="mt-4 hidden">
                                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <h4 class="text-lg font-medium text-green-900" id="book-title"></h4>
                                            <p class="text-sm text-green-700" id="book-details"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Book Error -->
                            <div id="book-error" class="mt-4 hidden">
                                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                                            <p class="mt-1 text-sm text-red-700" id="book-error-message"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Confirm Loan -->
                    <div id="step-confirm" class="space-y-4 opacity-50 pointer-events-none">
                        <div class="flex items-center space-x-2">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center" id="step-confirm-indicator">
                                <span class="text-white text-sm font-medium">3</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Konfirmasi Peminjaman</h3>
                        </div>

                        <div class="ml-10">
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <h4 class="text-sm font-medium text-blue-900 mb-3">Detail Peminjaman</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-700">Tanggal Peminjaman:</span>
                                        <div class="font-medium text-blue-900" id="loan-date">{{ date('d/m/Y') }}</div>
                                    </div>
                                    <div>
                                        <span class="text-blue-700">Tanggal Jatuh Tempo:</span>
                                        <div class="font-medium text-blue-900" id="due-date">{{ date('d/m/Y', strtotime('+7 days')) }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Catatan (Opsional)
                                </label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="2"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Catatan tambahan..."
                                ></textarea>
                            </div>

                            <div class="mt-6 flex items-center space-x-4">
                                <x-button type="button" id="process-loan-btn" variant="primary" class="flex-1 justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Proses Peminjaman
                                </x-button>
                                <x-button type="button" id="reset-form-btn" variant="secondary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Reset
                                </x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Petunjuk Penggunaan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">1</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Scan Kartu Anggota</h4>
                        <p class="text-sm text-gray-600 mt-1">Scan QR code atau barcode pada kartu anggota, atau ketik ID anggota secara manual.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">2</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Scan Barcode Buku</h4>
                        <p class="text-sm text-gray-600 mt-1">Scan barcode pada buku yang akan dipinjam. Sistem akan memvalidasi ketersediaan.</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">3</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Konfirmasi</h4>
                        <p class="text-sm text-gray-600 mt-1">Periksa detail peminjaman dan klik "Proses Peminjaman" untuk menyelesaikan transaksi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Loan JavaScript -->
    <script>
        let selectedMember = null;
        let selectedBook = null;

        function handleMemberScan(code) {
            // Search for member by ID or barcode
            searchMember(code);
        }

        function handleBookScan(code) {
            // Search for book by barcode
            searchBook(code);
        }

        async function searchMember(query) {
            try {
                const response = await fetch(`/api/loans/search-members?q=${encodeURIComponent(query)}&limit=1`);
                const members = await response.json();

                if (members.length > 0) {
                    const member = members[0];
                    if (member.can_borrow) {
                        selectedMember = member;
                        showMemberInfo(member);
                        enableStep('book');
                    } else {
                        showMemberError(`Anggota tidak dapat meminjam. Pinjaman aktif: ${member.active_loans_count}/3`);
                    }
                } else {
                    showMemberError('Anggota tidak ditemukan');
                }
            } catch (error) {
                showMemberError('Terjadi kesalahan saat mencari anggota');
            }
        }

        async function searchBook(barcode) {
            try {
                const response = await fetch(`/api/loans/search-books?q=${encodeURIComponent(barcode)}&limit=1`);
                const books = await response.json();

                if (books.length > 0) {
                    const book = books[0];
                    if (book.available_quantity > 0) {
                        selectedBook = book;
                        showBookInfo(book);
                        enableStep('confirm');
                    } else {
                        showBookError('Buku tidak tersedia untuk dipinjam');
                    }
                } else {
                    showBookError('Buku tidak ditemukan');
                }
            } catch (error) {
                showBookError('Terjadi kesalahan saat mencari buku');
            }
        }

        function showMemberInfo(member) {
            document.getElementById('member-name').textContent = member.name;
            document.getElementById('member-details').textContent = `${member.member_id} • ${member.email}`;
            document.getElementById('member-info').classList.remove('hidden');
            document.getElementById('member-error').classList.add('hidden');
        }

        function showMemberError(message) {
            document.getElementById('member-error-message').textContent = message;
            document.getElementById('member-error').classList.remove('hidden');
            document.getElementById('member-info').classList.add('hidden');
            selectedMember = null;
        }

        function showBookInfo(book) {
            document.getElementById('book-title').textContent = book.title;
            document.getElementById('book-details').textContent = `${book.author} • ${book.category} • Tersedia: ${book.available_quantity}`;
            document.getElementById('book-info').classList.remove('hidden');
            document.getElementById('book-error').classList.add('hidden');
        }

        function showBookError(message) {
            document.getElementById('book-error-message').textContent = message;
            document.getElementById('book-error').classList.remove('hidden');
            document.getElementById('book-info').classList.add('hidden');
            selectedBook = null;
        }

        function enableStep(step) {
            const stepElement = document.getElementById(`step-${step}`);
            const indicatorElement = document.getElementById(`step-${step}-indicator`);
            
            stepElement.classList.remove('opacity-50', 'pointer-events-none');
            indicatorElement.classList.remove('bg-gray-400');
            indicatorElement.classList.add('bg-blue-600');
        }

        // Process loan
        document.getElementById('process-loan-btn').addEventListener('click', async function() {
            if (!selectedMember || !selectedBook) {
                alert('Silakan lengkapi semua langkah terlebih dahulu');
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('member_id', selectedMember.id);
            formData.append('book_id', selectedBook.id);
            formData.append('loan_date', new Date().toISOString().split('T')[0]);
            formData.append('notes', document.getElementById('notes').value);

            try {
                const response = await fetch('/loans', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    alert('Peminjaman berhasil diproses!');
                    resetForm();
                } else {
                    const errorData = await response.text();
                    alert('Terjadi kesalahan: ' + errorData);
                }
            } catch (error) {
                alert('Terjadi kesalahan saat memproses peminjaman');
            }
        });

        // Reset form
        document.getElementById('reset-form-btn').addEventListener('click', resetForm);

        function resetForm() {
            selectedMember = null;
            selectedBook = null;
            
            // Reset inputs
            document.getElementById('member-barcode').value = '';
            document.getElementById('book-barcode').value = '';
            document.getElementById('notes').value = '';
            
            // Hide info/error displays
            document.getElementById('member-info').classList.add('hidden');
            document.getElementById('member-error').classList.add('hidden');
            document.getElementById('book-info').classList.add('hidden');
            document.getElementById('book-error').classList.add('hidden');
            
            // Reset steps
            document.getElementById('step-book').classList.add('opacity-50', 'pointer-events-none');
            document.getElementById('step-confirm').classList.add('opacity-50', 'pointer-events-none');
            document.getElementById('step-book-indicator').classList.remove('bg-blue-600');
            document.getElementById('step-book-indicator').classList.add('bg-gray-400');
            document.getElementById('step-confirm-indicator').classList.remove('bg-blue-600');
            document.getElementById('step-confirm-indicator').classList.add('bg-gray-400');
        }
    </script>
</x-app-layout>
