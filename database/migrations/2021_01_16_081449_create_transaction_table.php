<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transaction_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->integer('user_id')->nullable();
            $table->double('subtotal', 15, 2)->nullable();
            $table->double('total_discount', 15, 2)->nullable();
            $table->double('payment', 15, 2)->nullable();
            $table->string('payment_type')->nullable();
            $table->double('balance', 15, 2)->nullable();
            $table->integer('void')->nullable();
            $table->integer('completed')->nullable();
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
        Schema::dropIfExists('transaction');
    }
}
