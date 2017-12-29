<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NodesPagostt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Módulo General de PagosTT
        Schema::create('ptt_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('customer_id')->nullable();
            $table->string('payment_code')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->enum('status', ['holding','paid','cancelled'])->default('holding');
            $table->timestamps();
        });
        Schema::create('ptt_transaction_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable();
            $table->string('payment_id')->nullable();
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
        // Módulo General de PagosTT
        Schema::dropIfExists('ptt_transaction_payments');
        Schema::dropIfExists('ptt_transactions');

    }
}
