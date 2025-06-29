<x-app-layout title="Manajemen Buku">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Buku</h1>
                <p class="mt-1 text-sm text-gray-600">Kelola koleksi buku perpustakaan</p>
            </div>
            @if(auth()->user()->hasAnyRole(['admin', 'staff']))
                <div class="mt-4 sm:mt-0">
                    <x-button href="{{ route('books.create') }}" variant="primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Buku
                    </x-button>
                </div>
            @endif
        </div>

        <!-- Advanced Search Section -->
        <x-advanced-search
            :action="route('books.index')"
            title="Pencarian & Filter Buku"
            :results-count="$books->total()"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Quick Search with Autocomplete -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Buku</label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Judul, penulis, ISBN, barcode..."
                        data-autocomplete="books"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                </div>

                <!-- Barcode Scanner -->
                <div>
                    <label for="barcode-scanner" class="block text-sm font-medium text-gray-700 mb-1">Scan Barcode</label>
                    <x-barcode-scanner
                        input-id="barcode-scanner"
                        placeholder="Scan atau ketik barcode..."
                        :on-scan="'function(barcode) {
                            document.getElementById(\'search\').value = barcode;
                            document.querySelector(\'form\').submit();
                        }'"
                    />
                </div>

                <!-- Author Filter -->
                <div>
                    <label for="author" class="block text-sm font-medium text-gray-700 mb-1">Penulis</label>
                    <input
                        type="text"
                        name="author"
                        id="author"
                        value="{{ request('author') }}"
                        placeholder="Nama penulis..."
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select
                        name="category_id"
                        id="category_id"
                        class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }} ({{ $category->books_count ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select
                        name="status"
                        id="status"
                        class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                        <option value="">Semua Status</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Hilang</option>
                    </select>
                </div>

                <!-- Year Range -->
                <div>
                    <label for="year_from" class="block text-sm font-medium text-gray-700 mb-1">Tahun Dari</label>
                    <input
                        type="number"
                        name="year_from"
                        id="year_from"
                        value="{{ request('year_from') }}"
                        placeholder="1990"
                        min="1000"
                        max="{{ date('Y') }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                </div>

                <div>
                    <label for="year_to" class="block text-sm font-medium text-gray-700 mb-1">Tahun Sampai</label>
                    <input
                        type="number"
                        name="year_to"
                        id="year_to"
                        value="{{ request('year_to') }}"
                        placeholder="{{ date('Y') }}"
                        min="1000"
                        max="{{ date('Y') }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                </div>

                <!-- Availability Filter -->
                <div>
                    <label for="availability" class="block text-sm font-medium text-gray-700 mb-1">Ketersediaan</label>
                    <select
                        name="availability"
                        id="availability"
                        class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                        <option value="">Semua</option>
                        <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Ada yang tersedia</option>
                        <option value="unavailable" {{ request('availability') == 'unavailable' ? 'selected' : '' }}>Tidak tersedia</option>
                    </select>
                </div>

                <!-- Sort Options -->
                <div>
                    <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Urutkan berdasarkan</label>
                    <select
                        name="sort_by"
                        id="sort_by"
                        class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                        <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Judul</option>
                        <option value="author" {{ request('sort_by') == 'author' ? 'selected' : '' }}>Penulis</option>
                        <option value="publication_year" {{ request('sort_by') == 'publication_year' ? 'selected' : '' }}>Tahun Terbit</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal Ditambahkan</option>
                        <option value="available_quantity" {{ request('sort_by') == 'available_quantity' ? 'selected' : '' }}>Ketersediaan</option>
                    </select>
                </div>
            </div>

            <!-- Sort Order (hidden input, controlled by toggle) -->
            <input type="hidden" name="sort_order" value="{{ request('sort_order', 'asc') }}">

            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="sort_desc"
                            value="1"
                            {{ request('sort_order') == 'desc' ? 'checked' : '' }}
                            onchange="this.form.querySelector('input[name=sort_order]').value = this.checked ? 'desc' : 'asc'"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                        <span class="ml-2 text-sm text-gray-600">Urutan terbalik (Z-A, terbaru-terlama)</span>
                    </label>
                </div>
            </div>
        </x-advanced-search>

        <!-- Books Table -->
        <x-card>
            @if($books->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buku</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ketersediaan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($books as $book)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $book->title }}</div>
                                            <div class="text-sm text-gray-500">{{ $book->author }}</div>
                                            @if($book->publication_year)
                                                <div class="text-xs text-gray-400">{{ $book->publication_year }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $book->category->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($book->status_color == 'green') bg-green-100 text-green-800
                                            @elseif($book->status_color == 'yellow') bg-yellow-100 text-yellow-800
                                            @elseif($book->status_color == 'blue') bg-blue-100 text-blue-800
                                            @elseif($book->status_color == 'red') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $book->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $book->available_quantity }}/{{ $book->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $book->barcode ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <x-button href="{{ route('books.show', $book) }}" variant="outline" size="sm">
                                                Detail
                                            </x-button>
                                            @if(auth()->user()->hasAnyRole(['admin', 'staff']))
                                                <x-button href="{{ route('books.edit', $book) }}" variant="secondary" size="sm">
                                                    Edit
                                                </x-button>
                                                @if(auth()->user()->hasRole('admin'))
                                                    <form action="{{ route('books.destroy', $book) }}" method="POST" class="inline" 
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-button type="submit" variant="danger" size="sm">
                                                            Hapus
                                                        </x-button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $books->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada buku</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan buku pertama ke perpustakaan.</p>
                    @if(auth()->user()->hasAnyRole(['admin', 'staff']))
                        <div class="mt-6">
                            <x-button href="{{ route('books.create') }}" variant="primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Tambah Buku
                            </x-button>
                        </div>
                    @endif
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
