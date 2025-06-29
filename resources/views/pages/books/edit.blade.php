<x-app-layout title="Edit Buku - {{ $book->title }}">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('books.index') }}" class="hover:text-gray-700">Manajemen Buku</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <a href="{{ route('books.show', $book) }}" class="hover:text-gray-700">{{ Str::limit($book->title, 30) }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span>Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Buku</h1>
            <p class="mt-1 text-sm text-gray-600">Perbarui informasi buku</p>
        </div>

        <!-- Form -->
        <x-card>
            <form action="{{ route('books.update', $book) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Alert untuk error -->
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informasi Dasar -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Dasar</h3>
                        
                        <x-input 
                            name="title" 
                            label="Judul Buku" 
                            placeholder="Masukkan judul buku"
                            value="{{ $book->title }}"
                            required 
                        />

                        <x-input 
                            name="author" 
                            label="Penulis" 
                            placeholder="Masukkan nama penulis"
                            value="{{ $book->author }}"
                            required 
                        />

                        <x-input 
                            name="isbn" 
                            label="ISBN" 
                            placeholder="978-xxx-xxx-xxx-x"
                            value="{{ $book->isbn }}"
                        />

                        <x-select 
                            name="category_id" 
                            label="Kategori" 
                            required
                            value="{{ $book->category_id }}"
                            placeholder="Pilih kategori buku"
                        >
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (old('category_id', $book->category_id) == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </x-select>

                        <x-input 
                            type="number" 
                            name="publication_year" 
                            label="Tahun Terbit" 
                            placeholder="{{ date('Y') }}"
                            value="{{ $book->publication_year }}"
                            min="1000"
                            max="{{ date('Y') }}"
                        />
                    </div>

                    <!-- Detail Tambahan -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Detail Tambahan</h3>
                        
                        <x-input 
                            type="number" 
                            name="quantity" 
                            label="Jumlah Total Buku" 
                            placeholder="1"
                            value="{{ $book->quantity }}"
                            min="1"
                            required 
                        />

                        <x-select 
                            name="status" 
                            label="Status Buku" 
                            required
                            value="{{ $book->status }}"
                        >
                            <option value="available" {{ old('status', $book->status) == 'available' ? 'selected' : '' }}>Tersedia</option>
                            <option value="borrowed" {{ old('status', $book->status) == 'borrowed' ? 'selected' : '' }}>Dipinjam</option>
                            <option value="maintenance" {{ old('status', $book->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="lost" {{ old('status', $book->status) == 'lost' ? 'selected' : '' }}>Hilang</option>
                        </x-select>

                        <x-input 
                            name="barcode" 
                            label="Barcode" 
                            placeholder="Barcode buku"
                            value="{{ $book->barcode }}"
                        />

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Deskripsi
                            </label>
                            <textarea 
                                name="description" 
                                id="description" 
                                rows="4"
                                placeholder="Deskripsi singkat tentang buku..."
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >{{ old('description', $book->description) }}</textarea>
                            @if($errors->has('description'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->first('description') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Current Status Info -->
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Status Saat Ini</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Jumlah Total:</span>
                            <span class="font-medium text-gray-900 ml-1">{{ $book->quantity }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Tersedia:</span>
                            <span class="font-medium text-gray-900 ml-1">{{ $book->available_quantity }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Dipinjam:</span>
                            <span class="font-medium text-gray-900 ml-1">{{ $book->quantity - $book->available_quantity }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Status:</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                @if($book->status_color == 'green') bg-green-100 text-green-800
                                @elseif($book->status_color == 'yellow') bg-yellow-100 text-yellow-800
                                @elseif($book->status_color == 'blue') bg-blue-100 text-blue-800
                                @elseif($book->status_color == 'red') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif ml-1">
                                {{ $book->status_label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Warning untuk active loans -->
                @if($book->activeLoans->count() > 0)
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
                                    <p>Buku ini memiliki {{ $book->activeLoans->count() }} peminjaman aktif. Perubahan jumlah buku akan mempengaruhi ketersediaan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <x-button href="{{ route('books.show', $book) }}" variant="outline">
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
        </x-card>
    </div>
</x-app-layout>
