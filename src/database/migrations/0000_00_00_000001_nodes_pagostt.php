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
            $table->string('invoice_id')->nullable();
            $table->string('nit_company')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('auth_number')->nullable();
            $table->string('control_code')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_nit')->nullable();
            $table->enum('invoice_type', ['E','C'])->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->enum('status', ['holding','paid','cancelled'])->default('holding');
            $table->timestamps();
        });
        if(config('pagostt.cycle')){
            Schema::table('ptt_transactions', function (Blueprint $table) {
                $table->string('billing_cycle_dosage')->nullable();
                $table->string('billing_cycle_start_date')->nullable();
                $table->string('billing_cycle_end_date')->nullable();
                $table->string('billing_cycle_eticket')->nullable();
                $table->string('billing_cycle_legend')->nullable();
                $table->string('billing_cycle_parallel')->nullable();
                $table->string('billing_cycle_invoice_title')->nullable();
                $table->string('company_code')->nullable();
            });
        }
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
