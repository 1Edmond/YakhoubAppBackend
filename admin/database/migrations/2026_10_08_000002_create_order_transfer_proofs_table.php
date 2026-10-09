<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTransferProofsTable extends Migration
{
    public function up()
    {
        Schema::create('order_transfer_proofs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('transfer_account_id');
            $table->string('transaction_reference');
            $table->string('proof_image')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('transfer_account_id')->references('id')->on('transfer_accounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_transfer_proofs');
    }
}
