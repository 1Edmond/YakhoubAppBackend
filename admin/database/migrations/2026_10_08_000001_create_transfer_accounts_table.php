<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('transfer_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_number');
            $table->decimal('daily_limit', 15, 2);
            $table->decimal('current_daily_amount', 15, 2)->default(0);
            $table->string('owner_type'); // admin, seller
            $table->bigInteger('owner_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transfer_accounts');
    }
}
