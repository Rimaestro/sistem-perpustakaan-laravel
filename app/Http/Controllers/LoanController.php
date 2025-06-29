<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\Loan;
use App\Models\Book;
use App\Models\Member;
use Carbon\Carbon;

class LoanController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            'role:admin,staff',
        ];
    }

    /**
     * Display a listing of loans
     */
    public function index(Request $request)
    {
        $query = Loan::with(['book.category', 'member', 'processedBy']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('book', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            })->orWhereHas('member', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('loan_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('loan_date', '<=', $request->date_to);
        }

        // Filter overdue
        if ($request->filled('overdue') && $request->overdue === '1') {
            $query->overdue();
        }

        // Sort
        $sortBy = $request->get('sort_by', 'loan_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $loans = $query->paginate(15)->withQueryString();

        // Update overdue status
        $this->updateOverdueStatus();

        return view('pages.loans.index', compact('loans'));
    }

    /**
     * Show the form for creating a new loan
     */
    public function create()
    {
        return view('pages.loans.create');
    }

    /**
     * Store a newly created loan
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|exists:books,id',
            'member_id' => 'required|exists:members,id',
            'loan_date' => 'required|date',
            'due_date' => 'nullable|date|after:loan_date',
            'notes' => 'nullable|string',
        ], [
            'book_id.required' => 'Buku wajib dipilih.',
            'book_id.exists' => 'Buku tidak ditemukan.',
            'member_id.required' => 'Anggota wajib dipilih.',
            'member_id.exists' => 'Anggota tidak ditemukan.',
            'loan_date.required' => 'Tanggal peminjaman wajib diisi.',
            'loan_date.date' => 'Format tanggal tidak valid.',
            'due_date.date' => 'Format tanggal jatuh tempo tidak valid.',
            'due_date.after' => 'Tanggal jatuh tempo harus setelah tanggal peminjaman.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate business rules
        $book = Book::findOrFail($request->book_id);
        $member = Member::findOrFail($request->member_id);

        $businessValidation = $this->validateLoanBusinessRules($book, $member);
        if ($businessValidation !== true) {
            return redirect()->back()
                ->with('error', $businessValidation)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $book) {
                // Create loan
                $loan = Loan::create([
                    'book_id' => $request->book_id,
                    'member_id' => $request->member_id,
                    'loan_date' => $request->loan_date,
                    'due_date' => $request->due_date ?: Carbon::parse($request->loan_date)->addDays(7),
                    'notes' => $request->notes,
                    'processed_by' => auth()->id(),
                ]);

                // Update book availability
                $book->decrement('available_quantity');
                if ($book->available_quantity <= 0) {
                    $book->update(['status' => 'borrowed']);
                }
            });

            return redirect()->route('loans.index')
                ->with('success', 'Peminjaman berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified loan
     */
    public function show(Loan $loan)
    {
        $loan->load(['book.category', 'member', 'processedBy']);
        
        // Update fine if overdue
        if ($loan->isOverdue() && $loan->status === 'active') {
            $loan->update([
                'fine_amount' => $loan->calculateFine(),
                'status' => 'overdue'
            ]);
        }

        return view('pages.loans.show', compact('loan'));
    }

    /**
     * Show the form for editing the specified loan
     */
    public function edit(Loan $loan)
    {
        if ($loan->status === 'returned') {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Tidak dapat mengedit peminjaman yang sudah dikembalikan.');
        }

        return view('pages.loans.edit', compact('loan'));
    }

    /**
     * Update the specified loan
     */
    public function update(Request $request, Loan $loan)
    {
        if ($loan->status === 'returned') {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Tidak dapat mengedit peminjaman yang sudah dikembalikan.');
        }

        $validator = Validator::make($request->all(), [
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
            'fine_amount' => 'nullable|numeric|min:0',
        ], [
            'due_date.required' => 'Tanggal jatuh tempo wajib diisi.',
            'due_date.date' => 'Format tanggal tidak valid.',
            'fine_amount.numeric' => 'Denda harus berupa angka.',
            'fine_amount.min' => 'Denda tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $loan->update([
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'fine_amount' => $request->fine_amount ?? $loan->fine_amount,
            ]);

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Data peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Process book return
     */
    public function return(Request $request, Loan $loan)
    {
        if ($loan->status === 'returned') {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Buku sudah dikembalikan sebelumnya.');
        }

        $validator = Validator::make($request->all(), [
            'return_date' => 'required|date',
            'notes' => 'nullable|string',
            'fine_amount' => 'nullable|numeric|min:0',
        ], [
            'return_date.required' => 'Tanggal pengembalian wajib diisi.',
            'return_date.date' => 'Format tanggal tidak valid.',
            'fine_amount.numeric' => 'Denda harus berupa angka.',
            'fine_amount.min' => 'Denda tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $loan) {
                // Calculate fine if overdue
                $returnDate = Carbon::parse($request->return_date);
                $fineAmount = $request->fine_amount;
                
                if (!$fineAmount && $returnDate->gt($loan->due_date)) {
                    $daysOverdue = $returnDate->diffInDays($loan->due_date);
                    $fineAmount = $daysOverdue * 1000; // Rp 1000 per day
                }

                // Update loan
                $loan->update([
                    'return_date' => $request->return_date,
                    'status' => 'returned',
                    'fine_amount' => $fineAmount ?? 0,
                    'notes' => $request->notes,
                ]);

                // Update book availability
                $book = $loan->book;
                $book->increment('available_quantity');
                if ($book->available_quantity > 0 && $book->status === 'borrowed') {
                    $book->update(['status' => 'available']);
                }
            });

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Buku berhasil dikembalikan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Validate business rules for loan creation
     */
    private function validateLoanBusinessRules(Book $book, Member $member): string|bool
    {
        // Check if book is available
        if (!$book->isAvailable()) {
            return 'Buku tidak tersedia untuk dipinjam.';
        }

        // Check if member can borrow
        if (!$member->canBorrow()) {
            if ($member->status !== 'active') {
                return 'Anggota tidak aktif. Tidak dapat meminjam buku.';
            }
            if ($member->activeLoans()->count() >= 3) {
                return 'Anggota sudah mencapai batas maksimal peminjaman (3 buku).';
            }
        }

        // Check if member has overdue books
        if ($member->hasOverdueBooks()) {
            return 'Anggota memiliki buku yang terlambat dikembalikan. Harap kembalikan terlebih dahulu.';
        }

        // Check if member already borrowed this book
        $existingLoan = Loan::where('book_id', $book->id)
                           ->where('member_id', $member->id)
                           ->where('status', 'active')
                           ->exists();
        
        if ($existingLoan) {
            return 'Anggota sudah meminjam buku ini dan belum mengembalikannya.';
        }

        return true;
    }

    /**
     * Update overdue status for active loans
     */
    private function updateOverdueStatus(): void
    {
        Loan::where('status', 'active')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }

    /**
     * Quick loan form (for barcode scanning)
     */
    public function quickLoan()
    {
        return view('pages.loans.quick-loan');
    }

    /**
     * Quick return form (for barcode scanning)
     */
    public function quickReturn()
    {
        return view('pages.loans.quick-return');
    }

    /**
     * API: Search books for loan
     */
    public function searchBooks(Request $request)
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
                    ->available()
                    ->search($query)
                    ->limit($limit)
                    ->get(['id', 'title', 'author', 'isbn', 'barcode', 'category_id', 'available_quantity'])
                    ->map(function ($book) {
                        return [
                            'id' => $book->id,
                            'title' => $book->title,
                            'author' => $book->author,
                            'isbn' => $book->isbn,
                            'barcode' => $book->barcode,
                            'category' => $book->category->name,
                            'available_quantity' => $book->available_quantity,
                            'display' => $book->title . ' - ' . $book->author,
                        ];
                    });

        return response()->json($books);
    }

    /**
     * API: Search members for loan
     */
    public function searchMembers(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $members = Member::search($query)
                        ->active()
                        ->limit($limit)
                        ->get(['id', 'member_id', 'name', 'email'])
                        ->map(function ($member) {
                            return [
                                'id' => $member->id,
                                'member_id' => $member->member_id,
                                'name' => $member->name,
                                'email' => $member->email,
                                'can_borrow' => $member->canBorrow(),
                                'active_loans_count' => $member->activeLoans()->count(),
                                'display' => $member->name . ' (' . $member->member_id . ')',
                            ];
                        });

        return response()->json($members);
    }

    /**
     * API: Get loan statistics
     */
    public function statistics()
    {
        $stats = [
            'total_loans' => Loan::count(),
            'active_loans' => Loan::where('status', 'active')->count(),
            'overdue_loans' => Loan::overdue()->count(),
            'returned_loans' => Loan::where('status', 'returned')->count(),
            'total_fines' => Loan::sum('fine_amount'),
            'loans_today' => Loan::whereDate('loan_date', today())->count(),
            'returns_today' => Loan::whereDate('return_date', today())->count(),
        ];

        return response()->json($stats);
    }

    /**
     * API: Get overdue loans
     */
    public function overdueLoans()
    {
        // Update overdue status first
        Loan::where('status', 'active')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        $overdueLoans = Loan::with(['book', 'member'])
                           ->overdue()
                           ->orderBy('due_date', 'asc')
                           ->limit(10)
                           ->get()
                           ->map(function ($loan) {
                               // Hitung hari terlambat dengan benar - pastikan integer
                               $daysOverdue = (int) $loan->due_date->diffInDays(now(), false);

                               // Update fine amount
                               $fineAmount = $daysOverdue * 1000;
                               if ($loan->fine_amount != $fineAmount) {
                                   $loan->update(['fine_amount' => $fineAmount]);
                               }

                               return [
                                   'id' => $loan->id,
                                   'book_title' => $loan->book->title,
                                   'member_name' => $loan->member->name,
                                   'member_id' => $loan->member->member_id,
                                   'due_date' => $loan->due_date->format('Y-m-d'),
                                   'due_date_formatted' => $loan->due_date->format('d/m/Y'),
                                   'days_overdue' => $daysOverdue,
                                   'fine_amount' => $fineAmount,
                               ];
                           });

        return response()->json($overdueLoans);
    }

    /**
     * API: Find loan by barcode
     */
    public function findByBarcode(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $barcode = $request->get('barcode');

        if (!$barcode) {
            return response()->json(['error' => 'Barcode tidak boleh kosong'], 400);
        }

        // Find book by barcode
        $book = Book::where('barcode', $barcode)->first();

        if (!$book) {
            return response()->json(['error' => 'Buku dengan barcode tersebut tidak ditemukan'], 404);
        }

        // Find active loan for this book
        $loan = Loan::with(['member', 'book'])
                   ->where('book_id', $book->id)
                   ->where('status', 'active')
                   ->first();

        if (!$loan) {
            return response()->json(['error' => 'Tidak ada peminjaman aktif untuk buku ini'], 404);
        }

        return response()->json([
            'loan' => [
                'id' => $loan->id,
                'loan_date' => $loan->loan_date->format('Y-m-d'),
                'due_date' => $loan->due_date->format('Y-m-d'),
                'is_overdue' => $loan->isOverdue(),
                'fine_amount' => $loan->calculateFine(),
            ],
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'barcode' => $book->barcode,
            ],
            'member' => [
                'id' => $loan->member->id,
                'name' => $loan->member->name,
                'member_id' => $loan->member->member_id,
            ]
        ]);
    }
}
