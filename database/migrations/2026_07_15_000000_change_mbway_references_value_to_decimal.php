<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The value was stored as FLOAT, and the callback matches the paid
     * reference with an equality lookup — float rounding could make a
     * legitimate callback miss the row, leaving a real payment pending.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mbway_references', function (Blueprint $table) {
            $table->decimal('value', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mbway_references', function (Blueprint $table) {
            $table->float('value')->default(0)->change();
        });
    }
};
