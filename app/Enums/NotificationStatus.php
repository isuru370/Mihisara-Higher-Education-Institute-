<?php

namespace App\Enums;

class NotificationStatus
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const SENT = 'sent';
    public const FAILED = 'failed';
    public const CANCELLED = 'cancelled';

    /**
     * Get all status values
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::PROCESSING,
            self::SENT,
            self::FAILED,
            self::CANCELLED,
        ];
    }

    /**
     * Get status label
     */
    public static function label(string $status): string
    {
        return match ($status) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::SENT => 'Sent',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get status color
     */
    public static function color(string $status): string
    {
        return match ($status) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::SENT => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get status icon
     */
    public static function icon(string $status): string
    {
        return match ($status) {
            self::PENDING => 'fa-clock',
            self::PROCESSING => 'fa-spinner',
            self::SENT => 'fa-check-circle',
            self::FAILED => 'fa-times-circle',
            self::CANCELLED => 'fa-ban',
            default => 'fa-circle',
        };
    }

    /**
     * Check if status is final
     */
    public static function isFinal(string $status): bool
    {
        return in_array($status, [
            self::SENT,
            self::FAILED,
            self::CANCELLED,
        ]);
    }

    /**
     * Get statuses for dropdown
     */
    public static function options(): array
    {
        return [
            self::PENDING => self::label(self::PENDING),
            self::PROCESSING => self::label(self::PROCESSING),
            self::SENT => self::label(self::SENT),
            self::FAILED => self::label(self::FAILED),
            self::CANCELLED => self::label(self::CANCELLED),
        ];
    }
}