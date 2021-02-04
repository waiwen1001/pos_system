<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashier', function (Blueprint $table) {
            $table->increments('id');
            $table->string('branch')->nullable();
            $table->string('IP')->nullable();
            $table->integer('opening')->nullable();
            $table->integer('opening_by')->nullable();
            $table->double('opening_amount', 15, 2)->nullable();
            $table->dateTime('opening_date_time')->nullable();
            $table->integer('closing')->nullable();
            $table->integer('closing_by')->nullable();
            $table->double('closing_amount', 15, 2)->nullable();
            $table->dateTime('closing_date_time')->nullable();
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
        Schema::dropIfExists('cashier');
    }
}
