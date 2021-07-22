<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyncedToCashierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashier', function (Blueprint $table) {
            $table->string('synced', 1)->after('closing_date_time')->nullable();
        });

        Schema::table('cash_float', function (Blueprint $table) {
            $table->string('synced', 1)->after('remarks')->nullable();
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
            $table->dropColumn(['synced']);
        });

        Schema::table('cash_float', function (Blueprint $table) {
            $table->dropColumn(['synced']);
        });
    }
}
