<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiffToCashierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashier', function (Blueprint $table) {
            $table->double('calculated_amount', 2)->after('closing_amount')->nullable();
            $table->double('diff', 2)->after('calculated_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashier', function (Blueprint $table) {
            $table->dropColumn(['calculated_amount', 'diff']);
        });
    }
}
