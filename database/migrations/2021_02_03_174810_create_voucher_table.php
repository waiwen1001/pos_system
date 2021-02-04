<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->double('amount', 15, 2)->nullable();
            $table->integer('active')->nullable();
            $table->timestamps();
        });

        Schema::table('transaction', function (Blueprint $table) {
          $table->integer('voucher_id')->after('total_discount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher');

        Schema::table('transaction', function (Blueprint $table) {
            $table->dropColumn(['voucher_id']);
        });
    }
}
