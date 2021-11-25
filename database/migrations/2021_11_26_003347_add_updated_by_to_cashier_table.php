<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedByToCashierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashier', function (Blueprint $table) {
            $table->integer('updated_by')->after('synced')->nullable();
            $table->string('updated_by_name')->after('updated_by')->nullable();
        });

        Schema::table('cash_float', function (Blueprint $table) {
            $table->integer('updated_by')->after('synced')->nullable();
            $table->string('updated_by_name')->after('updated_by')->nullable();
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
            $table->dropColumn(['updated_by', 'updated_by_name']);
        });

        Schema::table('cash_float', function (Blueprint $table) {
            $table->dropColumn(['updated_by', 'updated_by_name']);
        });
    }
}
