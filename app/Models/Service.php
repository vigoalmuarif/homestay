<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'category',
        'sub_category',
        'price',
        'has_stock',
        'stock_quantity',
        'is_active',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'has_stock' => 'boolean',
            'is_active' => 'boolean',
            'stock_quantity' => 'integer',
        ];
    }

    /**
     * Room type inclusions linked to this service.
     */
    public function inclusions(): HasMany
    {
        return $this->hasMany(RoomTypeServiceInclusion::class);
    }

    /**
     * Booking items associated with this service.
     */
    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    /**
     * Scope only active services.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category.
     */
    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (blank($category) || $category === 'all') {
            return $query;
        }

        return $query->where('category', $category);
    }

    /**
     * Scope search by name or sub_category.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('sub_category', 'like', "%{$keyword}%")
                ->orWhere('category', 'like', "%{$keyword}%");
        });
    }

    /**
     * Scope items with physical stock tracking.
     */
    public function scopePhysicalStock(Builder $query): Builder
    {
        return $query->where('has_stock', true);
    }
}

