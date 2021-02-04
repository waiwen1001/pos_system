<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameIpToCashierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashier', function (Blueprint $table) {
            $table->renameColumn('IP', 'ip');
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
            $table->renameColumn('ip', 'IP');
        });
    }
}
