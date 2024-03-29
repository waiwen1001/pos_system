<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeInvoiceNoToReferenceNoToTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->renameColumn('invoice_no', 'reference_no');
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
            $table->renameColumn('reference_no', 'invoice_no');
        });
    }
}
