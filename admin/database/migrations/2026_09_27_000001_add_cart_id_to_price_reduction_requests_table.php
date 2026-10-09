<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCartIdToPriceReductionRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('price_reduction_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('price_reduction_requests', 'cart_id')) {
                $table->unsignedBigInteger('cart_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('price_reduction_requests', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('cart_id');
            }
        });
    }

    public function down()
    {
        Schema::table('price_reduction_requests', function (Blueprint $table) {
            if (Schema::hasColumn('price_reduction_requests', 'cart_id')) {
                $table->dropColumn('cart_id');
            }
            if (Schema::hasColumn('price_reduction_requests', 'product_id')) {
                $table->dropColumn('product_id');
            }
        });
    }
}
