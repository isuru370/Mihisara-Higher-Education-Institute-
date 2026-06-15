<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemUsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'custom_id' => 'ADM001',
                'full_name' => 'System Administrator',
                'email' => 'admin@nexorait.lk',
                'password' => 'Admin@123',
                'mobile' => '0711234567',
                'nic' => '123456789V',
                'bday' => '1985-01-15',
                'gender' => 'male',
                'address1' => 'Mirigama, Sri Lanka',
                'address2' => 'Nexora IT Solutions',
                'address3' => 'Mirigama',
            ],
            [
                'custom_id' => 'ADM002',
                'full_name' => 'Minipalasa Administrator',
                'email' => 'minipalasa@gmail.com',
                'password' => 'Admin@minipalasa',
                'mobile' => '0719876543',
                'nic' => '987654321V',
                'bday' => '1990-05-20',
                'gender' => 'male',
                'address1' => 'Mirigama',
                'address2' => 'Sri Lanka',
                'address3' => 'Sri Lanka',
            ],
        ];

        // 🔥 get admin user type
        $adminTypeId = DB::table('user_types')
            ->where('code', 'ADMIN')
            ->value('id');

        foreach ($admins as $admin) {

            // 🔥 users table
            DB::table('users')->updateOrInsert(
                ['email' => $admin['email']],
                [
                    'name' => $admin['full_name'],
                    'password' => Hash::make($admin['password']),
                    'user_type_id' => $adminTypeId,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $user = DB::table('users')->where('email', $admin['email'])->first();

            // 🔥 system_users table
            DB::table('system_users')->updateOrInsert(
                ['custom_id' => $admin['custom_id']],
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

            $this->command->info("✅ Admin {$admin['email']} ready");
        }
    }
}