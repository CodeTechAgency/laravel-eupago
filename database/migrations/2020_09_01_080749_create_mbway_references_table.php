<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMbwayReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mbway_references', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reference')->unique();
            $table->float('value', 10, 2)->default(0);
            $table->string('alias');
            $table->integer('state')->default(0);
            $table->morphs('mbwayable');
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
        Schema::dropIfExists('mbway_references');
    }
}
