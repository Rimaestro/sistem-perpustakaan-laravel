<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Member extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'name',
        'email',
        'phone',
        'address',
        'join_date',
        'status',
        'card_number',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'join_date' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate member_id
        static::creating(function ($member) {
            if (empty($member->member_id)) {
                $member->member_id = static::generateMemberId();
            }
            if (empty($member->card_number)) {
                $member->card_number = static::generateCardNumber();
            }
        });
    }

    /**
     * Relationship: Member belongs to User (optional)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Member has many Loans
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Relationship: Get active loans
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->where('status', 'active');
    }

    /**
     * Scope: Active members
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Search members
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('member_id', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Check if member can borrow books
     */
    public function canBorrow(): bool
    {
        return $this->status === 'active' && $this->activeLoans()->count() < 3; // Max 3 books
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'suspended' => 'Ditangguhkan',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'active' => 'green',
            'inactive' => 'gray',
            'suspended' => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Generate unique member ID
     */
    public static function generateMemberId(): string
    {
        $year = date('Y');
        $lastMember = static::where('member_id', 'like', "M{$year}%")
                           ->orderBy('member_id', 'desc')
                           ->first();

        if ($lastMember) {
            $lastNumber = (int) substr($lastMember->member_id, 5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'M' . $year . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique card number
     */
    public static function generateCardNumber(): string
    {
        do {
            $cardNumber = 'CARD' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('card_number', $cardNumber)->exists());

        return $cardNumber;
    }

    /**
     * Get member card data for display/printing
     */
    public function getCardData(): array
    {
        return [
            'member_id' => $this->member_id,
            'name' => $this->name,
            'card_number' => $this->card_number,
            'join_date' => $this->join_date->format('d/m/Y'),
            'status' => $this->status_label,
            'qr_code_data' => $this->member_id, // Data untuk QR code
        ];
    }

    /**
     * Get total books borrowed (all time)
     */
    public function getTotalBooksBorrowedAttribute(): int
    {
        return $this->loans()->count();
    }

    /**
     * Get current active loans count
     */
    public function getActiveLoansCountAttribute(): int
    {
        return $this->activeLoans()->count();
    }

    /**
     * Get overdue loans
     */
    public function overdueLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->where('status', 'overdue')
                    ->orWhere(function ($q) {
                        $q->where('status', 'active')
                          ->where('due_date', '<', now());
                    });
    }

    /**
     * Check if member has overdue books
     */
    public function hasOverdueBooks(): bool
    {
        return $this->overdueLoans()->exists();
    }

    /**
     * Get total fine amount
     */
    public function getTotalFineAttribute(): float
    {
        return $this->loans()->sum('fine_amount');
    }
}
