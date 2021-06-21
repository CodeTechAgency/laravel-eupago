<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMbReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mb_references', function (Blueprint $table) {
            $table->id();
            $table->string('entity');
            $table->string('reference')->unique();
            $table->decimal('value', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('min_value', 10, 2);
            $table->decimal('max_value', 10, 2);
            $table->integer('state');
            $table->morphs('mbable');
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
        Schema::dropIfExists('mb_references');
    }
}
