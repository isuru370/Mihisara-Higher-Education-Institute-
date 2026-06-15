<?php

return [

    'backup' => [

        'name' => 'MINIPALASA-EDUCATION-CENTER', 

        'source' => [

            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],

            'databases' => [
                'mysql', // Only connection names as strings
            ],
        ],

        'database_dump_compressor' => null,
        'database_dump_file_extension' => '',

        'destination' => [
            'filename_prefix' => '',
            'disks' => [
                'local',
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),

        'password' => null,
        'encryption' => 'default',
    ],

    // 🔹 Notifications completely disabled
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class         => [],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class      => [],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class        => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class    => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class   => [],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => [],
        ],
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
        'via' => [],
    ],

    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'databases' => [
        'mysql',
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

];
