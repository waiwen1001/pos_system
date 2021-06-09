<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromoPriceToProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dateTime('promotion_start')->after('price')->nullable();
            $table->dateTime('promotion_end')->after('promotion_start')->nullable();
            $table->double('promotion_price', 2)->after('promotion_end')->nullable();
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
            $table->dropColumn(['promotion_start', 'promotion_end', 'promotion_price']);
        });
    }
}
