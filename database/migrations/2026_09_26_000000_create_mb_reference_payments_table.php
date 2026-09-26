<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * An MB reference that allows repeat payments, or accepts an amount
     * range, can be paid more than once and for amounts other than its
     * value — each payment gets its own row, keyed by the Eupago transaction
     * so a repeated delivery is only recorded once.
     *
     * References already paid get their payment backfilled, so a repeated
     * delivery of it is recognised too. Before this table, only the exact
     * value could be confirmed, so it is the amount that was paid.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mb_reference_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mb_reference_id')->index()->constrained()->cascadeOnDelete();
            $table->string('transaction_id')->unique();
            $table->decimal('value', 10, 2);
            $table->timestamps();
        });

        DB::table('mb_reference_payments')->insertUsing(
            ['mb_reference_id', 'transaction_id', 'value', 'created_at', 'updated_at'],
            DB::table('mb_references')
                ->select(['id', 'transaction_id', 'value', 'updated_at', 'updated_at'])
                ->where('state', 1)
                ->whereNotNull('transaction_id')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mb_reference_payments');
    }
};
