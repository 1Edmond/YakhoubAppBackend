<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultishopColumnsToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('has_reduction')->default(false);
            $table->decimal('reduction_amount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2)->nullable();
            $table->decimal('cancellation_fee', 10, 2)->default(1000);
            $table->string('door_photo_url', 255)->nullable();
            $table->decimal('door_latitude', 10, 8)->nullable();
            $table->decimal('door_longitude', 11, 8)->nullable();
            $table->string('delivery_quarter', 100)->nullable();
            $table->string('delivery_street', 200)->nullable();
            $table->text('delivery_description')->nullable();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'has_reduction', 'reduction_amount', 'final_price', 'cancellation_fee',
                'door_photo_url', 'door_latitude', 'door_longitude', 'delivery_quarter',
                'delivery_street', 'delivery_description'
            ]);
        });
    }
}
