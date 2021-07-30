<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWholesalePrice2ToProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->decimal('wholesale_price', 12, 4)->change();
            $table->decimal('wholesale_quantity', 8, 2)->change();
            $table->decimal('wholesale_price2', 12, 4)->after('wholesale_price')->nullable();
            $table->decimal('wholesale_quantity2', 8, 2)->after('wholesale_quantity')->nullable();
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
            $table->dropColumn(['wholesale_price2', 'wholesale_quantity2']);
        });
    }
}
