<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HousekeepingTask extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'housekeeping_tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_id',
        'user_id',
        'created_by',
        'task_type',
        'priority',
        'status',
        'notes',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Room related to this task.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Housekeeper assigned to this task.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User who created this task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Human-readable task type label.
     */
    public function getTaskTypeLabelAttribute(): string
    {
        return match ($this->task_type) {
            'checkout_clean' => 'Pembersihan Check-Out',
            'stayover_clean' => 'Pembersihan Harian (Stayover)',
            'deep_clean' => 'Deep Cleaning (Menyeluruh)',
            'inspection' => 'Inspeksi Kesiapan Kamar',
            'maintenance_repair' => 'Perbaikan Teknis / Maintenance',
            default => ucfirst(str_replace('_', ' ', $this->task_type)),
        };
    }

    /**
     * Priority badge configuration for TailAdmin UI.
     *
     * @return array{color: string, label: string}
     */
    public function getPriorityBadgeAttribute(): array
    {
        return match ($this->priority) {
            'urgent' => ['color' => 'error', 'label' => 'Mendesak'],
            'high' => ['color' => 'warning', 'label' => 'Tinggi'],
            'low' => ['color' => 'gray', 'label' => 'Rendah'],
            default => ['color' => 'primary', 'label' => 'Normal'],
        };
    }

    /**
     * Status badge configuration for TailAdmin UI.
     *
     * @return array{color: string, label: string}
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'in_progress' => ['color' => 'primary', 'label' => 'Sedang Dikerjakan'],
            'completed' => ['color' => 'success', 'label' => 'Selesai'],
            'cancelled' => ['color' => 'gray', 'label' => 'Dibatalkan'],
            default => ['color' => 'warning', 'label' => 'Menunggu (Antrean)'],
        };
    }

    /**
     * Scope only active tasks (pending & in-progress).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Scope only completed tasks.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }
}

