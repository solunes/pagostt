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
        if(config('pagostt.enable_cycle')){
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
        if(config('pagostt.enable_preinvoice')){
            Schema::create('preinvoices', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('payment_id')->nullable();
                $table->string('invoice_batch')->nullable();
                $table->string('nit_name')->nullable();
                $table->string('nit_number')->nullable();
                $table->string('return_code')->nullable();
                $table->string('pagostt_iterator')->nullable();
                $table->string('pagostt_code')->nullable();
                $table->string('pagostt_error')->nullable()->default(0);
                $table->string('pagostt_message')->nullable();
                $table->timestamps();
            });
            Schema::create('preinvoice_items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable();
                $table->string('name')->nullable();
                $table->string('detail')->nullable();
                $table->string('product_code')->nullable();
                $table->integer('quantity')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('amount', 10, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Módulo General de PagosTT
        if(config('pagostt.enable_preinvoice')){
            Schema::dropIfExists('preinvoice_items');
            Schema::dropIfExists('preinvoices');
        }
        Schema::dropIfExists('ptt_transaction_payments');
        Schema::dropIfExists('ptt_transactions');

    }
}
