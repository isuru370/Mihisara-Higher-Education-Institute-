<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'title',
        'body',
        'type',
        'data',
        'status',
        'sent_at',
        'read_at',
        'scheduled_at',
        'error_message',
        'retry_count',
        'created_by',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    protected $attributes = [
        'retry_count' => 0,
    ];

    /**
     * Student who receives the notification.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Admin/User who created the notification.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Sent notifications.
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::SENT);
    }

    /**
     * Scope: Pending notifications.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::PENDING);
    }

    /**
     * Scope: Processing notifications.
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::PROCESSING);
    }

    /**
     * Scope: Failed notifications.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::FAILED);
    }

    /**
     * Scope: Cancelled notifications.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::CANCELLED);
    }

    /**
     * Scope: Unread notifications.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: Read notifications.
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope: Scheduled notifications.
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): self
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
        return $this;
    }

    /**
     * Mark notification as sent.
     */
    public function markAsSent(): self
    {
        $this->update([
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
            'error_message' => null,
        ]);
        return $this;
    }

    /**
     * Mark notification as failed.
     */
    public function markAsFailed(string $message): self
    {
        $this->update([
            'status' => NotificationStatus::FAILED,
            'error_message' => $message,
        ]);
        return $this;
    }

    /**
     * Mark notification as processing.
     */
    public function markAsProcessing(): self
    {
        $this->update([
            'status' => NotificationStatus::PROCESSING,
        ]);
        return $this;
    }

    /**
     * Mark notification as pending.
     */
    public function markAsPending(): self
    {
        $this->update([
            'status' => NotificationStatus::PENDING,
            'error_message' => null,
        ]);
        return $this;
    }

    /**
     * Mark notification as cancelled.
     */
    public function markAsCancelled(): self
    {
        $this->update([
            'status' => NotificationStatus::CANCELLED,
        ]);
        return $this;
    }

    /**
     * Check if notification was sent.
     */
    public function wasSent(): bool
    {
        return $this->status === NotificationStatus::SENT;
    }

    /**
     * Check if notification failed.
     */
    public function wasFailed(): bool
    {
        return $this->status === NotificationStatus::FAILED;
    }

    /**
     * Check if notification is pending.
     */
    public function isPending(): bool
    {
        return $this->status === NotificationStatus::PENDING;
    }

    /**
     * Check if notification is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === NotificationStatus::PROCESSING;
    }

    /**
     * Check if notification is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === NotificationStatus::CANCELLED;
    }

    /**
     * Check if notification is scheduled for later.
     */
    public function isScheduled(): bool
    {
        return !is_null($this->scheduled_at) && $this->scheduled_at->isFuture();
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return NotificationStatus::label($this->status);
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return NotificationType::label($this->type);
    }

    /**
     * Get type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return NotificationType::icon($this->type);
    }

    /**
     * Check if retry is allowed.
     */
    public function canRetry(): bool
    {
        return $this->wasFailed() && $this->retry_count < 3;
    }
}