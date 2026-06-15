<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagesTableSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Dashboard',
                'route_name' => 'admin.dashboard',
                'module' => 'dashboard'
            ],

            /*
            |--------------------------------------------------------------------------
            | System Users
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'System Users - List',
                'route_name' => 'admin.system-users.index',
                'module' => 'system_users'
            ],

            [
                'name' => 'System Users - Create',
                'route_name' => 'admin.system-users.create',
                'module' => 'system_users'
            ],

            [
                'name' => 'System Users - Store',
                'route_name' => 'admin.system-users.store',
                'module' => 'system_users'
            ],

            [
                'name' => 'System Users - Edit',
                'route_name' => 'admin.system-users.edit',
                'module' => 'system_users'
            ],

            [
                'name' => 'System Users - Update',
                'route_name' => 'admin.system-users.update',
                'module' => 'system_users'
            ],

            [
                'name' => 'System Users - Delete',
                'route_name' => 'admin.system-users.destroy',
                'module' => 'system_users'
            ],

            /*
            |--------------------------------------------------------------------------
            | Students
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Students - List',
                'route_name' => 'admin.students.index',
                'module' => 'students'
            ],

            [
                'name' => 'Students - Create',
                'route_name' => 'admin.students.create',
                'module' => 'students'
            ],

            [
                'name' => 'Students - Store',
                'route_name' => 'admin.students.store',
                'module' => 'students'
            ],

            [
                'name' => 'Students - Edit',
                'route_name' => 'admin.students.edit',
                'module' => 'students'
            ],

            [
                'name' => 'Students - Update',
                'route_name' => 'admin.students.update',
                'module' => 'students'
            ],

            [
                'name' => 'Students - View',
                'route_name' => 'admin.students.show',
                'module' => 'students'
            ],

            [
                'name' => 'Students - Delete',
                'route_name' => 'admin.students.destroy',
                'module' => 'students'
            ],

            /*
            |--------------------------------------------------------------------------
            | Teachers
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Teachers - List',
                'route_name' => 'admin.teachers.index',
                'module' => 'teachers'
            ],

            [
                'name' => 'Teachers - Create',
                'route_name' => 'admin.teachers.create',
                'module' => 'teachers'
            ],

            [
                'name' => 'Teachers - Store',
                'route_name' => 'admin.teachers.store',
                'module' => 'teachers'
            ],

            [
                'name' => 'Teachers - Edit',
                'route_name' => 'admin.teachers.edit',
                'module' => 'teachers'
            ],

            [
                'name' => 'Teachers - Update',
                'route_name' => 'admin.teachers.update',
                'module' => 'teachers'
            ],

            [
                'name' => 'Teachers - View',
                'route_name' => 'admin.teachers.show',
                'module' => 'teachers'
            ],

            [
                'name' => 'Teachers - Delete',
                'route_name' => 'admin.teachers.destroy',
                'module' => 'teachers'
            ],

            /*
            |--------------------------------------------------------------------------
            | Organizers
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Organizers - List',
                'route_name' => 'admin.organizers.index',
                'module' => 'organizers'
            ],

            [
                'name' => 'Organizers - Create',
                'route_name' => 'admin.organizers.create',
                'module' => 'organizers'
            ],

            [
                'name' => 'Organizers - Store',
                'route_name' => 'admin.organizers.store',
                'module' => 'organizers'
            ],

            [
                'name' => 'Organizers - Edit',
                'route_name' => 'admin.organizers.edit',
                'module' => 'organizers'
            ],

            [
                'name' => 'Organizers - Update',
                'route_name' => 'admin.organizers.update',
                'module' => 'organizers'
            ],

            [
                'name' => 'Organizers - Delete',
                'route_name' => 'admin.organizers.destroy',
                'module' => 'organizers'
            ],

            /*
            |--------------------------------------------------------------------------
            | Admissions
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Admissions - List',
                'route_name' => 'admin.admissions.index',
                'module' => 'admissions'
            ],

            [
                'name' => 'Admissions - Create',
                'route_name' => 'admin.admissions.create',
                'module' => 'admissions'
            ],

            [
                'name' => 'Admissions - Store',
                'route_name' => 'admin.admissions.store',
                'module' => 'admissions'
            ],

            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Payments',
                'route_name' => 'admin.payments.index',
                'module' => 'payments'
            ],

            /*
            |--------------------------------------------------------------------------
            | Attendance
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Attendance',
                'route_name' => 'admin.attendance.index',
                'module' => 'attendance'
            ],

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Daily Report',
                'route_name' => 'admin.daily-report.index',
                'module' => 'reports'
            ],

            [
                'name' => 'Teacher Report',
                'route_name' => 'admin.teacher-report.index',
                'module' => 'reports'
            ],
        ];

        foreach ($pages as $page) {

            DB::table('pages')->updateOrInsert(

                [
                    'route_name' => $page['route_name']
                ],

                [
                    'name' => $page['name'],
                    'module' => $page['module'],
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->command->info(
            '✅ Pages seeded successfully!'
        );
    }
}
