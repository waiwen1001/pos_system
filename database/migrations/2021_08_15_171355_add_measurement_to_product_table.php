<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeasurementToProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->string('measurement')->after('uom')->nullable();
            $table->decimal('normal_wholesale_price3', 12, 4)->after('normal_wholesale_price2')->nullable();
            $table->decimal('normal_wholesale_price4', 12, 4)->after('normal_wholesale_price3')->nullable();
            $table->decimal('normal_wholesale_price5', 12, 4)->after('normal_wholesale_price4')->nullable();
            $table->decimal('normal_wholesale_price6', 12, 4)->after('normal_wholesale_price5')->nullable();
            $table->decimal('normal_wholesale_price7', 12, 4)->after('normal_wholesale_price6')->nullable();
            $table->decimal('normal_wholesale_quantity3', 12, 4)->after('normal_wholesale_quantity2')->nullable();
            $table->decimal('normal_wholesale_quantity4', 12, 4)->after('normal_wholesale_quantity3')->nullable();
            $table->decimal('normal_wholesale_quantity5', 12, 4)->after('normal_wholesale_quantity4')->nullable();
            $table->decimal('normal_wholesale_quantity6', 12, 4)->after('normal_wholesale_quantity5')->nullable();
            $table->decimal('normal_wholesale_quantity7', 12, 4)->after('normal_wholesale_quantity6')->nullable();

            $table->decimal('wholesale_price3', 12, 4)->after('wholesale_price2')->nullable();
            $table->decimal('wholesale_price4', 12, 4)->after('wholesale_price3')->nullable();
            $table->decimal('wholesale_price5', 12, 4)->after('wholesale_price4')->nullable();
            $table->decimal('wholesale_price6', 12, 4)->after('wholesale_price5')->nullable();
            $table->decimal('wholesale_price7', 12, 4)->after('wholesale_price6')->nullable();
            $table->decimal('wholesale_quantity3', 12, 4)->after('wholesale_quantity2')->nullable();
            $table->decimal('wholesale_quantity4', 12, 4)->after('wholesale_quantity3')->nullable();
            $table->decimal('wholesale_quantity5', 12, 4)->after('wholesale_quantity4')->nullable();
            $table->decimal('wholesale_quantity6', 12, 4)->after('wholesale_quantity5')->nullable();
            $table->decimal('wholesale_quantity7', 12, 4)->after('wholesale_quantity6')->nullable();
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
            $table->dropColumn(['measurement', 'normal_wholesale_price3', 'normal_wholesale_price4', 'normal_wholesale_price5', 'normal_wholesale_price6', 'normal_wholesale_price7', 'normal_wholesale_quantity3', 'normal_wholesale_quantity4', 'normal_wholesale_quantity5', 'normal_wholesale_quantity6', 'normal_wholesale_quantity7', 'wholesale_price3', 'wholesale_price4', 'wholesale_price5', 'wholesale_price6', 'wholesale_price7', 'wholesale_quantity3', 'wholesale_quantity4', 'wholesale_quantity5', 'wholesale_quantity6', 'wholesale_quantity7']);
        });
    }
}
