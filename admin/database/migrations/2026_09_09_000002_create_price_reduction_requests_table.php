<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceReductionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('price_reduction_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id')->unsigned();
            $table->bigInteger('customer_id')->unsigned();
            $table->bigInteger('vendor_id')->unsigned();
            $table->decimal('original_price', 10, 2);
            $table->decimal('requested_reduction', 10, 2);
            $table->decimal('proposed_price', 10, 2)->nullable();
            $table->enum('status', ['pending', 'accepted', 'refused', 'counter_offer']);
            $table->decimal('counter_offer_amount', 10, 2)->nullable();
            $table->integer('round_number')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_reduction_requests');
    }
}
