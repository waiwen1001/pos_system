<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransactionDateToTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->double('total', 2)->after('balance')->nullable();
            $table->integer('completed_by')->after('completed')->nullable();
            $table->dateTime('transaction_date')->after('completed_by')->nullable();
            $table->integer('void_by')->after('void')->nullable();
            $table->dateTime('void_date')->after('void_by')->nullable();

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
            $table->dropColumn(['total', 'completed_by', 'transaction_date', 'void_by', 'void_date']);
        });
    }
}
