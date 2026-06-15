<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Banks Data
        $banks = [
            ['bank_name' => 'Alliance Finance Company PLC', 'bank_code' => '7852', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Amana Bank PLC', 'bank_code' => '7463', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Axis Bank', 'bank_code' => '7472', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Bank of Ceylon', 'bank_code' => '7010', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Cargills Bank Limited', 'bank_code' => '7481', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Central Bank of Sri Lanka', 'bank_code' => '8004', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Central Finance PLC', 'bank_code' => '7825', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Citi Bank', 'bank_code' => '7047', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Citizen Development Business Finance PLC', 'bank_code' => '7746', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Commercial Bank PLC', 'bank_code' => '7056', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Commercial Credit & Finance PLC', 'bank_code' => '7870', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Commercial Leasing and Finance', 'bank_code' => '7807', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Deutsche Bank', 'bank_code' => '7205', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'DFCC Bank PLC', 'bank_code' => '7454', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Habib Bank Ltd', 'bank_code' => '7074', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Hatton National Bank PLC', 'bank_code' => '7083', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'HDFC Bank', 'bank_code' => '7737', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Hongkong Shanghai Bank', 'bank_code' => '7092', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'ICICI Bank Ltd', 'bank_code' => '7384', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Indian Bank', 'bank_code' => '7108', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Indian Overseas Bank', 'bank_code' => '7117', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Kanrich Finance Limited', 'bank_code' => '7834', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Lanka Orix Finance PLC', 'bank_code' => '7861', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'LB Finance PLC', 'bank_code' => '7773', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'MCB Bank Ltd', 'bank_code' => '7269', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Mercantile Investment and Finance PLC', 'bank_code' => '7913', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Merchant Bank of Sri Lanka & Finance PLC', 'bank_code' => '7898', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'National Development Bank PLC', 'bank_code' => '7214', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'National Savings Bank', 'bank_code' => '7719', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Nations Trust Bank PLC', 'bank_code' => '7162', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Pan Asia Banking Corporation PLC', 'bank_code' => '7311', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Peoples Bank', 'bank_code' => '7135', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Peopleâ€™s Leasing & Finance PLC', 'bank_code' => '7922', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Public Bank', 'bank_code' => '7296', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Regional Development Bank', 'bank_code' => '7755', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Sampath Bank PLC', 'bank_code' => '7278', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Sanasa Development Bank', 'bank_code' => '7728', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Senkadagala Finance PLC', 'bank_code' => '7782', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Seylan Bank PLC', 'bank_code' => '7287', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Standard Chartered Bank', 'bank_code' => '7038', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'State Bank of India', 'bank_code' => '7144', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'State Mortgage & Investment Bank', 'bank_code' => '7764', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Union Bank of Colombo PLC', 'bank_code' => '7302', 'created_at' => now(), 'updated_at' => now()],
            ['bank_name' => 'Vallibel Finance PLC', 'bank_code' => '7816', 'created_at' => now(), 'updated_at' => now()],
        ];

        // Check existing registration before entering data.
        $existingBanks = DB::table('banks')->count();

        if ($existingBanks == 0) {
            DB::table('banks')->insert($banks);
            $this->command->info('✅ Banks were successfully created.!');
            $this->command->info('🏦 Total Banks: ' . count($banks));
        } else {
            $this->command->info('ℹ️ Banks already exist. (' . $existingBanks . ' banks)');
        }
    }
}
