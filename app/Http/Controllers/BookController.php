<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\Book;
use App\Models\Category;

class BookController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('role:admin,staff', except: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::with('category');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by author
        if ($request->filled('author')) {
            $query->where('author', 'like', '%' . $request->author . '%');
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by publication year range
        if ($request->filled('year_from')) {
            $query->where('publication_year', '>=', $request->year_from);
        }
        if ($request->filled('year_to')) {
            $query->where('publication_year', '<=', $request->year_to);
        }

        // Filter by availability
        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->where('available_quantity', '>', 0);
            } elseif ($request->availability === 'unavailable') {
                $query->where('available_quantity', '<=', 0);
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'title');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $books = $query->paginate(10)->withQueryString();

        // Get categories with book count
        $categories = Category::withCount('books')->get();

        return view('pages.books.index', compact('books', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('pages.books.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:books,isbn',
            'category_id' => 'required|exists:categories,id',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'barcode' => 'nullable|string|unique:books,barcode',
        ], [
            'title.required' => 'Judul buku wajib diisi.',
            'author.required' => 'Penulis buku wajib diisi.',
            'isbn.unique' => 'ISBN sudah terdaftar.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'publication_year.integer' => 'Tahun terbit harus berupa angka.',
            'publication_year.min' => 'Tahun terbit tidak valid.',
            'publication_year.max' => 'Tahun terbit tidak boleh melebihi tahun ini.',
            'quantity.required' => 'Jumlah buku wajib diisi.',
            'quantity.min' => 'Jumlah buku minimal 1.',
            'barcode.unique' => 'Barcode sudah terdaftar.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $bookData = $request->all();
        $bookData['available_quantity'] = $bookData['quantity'];
        $bookData['status'] = 'available';

        Book::create($bookData);

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil ditambahkan ke perpustakaan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load(['category', 'loans.member', 'loans.processedBy']);
        return view('pages.books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('pages.books.edit', compact('book', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:books,isbn,' . $book->id,
            'category_id' => 'required|exists:categories,id',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:available,borrowed,maintenance,lost',
            'barcode' => 'nullable|string|unique:books,barcode,' . $book->id,
        ], [
            'title.required' => 'Judul buku wajib diisi.',
            'author.required' => 'Penulis buku wajib diisi.',
            'isbn.unique' => 'ISBN sudah terdaftar.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'publication_year.integer' => 'Tahun terbit harus berupa angka.',
            'publication_year.min' => 'Tahun terbit tidak valid.',
            'publication_year.max' => 'Tahun terbit tidak boleh melebihi tahun ini.',
            'quantity.required' => 'Jumlah buku wajib diisi.',
            'quantity.min' => 'Jumlah buku minimal 1.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'barcode.unique' => 'Barcode sudah terdaftar.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $bookData = $request->all();

        // Adjust available_quantity jika quantity berubah
        if ($request->quantity != $book->quantity) {
            $difference = $request->quantity - $book->quantity;
            $bookData['available_quantity'] = max(0, $book->available_quantity + $difference);
        }

        $book->update($bookData);

        return redirect()->route('books.index')
            ->with('success', 'Data buku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        // Check if book has active loans
        if ($book->activeLoans()->exists()) {
            return redirect()->back()
                ->with('error', 'Buku tidak dapat dihapus karena masih ada peminjaman aktif.');
        }

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil dihapus dari perpustakaan.');
    }

    /**
     * API endpoint untuk autocomplete search
     */
    public function search(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $books = Book::with('category')
            ->search($query)
            ->limit($limit)
            ->get(['id', 'title', 'author', 'isbn', 'barcode', 'category_id'])
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'isbn' => $book->isbn,
                    'barcode' => $book->barcode,
                    'category' => $book->category->name,
                    'display' => $book->title . ' - ' . $book->author,
                ];
            });

        return response()->json($books);
    }

    /**
     * Get available years for filter
     */
    public function getAvailableYears()
    {
        $years = Book::whereNotNull('publication_year')
            ->distinct()
            ->orderBy('publication_year', 'desc')
            ->pluck('publication_year')
            ->take(20);

        return response()->json($years);
    }

    /**
     * Get popular authors for filter
     */
    public function getPopularAuthors()
    {
        $authors = Book::select('author')
            ->groupBy('author')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(20)
            ->pluck('author');

        return response()->json($authors);
    }
}
