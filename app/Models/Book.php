<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category_id',
        'status',
        'barcode',
        'publication_year',
        'description',
        'quantity',
        'available_quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'publication_year' => 'integer',
        'quantity' => 'integer',
        'available_quantity' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate barcode jika belum ada
        static::creating(function ($book) {
            if (empty($book->barcode)) {
                $book->barcode = static::generateBarcode();
            }
        });

        // Update status berdasarkan available_quantity
        static::saving(function ($book) {
            if ($book->available_quantity <= 0) {
                $book->status = 'borrowed';
            } elseif ($book->available_quantity > 0 && $book->status === 'borrowed') {
                $book->status = 'available';
            }
        });
    }

    /**
     * Relationship: Book belongs to Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship: Book has many Loans
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Relationship: Get active loans (not returned)
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->where('status', 'active');
    }

    /**
     * Scope: Available books
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available')
                    ->where('available_quantity', '>', 0);
    }

    /**
     * Scope: Search books by title, author, or ISBN
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('isbn', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by publication year
     */
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->where('publication_year', $year);
    }

    /**
     * Check if book is available for loan
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->available_quantity > 0;
    }

    /**
     * Check if book can be borrowed
     */
    public function canBeBorrowed(): bool
    {
        return $this->isAvailable() && !in_array($this->status, ['maintenance', 'lost']);
    }

    /**
     * Get formatted status
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'available' => 'Tersedia',
            'borrowed' => 'Dipinjam',
            'maintenance' => 'Maintenance',
            'lost' => 'Hilang',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'available' => 'green',
            'borrowed' => 'yellow',
            'maintenance' => 'blue',
            'lost' => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Generate unique barcode
     */
    public static function generateBarcode(): string
    {
        do {
            $barcode = 'BK' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Decrease available quantity (when borrowed)
     */
    public function decreaseQuantity(int $amount = 1): bool
    {
        if ($this->available_quantity >= $amount) {
            $this->available_quantity -= $amount;
            return $this->save();
        }
        return false;
    }

    /**
     * Increase available quantity (when returned)
     */
    public function increaseQuantity(int $amount = 1): bool
    {
        if ($this->available_quantity + $amount <= $this->quantity) {
            $this->available_quantity += $amount;
            return $this->save();
        }
        return false;
    }
}
