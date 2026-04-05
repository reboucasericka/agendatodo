<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskModel extends Model
{
    use HasFactory;

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_LOW = 'low';

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'completed_at',
        'user_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'priority' => self::PRIORITY_MEDIUM,
        'status' => self::STATUS_PENDING,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if ($status === null || $status === '') {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopePriority(Builder $query, ?string $priority): Builder
    {
        if ($priority === null || $priority === '') {
            return $query;
        }

        return $query->where('priority', $priority);
    }

    public function scopeSort(Builder $query, ?string $field, ?string $direction): Builder
    {
        $allowedFields = ['created_at', 'due_date'];
        $sortField = in_array($field, $allowedFields, true) ? $field : 'created_at';
        $sortDirection = strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortField, $sortDirection)
            ->orderBy('id', 'desc');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = trim($search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($search): void {
            $subQuery->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeDueDate(Builder $query, ?string $date): Builder
    {
        if ($date === null || $date === '') {
            return $query;
        }

        return $query->whereDate('due_date', $date);
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from !== null && $from !== '') {
            $query->whereDate('due_date', '>=', $from);
        }

        if ($to !== null && $to !== '') {
            $query->whereDate('due_date', '<=', $to);
        }

        return $query;
    }

    public function scopeOverdue(Builder $query, ?bool $overdue): Builder
    {
        if (! $overdue) {
            return $query;
        }

        return $query
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->where('status', self::STATUS_PENDING);
    }

    public function applyStatus(?string $status): void
    {
        if ($status === null) {
            return;
        }

        $this->status = $status;
        $this->completed_at = $status === self::STATUS_COMPLETED ? now() : null;
    }

    public function toggleStatus(): void
    {
        $this->applyStatus(
            $this->status === self::STATUS_COMPLETED
                ? self::STATUS_PENDING
                : self::STATUS_COMPLETED
        );
    }
}
