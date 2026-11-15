<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPriceReductionRequestsTableForCartGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_reduction_requests', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('price_reduction_requests', 'cart_id')) {
                $table->dropColumn('cart_id');
            }
            if (Schema::hasColumn('price_reduction_requests', 'product_id')) {
                $table->dropColumn('product_id');
            }
            
            // Add new columns
            if (!Schema::hasColumn('price_reduction_requests', 'cart_group_id')) {
                $table->string('cart_group_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('price_reduction_requests', 'original_price')) {
                $table->decimal('original_price', 24, 2)->default(0)->after('cart_group_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_reduction_requests', function (Blueprint $table) {
            $table->dropColumn(['cart_group_id', 'original_price']);
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
        });
    }
}
