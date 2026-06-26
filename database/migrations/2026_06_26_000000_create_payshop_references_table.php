<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payshop_references', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reference')->unique();
            $table->decimal('value', 10, 2)->default(0);
            $table->integer('state')->default(0);
            $table->morphs('payshopable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payshop_references');
    }
};
