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
        Schema::create('credit_card_references', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index();
            // The id of the hosted payment form, returned when the payment is
            // created. `transaction_id` is the Eupago transaction the callback
            // delivers once the payment is made — the two are different ids.
            $table->string('form_transaction_id')->nullable()->index();
            $table->string('transaction_id')->nullable()->index();
            $table->string('reference')->index();
            $table->text('url')->nullable();
            $table->decimal('value', 10, 2)->default(0);
            $table->integer('state')->default(0);
            $table->morphs('creditcardable');
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
        Schema::dropIfExists('credit_card_references');
    }
};
