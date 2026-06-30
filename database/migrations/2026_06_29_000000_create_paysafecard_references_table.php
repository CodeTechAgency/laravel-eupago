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
        Schema::create('paysafecard_references', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index();
            $table->text('url')->nullable();
            $table->decimal('value', 10, 2)->default(0);
            $table->integer('state')->default(0);
            $table->morphs('paysafecardable');
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
        Schema::dropIfExists('paysafecard_references');
    }
};
