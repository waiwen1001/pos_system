<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDecimalPlaceToProductListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->decimal('normal_wholesale_price','16','6')->change();
            $table->decimal('normal_wholesale_price2','16','6')->change();
            $table->decimal('normal_wholesale_price3','16','6')->change();
            $table->decimal('normal_wholesale_price4','16','6')->change();
            $table->decimal('normal_wholesale_price5','16','6')->change();
            $table->decimal('normal_wholesale_price6','16','6')->change();
            $table->decimal('normal_wholesale_price7','16','6')->change();
            $table->decimal('wholesale_price','16','6')->change();
            $table->decimal('wholesale_price2','16','6')->change();
            $table->decimal('wholesale_price3','16','6')->change();
            $table->decimal('wholesale_price4','16','6')->change();
            $table->decimal('wholesale_price5','16','6')->change();
            $table->decimal('wholesale_price6','16','6')->change();
            $table->decimal('wholesale_price7','16','6')->change();
            $table->decimal('promotion_price','16','6')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
