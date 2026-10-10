<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /** Paliers du CDC 3.4.7 (le seeder d'origine était dans un dossier non chargé). */
    public function up(): void
    {
        if (DB::table('reduction_tiers')->count() > 0) {
            return;
        }

        $now = now();
        $rows = [];
        foreach ([250, 500, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 5000, 10000] as $i => $amount) {
            $rows[] = ['tier_number' => $i + 1, 'amount' => $amount, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('reduction_tiers')->insert($rows);
    }

    public function down(): void
    {
    }
};
