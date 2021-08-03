<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNormalWholesaleToProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->decimal('normal_wholesale_price', 12, 4)->after('promotion_price')->nullable();
            $table->decimal('normal_wholesale_price2', 12, 4)->after('normal_wholesale_price')->nullable();
            $table->decimal('normal_wholesale_quantity', 8, 2)->after('normal_wholesale_price2')->nullable();
            $table->decimal('normal_wholesale_quantity2', 8 , 2)->after('normal_wholesale_quantity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn(['normal_wholesale_price', 'normal_wholesale_price2', 'normal_wholesale_quantity', 'normal_wholesale_quantity2']);
        });
    }
}
