<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session', function (Blueprint $table) {
            $table->increments('id');
            $table->string('branch')->nullable();
            $table->string('ip')->nullable();
            $table->dateTime('opening_date_time')->nullable();
            $table->dateTime('closing_date_time')->nullable();
            $table->integer('closed')->nullable();
            $table->timestamps();
        });

        Schema::table('cashier', function (Blueprint $table) {
            $table->integer('session_id')->after('ip')->nullable();
        });

        Schema::table('transaction', function (Blueprint $table) {
            $table->integer('session_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('session');

        Schema::table('cashier', function (Blueprint $table) {
            $table->dropColumn('session_id');
        });

        Schema::table('transaction', function (Blueprint $table) {
            $table->dropColumn('session_id');
        });
    }
}
