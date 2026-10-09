<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReductionTierSeeder extends Seeder
{
    public function run()
    {
        $tiers = [250, 500, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 5000, 10000];
        foreach ($tiers as $index => $amount) {
            DB::table('reduction_tiers')->insert([
                'tier_number' => $index + 1,
                'amount' => $amount,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
