<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemUsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $admin = [
            'custom_id' => 'ADM001',
            'full_name' => 'System Administrator',

            'email' => env('SUPER_ADMIN_EMAIL'),
            'password' => env('SUPER_ADMIN_PASSWORD'),

            'role' => 'SUPER_ADMIN',

            'mobile' => '0711234567',
            'nic' => '123456789V',
            'bday' => '1985-01-15',
            'gender' => 'male',
            'address1' => 'Mirigama, Sri Lanka',
            'address2' => 'Nexora IT Solutions',
            'address3' => 'Mirigama',
        ];

        // -------------------------------------------------
        // Validate Email & Password
        // -------------------------------------------------

        if (
            !is_string($admin['email']) ||
            trim($admin['email']) === ''
        ) {
            throw new \RuntimeException(
                'SUPER_ADMIN_EMAIL is not configured in the .env file.'
            );
        }

        if (
            !is_string($admin['password']) ||
            trim($admin['password']) === ''
        ) {
            throw new \RuntimeException(
                'SUPER_ADMIN_PASSWORD is not configured in the .env file.'
            );
        }

        // -------------------------------------------------
        // Get Role ID
        // -------------------------------------------------

        $userTypeId = DB::table('user_types')
            ->where('code', $admin['role'])
            ->value('id');

        if (!$userTypeId) {
            throw new \RuntimeException(
                "User type '{$admin['role']}' not found."
            );
        }

        // -------------------------------------------------
        // Users Table
        // -------------------------------------------------

        DB::table('users')->updateOrInsert(
            [
                'email' => $admin['email'],
            ],
            [
                'name' => $admin['full_name'],
                'password' => Hash::make($admin['password']),
                'user_type_id' => $userTypeId,
                'is_active' => true,
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // -------------------------------------------------
        // Get User
        // -------------------------------------------------

        $user = DB::table('users')
            ->where('email', $admin['email'])
            ->first();

        if (!$user) {
            throw new \RuntimeException(
                "Failed to create or retrieve user: {$admin['email']}"
            );
        }

        // -------------------------------------------------
        // System Users Table
        // -------------------------------------------------

        DB::table('system_users')->updateOrInsert(
            [
                'custom_id' => $admin['custom_id'],
            ],
            [
                'user_id' => $user->id,
                'full_name' => $admin['full_name'],
                'mobile' => $admin['mobile'],
                'nic' => $admin['nic'],
                'bday' => $admin['bday'],
                'gender' => $admin['gender'],
                'address1' => $admin['address1'],
                'address2' => $admin['address2'],
                'address3' => $admin['address3'],
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // -------------------------------------------------
        // Success Message
        // -------------------------------------------------

        $this->command->info(
            "✅ {$admin['role']} {$admin['email']} ready"
        );
    }
}