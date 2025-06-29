<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Helpers\DateHelper;

class Loan extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'member_id',
        'loan_date',
        'due_date',
        'return_date',
        'fine_amount',
        'status',
        'notes',
        'processed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Set default due date (7 days from loan date)
        static::creating(function ($loan) {
            if (empty($loan->due_date)) {
                $loan->due_date = Carbon::parse($loan->loan_date)->addDays(7);
            }
        });
    }

    /**
     * Relationship: Loan belongs to Book
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Relationship: Loan belongs to Member
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Relationship: Loan processed by User (staff/admin)
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope: Active loans (not returned)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Overdue loans
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function ($q) {
                        $q->where('status', 'active')
                          ->where('due_date', '<', now());
                    });
    }

    /**
     * Scope: Returned loans
     */
    public function scopeReturned(Builder $query): Builder
    {
        return $query->where('status', 'returned');
    }

    /**
     * Check if loan is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->status === 'active' && $this->due_date < now());
    }

    /**
     * Calculate fine amount
     */
    public function calculateFine(): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        // Hitung hari terlambat dengan benar - pastikan integer
        $daysOverdue = (int) $this->due_date->diffInDays(now(), false);
        $finePerDay = 1000; // Rp 1000 per hari

        return $daysOverdue * $finePerDay;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'active' => 'Aktif',
            'returned' => 'Dikembalikan',
            'overdue' => 'Terlambat',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'active' => 'blue',
            'returned' => 'green',
            'overdue' => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get days remaining or overdue
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->status === 'returned') {
            return 0;
        }

        // Jika sudah lewat due_date, return nilai negatif (terlambat)
        // Jika belum lewat due_date, return nilai positif (sisa hari)
        return (int) now()->diffInDays($this->due_date, false);
    }

    /**
     * Get days overdue (always positive)
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return DateHelper::calculateOverdueDays($this->due_date);
    }

    /**
     * Get formatted fine amount
     */
    public function getFormattedFineAttribute(): string
    {
        return DateHelper::formatCurrency($this->fine_amount);
    }

    /**
     * Get formatted due date
     */
    public function getFormattedDueDateAttribute(): string
    {
        return DateHelper::formatShort($this->due_date);
    }

    /**
     * Get formatted due date with day
     */
    public function getFormattedDueDateWithDayAttribute(): string
    {
        return DateHelper::formatWithDay($this->due_date);
    }
}
