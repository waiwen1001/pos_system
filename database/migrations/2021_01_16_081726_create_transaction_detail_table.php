<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->integer('quantity')->nullable();
            $table->double('price', 15, 2)->nullable();
            $table->double('discount', 15, 2)->nullable();
            $table->double('subtotal', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->integer('void')->nullable();
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
        Schema::dropIfExists('transaction_detail');
    }
}
