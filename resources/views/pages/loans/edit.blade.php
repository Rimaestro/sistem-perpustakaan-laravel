<x-app-layout title="Edit Peminjaman">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Peminjaman</h1>
                <p class="mt-1 text-sm text-gray-600">Perbarui data transaksi peminjaman</p>
            </div>
            <div class="flex items-center space-x-3">
                <x-button href="{{ route('loans.show', $loan) }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Detail
                </x-button>
                <x-button href="{{ route('loans.index') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </x-button>
            </div>
        </div>

        <!-- Loan Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-blue-900">{{ $loan->book->title }}</h3>
                            <p class="text-sm text-blue-700">Dipinjam oleh {{ $loan->member->name }} ({{ $loan->member->member_id }})</p>
                        </div>
                        <div class="text-right">
                            @if($loan->status === 'active')
                                @if($loan->isOverdue())
                                    <x-status-badge status="overdue" color="red">Terlambat</x-status-badge>
                                @else
                                    <x-status-badge status="active" color="blue">Aktif</x-status-badge>
                                @endif
                            @else
                                <x-status-badge status="overdue" color="red">Terlambat</x-status-badge>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form method="POST" action="{{ route('loans.update', $loan) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="due_date"
                            id="due_date"
                            value="{{ old('due_date', $loan->due_date->format('Y-m-d')) }}"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('due_date') border-red-300 @enderror"
                        >
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Tanggal peminjaman: {{ $loan->loan_date->format('d/m/Y') }}
                        </p>
                    </div>

                    <!-- Fine Amount -->
                    <div>
                        <label for="fine_amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Denda (Rp)
                        </label>
                        <input
                            type="number"
                            name="fine_amount"
                            id="fine_amount"
                            value="{{ old('fine_amount', $loan->fine_amount) }}"
                            min="0"
                            step="1000"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('fine_amount') border-red-300 @enderror"
                            placeholder="0"
                        >
                        @error('fine_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if($loan->isOverdue())
                            <p class="mt-1 text-xs text-gray-500">
                                Denda otomatis: Rp {{ number_format($loan->calculateFine(), 0, ',', '.') }}
                            </p>
                        @endif
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan
                        </label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="4"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('notes') border-red-300 @enderror"
                            placeholder="Catatan tambahan tentang peminjaman ini..."
                        >{{ old('notes', $loan->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Current Information Display -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Informasi Saat Ini</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Tanggal Peminjaman:</span>
                            <div class="font-medium text-gray-900">{{ $loan->loan_date->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">Jatuh Tempo Saat Ini:</span>
                            <div class="font-medium text-gray-900">{{ $loan->due_date->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">Status:</span>
                            <div class="font-medium text-gray-900">
                                @if($loan->isOverdue())
                                    <span class="text-red-600">Terlambat {{ $loan->due_date->diffInDays(now()) }} hari</span>
                                @else
                                    <span class="text-green-600">{{ $loan->days_remaining }} hari lagi</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning for Overdue -->
                @if($loan->isOverdue())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Perhatian</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Peminjaman ini sudah terlambat. Pastikan untuk menghubungi anggota dan memproses pengembalian sesegera mungkin.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Info Section -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Informasi</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Anda hanya dapat mengubah tanggal jatuh tempo, denda, dan catatan</li>
                                    <li>Untuk mengembalikan buku, gunakan fitur pengembalian di halaman detail</li>
                                    <li>Denda akan dihitung otomatis berdasarkan keterlambatan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <x-button href="{{ route('loans.show', $loan) }}" variant="secondary">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Auto-calculate fine -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dueDateInput = document.getElementById('due_date');
            const fineAmountInput = document.getElementById('fine_amount');
            const loanDate = new Date('{{ $loan->loan_date->format('Y-m-d') }}');

            dueDateInput.addEventListener('change', function() {
                const dueDate = new Date(this.value);
                const today = new Date();
                
                if (today > dueDate) {
                    const daysOverdue = Math.ceil((today - dueDate) / (1000 * 60 * 60 * 24));
                    const calculatedFine = daysOverdue * 1000;
                    
                    if (!fineAmountInput.value || fineAmountInput.value == 0) {
                        fineAmountInput.value = calculatedFine;
                    }
                }
            });
        });
    </script>
</x-app-layout>
