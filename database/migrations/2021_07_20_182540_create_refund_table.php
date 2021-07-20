<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id')->nullable();
            $table->integer('opening_id')->nullable();
            $table->string('ip')->nullable();
            $table->string('cashier_name')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->double('subtotal', 15, 2)->nullable();
            $table->double('round_off', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->integer('synced')->nullable();
            $table->timestamps();
        });

        Schema::create('refund_detail', function (Blueprint $table) {
            $table->id();
            $table->integer('refund_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('barcode')->nullable();
            $table->string('product_name')->nullable();
            $table->integer('quantity')->nullable();
            $table->double('price', 15, 2)->nullable();
            $table->double('subtotal', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
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
        Schema::dropIfExists('refund');
        Schema::dropIfExists('refund_detail');
    }
}
