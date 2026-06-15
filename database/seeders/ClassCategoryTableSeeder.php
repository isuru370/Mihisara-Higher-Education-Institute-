<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassCategoryTableSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Theory',
                'code' => 'theory',
            ],
            [
                'category_name' => 'Revision',
                'code' => 'revision',
            ],
            [
                'category_name' => 'Paper Class',
                'code' => 'paper',
            ],
        ];

        foreach ($categories as $category) {

            DB::table('class_categories')->updateOrInsert(
                ['code' => $category['code']], // unique check
                [
                    'category_name' => $category['category_name'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Class categories seeded successfully!');
    }
}