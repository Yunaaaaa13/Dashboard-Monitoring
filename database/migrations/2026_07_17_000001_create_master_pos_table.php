<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('master_pos', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->nullable();
            $table->string('supplier')->nullable();
            $table->string('po')->nullable();
            $table->string('item_code')->nullable();
            $table->string('name')->nullable();
            $table->integer('qty')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('master_pos');
    }
};
