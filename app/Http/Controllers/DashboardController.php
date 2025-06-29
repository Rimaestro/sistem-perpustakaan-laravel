<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\Book;
use App\Models\Member;
use App\Models\Loan;
use Carbon\Carbon;

class DashboardController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
        ];
    }

    /**
     * Display the dashboard
     */
    public function index()
    {
        // Update overdue status before showing dashboard
        $this->updateOverdueStatus();

        // Get basic statistics
        $stats = $this->getBasicStatistics();
        
        // Get recent activities
        $recentLoans = $this->getRecentLoans();
        $recentReturns = $this->getRecentReturns();
        
        // Get overdue alerts
        $overdueLoans = $this->getOverdueLoans(5);
        
        // Get chart data
        $chartData = $this->getChartData();

        return view('dashboard', compact(
            'stats',
            'recentLoans',
            'recentReturns',
            'overdueLoans',
            'chartData'
        ));
    }

    /**
     * Get basic statistics for dashboard
     */
    private function getBasicStatistics(): array
    {
        return [
            'total_books' => Book::count(),
            'available_books' => Book::where('status', 'available')->count(),
            'borrowed_books' => Book::where('status', 'borrowed')->count(),
            'total_members' => Member::count(),
            'active_members' => Member::where('status', 'active')->count(),
            'total_loans' => Loan::count(),
            'active_loans' => Loan::where('status', 'active')->count(),
            'overdue_loans' => Loan::where('status', 'overdue')->count(),
            'loans_today' => Loan::whereDate('loan_date', today())->count(),
            'returns_today' => Loan::whereDate('return_date', today())->count(),
            'total_fines' => Loan::sum('fine_amount'),
            'books_low_stock' => Book::where('available_quantity', '<=', 2)->where('available_quantity', '>', 0)->count(),
        ];
    }

    /**
     * Get recent loan transactions
     */
    private function getRecentLoans(int $limit = 5)
    {
        return Loan::with(['book', 'member', 'processedBy'])
                   ->latest('loan_date')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get recent return transactions
     */
    private function getRecentReturns(int $limit = 5)
    {
        return Loan::with(['book', 'member'])
                   ->where('status', 'returned')
                   ->whereNotNull('return_date')
                   ->latest('return_date')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get overdue loans for alerts
     */
    private function getOverdueLoans(int $limit = 10)
    {
        return Loan::with(['book', 'member'])
                   ->where('status', 'overdue')
                   ->orderBy('due_date', 'asc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get chart data for dashboard
     */
    private function getChartData(): array
    {
        // Loans per day for the last 7 days
        $loansPerDay = [];
        $returnsPerDay = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            
            $loansPerDay[] = [
                'date' => $date->format('d/m'),
                'count' => Loan::whereDate('loan_date', $dateString)->count()
            ];
            
            $returnsPerDay[] = [
                'date' => $date->format('d/m'),
                'count' => Loan::whereDate('return_date', $dateString)->count()
            ];
        }

        // Books by category
        $booksByCategory = Book::selectRaw('categories.name as category, COUNT(*) as count')
                              ->join('categories', 'books.category_id', '=', 'categories.id')
                              ->groupBy('categories.id', 'categories.name')
                              ->orderBy('count', 'desc')
                              ->limit(5)
                              ->get()
                              ->toArray();

        // Member registration per month (last 6 months)
        $memberRegistrations = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $memberRegistrations[] = [
                'month' => $date->format('M Y'),
                'count' => Member::whereYear('join_date', $date->year)
                                ->whereMonth('join_date', $date->month)
                                ->count()
            ];
        }

        return [
            'loans_per_day' => $loansPerDay,
            'returns_per_day' => $returnsPerDay,
            'books_by_category' => $booksByCategory,
            'member_registrations' => $memberRegistrations,
        ];
    }

    /**
     * Update overdue status for active loans
     */
    private function updateOverdueStatus(): void
    {
        Loan::where('status', 'active')
            ->where('due_date', '<', now())
            ->update([
                'status' => 'overdue',
                'updated_at' => now()
            ]);

        // Update fine amounts for overdue loans
        $overdueLoans = Loan::where('status', 'overdue')->get();
        
        foreach ($overdueLoans as $loan) {
            // Hitung hari terlambat dengan benar - pastikan integer
            $daysOverdue = (int) $loan->due_date->diffInDays(now(), false);
            $newFine = $daysOverdue * 1000; // Rp 1000 per hari

            if ($loan->fine_amount != $newFine) {
                $loan->update(['fine_amount' => $newFine]);
            }
        }
    }

    /**
     * API: Get dashboard statistics
     */
    public function statistics()
    {
        $this->updateOverdueStatus();
        $stats = $this->getBasicStatistics();
        
        return response()->json($stats);
    }

    /**
     * API: Get overdue alerts
     */
    public function overdueAlerts(Request $request)
    {
        $limit = $request->get('limit', 10);
        $overdueLoans = $this->getOverdueLoans($limit);
        
        $alerts = $overdueLoans->map(function ($loan) {
            // Hitung hari terlambat dengan benar - pastikan integer
            $daysOverdue = (int) $loan->due_date->diffInDays(now(), false);

            return [
                'id' => $loan->id,
                'book_title' => $loan->book->title,
                'member_name' => $loan->member->name,
                'member_id' => $loan->member->member_id,
                'due_date' => $loan->due_date->format('Y-m-d'),
                'due_date_formatted' => $loan->due_date->format('d/m/Y'),
                'days_overdue' => $daysOverdue,
                'fine_amount' => $loan->fine_amount,
                'loan_url' => route('loans.show', $loan),
            ];
        });

        return response()->json($alerts);
    }

    /**
     * API: Get chart data
     */
    public function chartData()
    {
        $chartData = $this->getChartData();
        return response()->json($chartData);
    }

    /**
     * Send overdue notifications (for future implementation)
     */
    public function sendOverdueNotifications()
    {
        $overdueLoans = Loan::with(['book', 'member'])
                           ->where('status', 'overdue')
                           ->get();

        $notificationsSent = 0;

        foreach ($overdueLoans as $loan) {
            // Here you can implement email/SMS notifications
            // For now, we'll just log the notification
            \Log::info("Overdue notification for loan #{$loan->id}: {$loan->book->title} - {$loan->member->name}");
            $notificationsSent++;
        }

        return response()->json([
            'message' => "Sent {$notificationsSent} overdue notifications",
            'count' => $notificationsSent
        ]);
    }
}
