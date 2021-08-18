<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeasurementToRefundDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refund_detail', function (Blueprint $table) {
            $table->string('measurement_type')->after('quantity')->nullable();
            $table->decimal('measurement', 12, 4)->after('measurement_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('refund_detail', function (Blueprint $table) {
            $table->dropColumn(['measurement_type', 'measurement']);
        });
    }
}
