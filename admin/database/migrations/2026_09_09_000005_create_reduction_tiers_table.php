<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReductionTiersTable extends Migration
{
    public function up()
    {
        Schema::create('reduction_tiers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tier_number');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reduction_tiers');
    }
}
