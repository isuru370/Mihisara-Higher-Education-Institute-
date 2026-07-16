<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $userTypes = [
            [
                'name' => 'Super Admin',
                'code' => 'SUPER_ADMIN',
                'description' => 'Full system access',
            ],
            [
                'name' => 'Admin',
                'code' => 'ADMIN',
                'description' => 'Institute Administrator',
            ],
            [
                'name' => 'Teacher',
                'code' => 'TEACHER',
                'description' => 'Teacher Mobile App User',
            ],
        ];

        foreach ($userTypes as $type) {
            DB::table('user_types')->updateOrInsert(
                ['code' => $type['code']],
                [
                    'name'        => $type['name'],
                    'description' => $type['description'],
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        $this->command->info('✅ User types seeded successfully!');
    }
}