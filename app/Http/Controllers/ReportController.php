<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\Loan;
use App\Models\Book;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller implements HasMiddleware
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
     * Display loan statistics dashboard
     */
    public function loanStatistics(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = Carbon::now()->subDays($period);
        
        // Basic statistics
        $stats = [
            'total_loans' => Loan::count(),
            'active_loans' => Loan::where('status', 'active')->count(),
            'overdue_loans' => Loan::where('status', 'overdue')->count(),
            'returned_loans' => Loan::where('status', 'returned')->count(),
            'total_fines' => Loan::sum('fine_amount'),
            'period_loans' => Loan::where('loan_date', '>=', $startDate)->count(),
            'period_returns' => Loan::where('return_date', '>=', $startDate)->count(),
        ];

        // Loans by day (last 30 days)
        $loansByDay = Loan::select(
                DB::raw('DATE(loan_date) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('loan_date', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Returns by day (last 30 days)
        $returnsByDay = Loan::select(
                DB::raw('DATE(return_date) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('return_date', '>=', $startDate)
            ->whereNotNull('return_date')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Most borrowed books - using subquery approach for better compatibility
        $popularBooksSubquery = DB::table('loans')
            ->select('book_id', DB::raw('COUNT(*) as loan_count'))
            ->groupBy('book_id')
            ->orderBy('loan_count', 'desc')
            ->limit(10);

        $popularBooks = Book::select('books.*', 'loan_stats.loan_count')
            ->joinSub($popularBooksSubquery, 'loan_stats', function ($join) {
                $join->on('books.id', '=', 'loan_stats.book_id');
            })
            ->orderBy('loan_stats.loan_count', 'desc')
            ->get();

        // Most active members - using subquery approach for better compatibility
        $activeMembersSubquery = DB::table('loans')
            ->select('member_id', DB::raw('COUNT(*) as loan_count'))
            ->groupBy('member_id')
            ->orderBy('loan_count', 'desc')
            ->limit(10);

        $activeMembers = Member::select('members.*', 'loan_stats.loan_count')
            ->joinSub($activeMembersSubquery, 'loan_stats', function ($join) {
                $join->on('members.id', '=', 'loan_stats.member_id');
            })
            ->orderBy('loan_stats.loan_count', 'desc')
            ->get();

        // Overdue analysis
        $overdueAnalysis = Loan::select(
                DB::raw('DATEDIFF(NOW(), due_date) as days_overdue'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(fine_amount) as total_fine')
            )
            ->where('status', 'overdue')
            ->groupBy('days_overdue')
            ->orderBy('days_overdue')
            ->get();

        return view('pages.reports.loan-statistics', compact(
            'stats',
            'loansByDay',
            'returnsByDay',
            'popularBooks',
            'activeMembers',
            'overdueAnalysis',
            'period'
        ));
    }

    /**
     * Display transaction history report
     */
    public function transactionHistory(Request $request)
    {
        $query = Loan::with(['book', 'member', 'processedBy']);

        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('loan_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('loan_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        $transactions = $query->orderBy('loan_date', 'desc')->paginate(20);

        // Summary statistics for filtered data
        $summary = [
            'total_transactions' => $query->count(),
            'total_fines' => $query->sum('fine_amount'),
            'avg_loan_duration' => $query->whereNotNull('return_date')
                ->selectRaw('AVG(DATEDIFF(return_date, loan_date)) as avg_duration')
                ->value('avg_duration'),
        ];

        return view('pages.reports.transaction-history', compact('transactions', 'summary'));
    }

    /**
     * Display overdue report
     */
    public function overdueReport(Request $request)
    {
        $query = Loan::with(['book', 'member'])
            ->where('status', 'overdue');

        // Apply filters
        if ($request->filled('days_overdue')) {
            $daysOverdue = $request->days_overdue;
            $query->whereRaw('DATEDIFF(NOW(), due_date) >= ?', [$daysOverdue]);
        }

        if ($request->filled('min_fine')) {
            $query->where('fine_amount', '>=', $request->min_fine);
        }

        $overdueLoans = $query->orderBy('due_date', 'asc')->paginate(20);

        // Summary
        $summary = [
            'total_overdue' => $query->count(),
            'total_fines' => $query->sum('fine_amount'),
            'avg_days_overdue' => $query->selectRaw('AVG(DATEDIFF(NOW(), due_date)) as avg_days')
                ->value('avg_days'),
            'max_days_overdue' => $query->selectRaw('MAX(DATEDIFF(NOW(), due_date)) as max_days')
                ->value('max_days'),
        ];

        return view('pages.reports.overdue-report', compact('overdueLoans', 'summary'));
    }

    /**
     * Display member activity report
     */
    public function memberActivity(Request $request)
    {
        $period = $request->get('period', '90'); // days
        $startDate = Carbon::now()->subDays($period);

        $query = Member::select('members.*')
            ->withCount([
                'loans as total_loans',
                'loans as active_loans' => function ($query) {
                    $query->where('status', 'active');
                },
                'loans as overdue_loans' => function ($query) {
                    $query->where('status', 'overdue');
                },
                'loans as period_loans' => function ($query) use ($startDate) {
                    $query->where('loan_date', '>=', $startDate);
                }
            ])
            ->withSum('loans as total_fines', 'fine_amount');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('min_loans')) {
            $query->having('total_loans', '>=', $request->min_loans);
        }

        $members = $query->orderBy('total_loans', 'desc')->paginate(20);

        return view('pages.reports.member-activity', compact('members', 'period'));
    }

    /**
     * Display book circulation report
     */
    public function bookCirculation(Request $request)
    {
        $period = $request->get('period', '90'); // days
        $startDate = Carbon::now()->subDays($period);

        $query = Book::select('books.*')
            ->withCount([
                'loans as total_loans',
                'loans as active_loans' => function ($query) {
                    $query->where('status', 'active');
                },
                'loans as period_loans' => function ($query) use ($startDate) {
                    $query->where('loan_date', '>=', $startDate);
                }
            ])
            ->with('category');

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('min_loans')) {
            $query->having('total_loans', '>=', $request->min_loans);
        }

        $books = $query->orderBy('total_loans', 'desc')->paginate(20);

        // Category statistics
        $categoryStats = Book::select('categories.name', DB::raw('COUNT(loans.id) as loan_count'))
            ->leftJoin('loans', 'books.id', '=', 'loans.book_id')
            ->leftJoin('categories', 'books.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('loan_count', 'desc')
            ->get();

        return view('pages.reports.book-circulation', compact('books', 'categoryStats', 'period'));
    }

    /**
     * API: Get loan statistics data
     */
    public function loanStatisticsData(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);

        // Daily loan/return data
        $dailyData = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            
            $dailyData[] = [
                'date' => $date->format('d/m'),
                'loans' => Loan::whereDate('loan_date', $dateString)->count(),
                'returns' => Loan::whereDate('return_date', $dateString)->count(),
            ];
        }

        // Status distribution
        $statusData = [
            'active' => Loan::where('status', 'active')->count(),
            'overdue' => Loan::where('status', 'overdue')->count(),
            'returned' => Loan::where('status', 'returned')->count(),
        ];

        // Monthly trends (last 12 months)
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'loans' => Loan::whereYear('loan_date', $date->year)
                             ->whereMonth('loan_date', $date->month)
                             ->count(),
                'returns' => Loan::whereYear('return_date', $date->year)
                               ->whereMonth('return_date', $date->month)
                               ->count(),
            ];
        }

        return response()->json([
            'daily' => $dailyData,
            'status' => $statusData,
            'monthly' => $monthlyData,
        ]);
    }

    /**
     * Export loan report to CSV
     */
    public function exportLoans(Request $request)
    {
        $query = Loan::with(['book', 'member', 'processedBy']);

        // Apply same filters as transaction history
        if ($request->filled('start_date')) {
            $query->where('loan_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('loan_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->orderBy('loan_date', 'desc')->get();

        $filename = 'loan_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($loans) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Tanggal Pinjam',
                'Tanggal Jatuh Tempo',
                'Tanggal Kembali',
                'Judul Buku',
                'Penulis',
                'Nama Anggota',
                'ID Anggota',
                'Status',
                'Denda',
                'Diproses Oleh'
            ]);

            // CSV data
            foreach ($loans as $loan) {
                fputcsv($file, [
                    $loan->id,
                    $loan->loan_date->format('d/m/Y'),
                    $loan->due_date->format('d/m/Y'),
                    $loan->return_date ? $loan->return_date->format('d/m/Y') : '',
                    $loan->book->title,
                    $loan->book->author,
                    $loan->member->name,
                    $loan->member->member_id,
                    $loan->status,
                    $loan->fine_amount,
                    $loan->processedBy->name
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
