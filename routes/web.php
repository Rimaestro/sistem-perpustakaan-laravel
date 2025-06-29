<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Book Management Routes
    Route::resource('books', BookController::class);

    // Member Management Routes
    Route::resource('members', MemberController::class);
    Route::get('/members/{member}/card', [MemberController::class, 'card'])->name('members.card');
    Route::get('/members/{member}/loan-history', [MemberController::class, 'loanHistory'])->name('members.loan-history');

    // Loan Transaction Routes
    Route::resource('loans', LoanController::class);
    Route::post('/loans/{loan}/return', [LoanController::class, 'return'])->name('loans.return');
    Route::get('/quick-loan', [LoanController::class, 'quickLoan'])->name('loans.quick-loan');
    Route::get('/quick-return', [LoanController::class, 'quickReturn'])->name('loans.quick-return');

    // Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/loan-statistics', [ReportController::class, 'loanStatistics'])->name('loan-statistics');
        Route::get('/transaction-history', [ReportController::class, 'transactionHistory'])->name('transaction-history');
        Route::get('/overdue-report', [ReportController::class, 'overdueReport'])->name('overdue-report');
        Route::get('/member-activity', [ReportController::class, 'memberActivity'])->name('member-activity');
        Route::get('/book-circulation', [ReportController::class, 'bookCirculation'])->name('book-circulation');
        Route::get('/export-loans', [ReportController::class, 'exportLoans'])->name('export-loans');
    });

    // API Routes for Book Search
    Route::get('/api/books/search', [BookController::class, 'search'])->name('api.books.search');
    Route::get('/api/books/years', [BookController::class, 'getAvailableYears'])->name('api.books.years');
    Route::get('/api/books/authors', [BookController::class, 'getPopularAuthors'])->name('api.books.authors');

    // API Routes for Member Search
    Route::get('/api/members/search', [MemberController::class, 'search'])->name('api.members.search');
    Route::get('/api/members/statistics', [MemberController::class, 'statistics'])->name('api.members.statistics');

    // API Routes for Loan Transactions
    Route::get('/api/loans/search-books', [LoanController::class, 'searchBooks'])->name('api.loans.search-books');
    Route::get('/api/loans/search-members', [LoanController::class, 'searchMembers'])->name('api.loans.search-members');
    Route::get('/api/loans/statistics', [LoanController::class, 'statistics'])->name('api.loans.statistics');
    Route::get('/api/loans/overdue', [LoanController::class, 'overdueLoans'])->name('api.loans.overdue');
    Route::get('/api/loans/find-by-barcode', [LoanController::class, 'findByBarcode'])->name('api.loans.find-by-barcode');

    // API Routes for Dashboard
    Route::get('/api/dashboard/statistics', [DashboardController::class, 'statistics'])->name('api.dashboard.statistics');
    Route::get('/api/dashboard/overdue-alerts', [DashboardController::class, 'overdueAlerts'])->name('api.dashboard.overdue-alerts');
    Route::get('/api/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('api.dashboard.chart-data');

    // API Routes for Reports
    Route::get('/api/reports/loan-statistics-data', [ReportController::class, 'loanStatisticsData'])->name('api.reports.loan-statistics-data');

    // API Routes for Global Search
    Route::prefix('api/search')->name('api.search.')->group(function () {
        Route::get('/global', [SearchController::class, 'global'])->name('global');
        Route::get('/suggestions', [SearchController::class, 'suggestions'])->name('suggestions');
        Route::get('/filter-options', [SearchController::class, 'filterOptions'])->name('filter-options');
        Route::get('/advanced', [SearchController::class, 'advanced'])->name('advanced');
    });

    // Search Pages
    Route::get('/search', [SearchController::class, 'advanced'])->name('search.advanced');
});
