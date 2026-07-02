<?php

namespace App\Enums;

class NotificationType
{
    public const GENERAL = 'general';
    public const REMINDER = 'reminder';
    public const EXAM = 'exam';
    public const ANNOUNCEMENT = 'announcement';
    public const ATTENDANCE = 'attendance';
    public const GRADE = 'grade';
    public const PAYMENT = 'payment';

    /**
     * Get all type values
     */
    public static function all(): array
    {
        return [
            self::GENERAL,
            self::REMINDER,
            self::EXAM,
            self::ANNOUNCEMENT,
            self::ATTENDANCE,
            self::GRADE,
            self::PAYMENT,
        ];
    }

    /**
     * Get type label
     */
    public static function label(string $type): string
    {
        return match ($type) {
            self::GENERAL => 'General',
            self::REMINDER => 'Reminder',
            self::EXAM => 'Exam',
            self::ANNOUNCEMENT => 'Announcement',
            self::ATTENDANCE => 'Attendance',
            self::GRADE => 'Grade',
            self::PAYMENT => 'Payment',
            default => 'Unknown',
        };
    }

    /**
     * Get type icon
     */
    public static function icon(string $type): string
    {
        return match ($type) {
            self::GENERAL => 'fa-bell',
            self::REMINDER => 'fa-clock',
            self::EXAM => 'fa-pencil',
            self::ANNOUNCEMENT => 'fa-bullhorn',
            self::ATTENDANCE => 'fa-calendar-check',
            self::GRADE => 'fa-star',
            self::PAYMENT => 'fa-credit-card',
            default => 'fa-bell',
        };
    }

    /**
     * Get type color
     */
    public static function color(string $type): string
    {
        return match ($type) {
            self::GENERAL => 'primary',
            self::REMINDER => 'info',
            self::EXAM => 'warning',
            self::ANNOUNCEMENT => 'success',
            self::ATTENDANCE => 'secondary',
            self::GRADE => 'danger',
            self::PAYMENT => 'success',
            default => 'primary',
        };
    }

    /**
     * Get types for dropdown
     */
    public static function options(): array
    {
        return [
            self::GENERAL => self::label(self::GENERAL),
            self::REMINDER => self::label(self::REMINDER),
            self::EXAM => self::label(self::EXAM),
            self::ANNOUNCEMENT => self::label(self::ANNOUNCEMENT),
            self::ATTENDANCE => self::label(self::ATTENDANCE),
            self::GRADE => self::label(self::GRADE),
            self::PAYMENT => self::label(self::PAYMENT),
        ];
    }

    /**
     * Get types with icons for dropdown
     */
    public static function optionsWithIcon(): array
    {
        $options = [];
        foreach (self::all() as $type) {
            $options[$type] = [
                'label' => self::label($type),
                'icon' => self::icon($type),
                'color' => self::color($type),
            ];
        }
        return $options;
    }
}