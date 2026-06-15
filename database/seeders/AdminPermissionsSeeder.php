<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $adminTypeId = DB::table('user_types')
            ->where('code', 'ADMIN')
            ->value('id');

        if (!$adminTypeId) {
            $this->command->error('❌ Admin user type not found.');
            return;
        }

        $pages = DB::table('pages')->pluck('id');

        foreach ($pages as $pageId) {
            DB::table('permissions')->updateOrInsert(
                [
                    'user_type_id' => $adminTypeId,
                    'page_id' => $pageId,
                ],
                [
                    'can_view' => true,
                    'can_create' => true,
                    'can_update' => true,
                    'can_delete' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Admin permissions seeded successfully!');
    }
}