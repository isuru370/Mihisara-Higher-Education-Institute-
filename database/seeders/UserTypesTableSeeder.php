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
                'name' => 'Admin',
                'code' => 'ADMIN',
                'description' => 'System Administrator',
            ],
            [
                'name' => 'User',
                'code' => 'USER',
                'description' => 'Normal User',
            ],
        ];

        foreach ($userTypes as $type) {
            DB::table('user_types')->updateOrInsert(
                ['code' => $type['code']], // unique check
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ User types seeded successfully!');
    }
}