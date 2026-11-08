<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomTypeServiceInclusion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'room_type_service_inclusions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_type_id',
        'service_id',
        'category',
        'session_time',
        'allowance_limit',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allowance_limit' => 'decimal:2',
        ];
    }

    /**
     * Room type parent.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Specific service associated (if any).
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

