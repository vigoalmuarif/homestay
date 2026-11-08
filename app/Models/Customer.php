<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'identity_type',
        'identity_number',
        'phone_number',
        'email',
        'address',
        'country',
        'special_notes',
        'is_blacklisted',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_blacklisted' => 'boolean',
        ];
    }

    /**
     * Scope a query to search customers by name, phone, NIK, or email.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('full_name', 'like', "%{$keyword}%")
                ->orWhere('phone_number', 'like', "%{$keyword}%")
                ->orWhere('identity_number', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
        });
    }

    /**
     * Scope a query to only include active (non-blacklisted) customers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_blacklisted', false);
    }

    /**
     * Scope a query to only include blacklisted customers.
     */
    public function scopeBlacklisted(Builder $query): Builder
    {
        return $query->where('is_blacklisted', true);
    }

    /**
     * Accessor for name alias.
     */
    public function getNameAttribute(): string
    {
        return $this->full_name ?? '';
    }

    /**
     * Bookings made by this customer.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
