<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTotalToTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->decimal('total', 12, 2)->change();
            $table->decimal('total_discount', 12, 2)->change();
            $table->decimal('subtotal', 12, 2)->change();
            $table->decimal('payment', 12, 2)->change();
            $table->decimal('balance', 12, 2)->change();
            $table->decimal('round_off', 12, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction', function (Blueprint $table) {
            //
        });
    }
}
