<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNniRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('nni_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('nni_number', 20)->unique();
            $table->enum('document_type', ['nni', 'passport'])->default('nni');
            $table->string('document_photo_url', 255)->nullable();
            $table->enum('document_photo_storage', ['local', 'firebase'])->default('local');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->bigInteger('verified_by')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nni_records');
    }
}
