<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradesTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 11) as $grade) {

            DB::table('grades')->updateOrInsert(
                ['grade_name' => (string) $grade], // unique check
                [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Grades seeded successfully!');
    }
}