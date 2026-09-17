<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tables gaining the column. Credit Card already has it.
     *
     * @var array<int, string>
     */
    private array $tables = [
        'mb_references',
        'mbway_references',
        'payshop_references',
        'paysafecard_references',
    ];

    /**
     * Run the migrations.
     *
     * `transaction_id` is the Eupago transaction the callback delivers once
     * the payment is made — the id refunds are keyed by. References paid
     * before this migration keep a null value: the transaction only ever
     * exists in the callback payload, so there is nothing to backfill from.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('transaction_id')->nullable()->index()->after('reference');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * SQLite rebuilds the table on a column drop and validates the surviving
     * indexes against it, so the index goes first.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['transaction_id']);
                $blueprint->dropColumn('transaction_id');
            });
        }
    }
};
