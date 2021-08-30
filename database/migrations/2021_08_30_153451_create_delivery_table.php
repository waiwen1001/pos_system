<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id')->nullable();
            $table->string('opening_id')->nullable();
            $table->string('ip')->nullable();
            $table->string('cashier_name')->nullable();
            $table->string('transaction_no')->nullable();
            $table->string('reference_no')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->decimal('subtotal', 12, 2)->nullable();
            $table->decimal('total_discount', 12, 2)->nullable();
            $table->integer('voucher_id')->nullable();
            $table->string('voucher_code')->nullable();
            $table->decimal('payment', 12, 2)->nullable();
            $table->string('delivery_type')->nullable();
            $table->string('delivery_type_text')->nullable();
            $table->decimal('balance', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->decimal('round_off', 12, 2)->nullable();
            $table->integer('void')->nullable();
            $table->integer('void_by')->nullable();
            $table->dateTime('void_date')->nullable();
            $table->integer('completed')->nullable();
            $table->integer('completed_by')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->integer('synced')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_detail', function (Blueprint $table) {
          $table->id();
          $table->integer('delivery_id')->nullable();
          $table->integer('department_id')->nullable();
          $table->integer('category_id')->nullable();
          $table->integer('product_id')->nullable();
          $table->string('barcode')->nullable();
          $table->string('product_name')->nullable();
          $table->integer('quantity')->nullable();
          $table->string('measurement_type')->nullable();
          $table->decimal('measurement', 8, 4)->nullable();
          $table->decimal('price', 12, 4)->nullable();
          $table->decimal('wholesale_price', 12, 4)->nullable();
          $table->decimal('discount', 12, 4)->nullable();
          $table->decimal('subtotal', 12, 4)->nullable();
          $table->decimal('total', 12, 4)->nullable();
          $table->integer('void')->nullable();
          $table->timestamps();
          $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery');
        Schema::dropIfExists('delivery_detail');
    }
}
