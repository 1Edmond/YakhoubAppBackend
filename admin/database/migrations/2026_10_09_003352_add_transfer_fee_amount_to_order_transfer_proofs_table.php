<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_transfer_proofs', function (Blueprint $table) {
            $table->decimal('transfer_fee_amount', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_transfer_proofs', function (Blueprint $table) {
            $table->dropColumn('transfer_fee_amount');
        });
    }
};
