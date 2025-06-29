<x-app-layout title="Tambah Buku Baru">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('books.index') }}" class="hover:text-gray-700">Manajemen Buku</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span>Tambah Buku</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Buku Baru</h1>
            <p class="mt-1 text-sm text-gray-600">Tambahkan buku baru ke koleksi perpustakaan</p>
        </div>

        <!-- Form -->
        <x-card>
            <form action="{{ route('books.store') }}" method="POST" class="space-y-6">
                @csrf

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
                            required 
                        />

                        <x-input 
                            name="author" 
                            label="Penulis" 
                            placeholder="Masukkan nama penulis"
                            required 
                        />

                        <x-input 
                            name="isbn" 
                            label="ISBN" 
                            placeholder="978-xxx-xxx-xxx-x"
                        />

                        <!-- Category Field -->
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="category_id"
                                id="category_id"
                                required
                                class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('category_id') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                            >
                                <option value="">Pilih kategori buku</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-input 
                            type="number" 
                            name="publication_year" 
                            label="Tahun Terbit" 
                            placeholder="{{ date('Y') }}"
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
                            label="Jumlah Buku" 
                            placeholder="1"
                            min="1"
                            required 
                        />

                        <div class="mb-4">
                            <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                            <input
                                type="text"
                                name="barcode"
                                id="barcode"
                                value="{{ old('barcode') }}"
                                placeholder="Kosongkan untuk auto-generate"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                onchange="updateBarcodePreview(this.value)"
                            >
                            @if($errors->has('barcode'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->first('barcode') }}</p>
                            @endif

                            <!-- Barcode Preview -->
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Preview Barcode</label>
                                <div id="barcode-preview">
                                    <x-barcode-generator value="" />
                                </div>
                            </div>
                        </div>

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
                            >{{ old('description') }}</textarea>
                            @if($errors->has('description'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->first('description') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informasi Tambahan -->
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
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Barcode akan di-generate otomatis jika tidak diisi</li>
                                    <li>Status buku akan otomatis diset ke "Tersedia"</li>
                                    <li>Jumlah tersedia akan sama dengan jumlah total</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <x-button href="{{ route('books.index') }}" variant="outline">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Buku
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

    <script>
    function updateBarcodePreview(value) {
        const preview = document.getElementById('barcode-preview');
        if (value && value.length > 0) {
            preview.innerHTML = '<div id="barcode-preview-canvas" class="text-center"></div>';
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode("#barcode-preview-canvas", value, {
                    format: "CODE128",
                    width: 2,
                    height: 40,
                    displayValue: true
                });
            }
        } else {
            preview.innerHTML = `
                <div class="bg-gray-100 p-4 rounded border-2 border-dashed border-gray-300">
                    <div class="text-center text-gray-500">
                        <svg class="mx-auto h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                        <p class="text-sm">Barcode akan di-generate otomatis</p>
                    </div>
                </div>
            `;
        }
    }
    </script>
</x-app-layout>
