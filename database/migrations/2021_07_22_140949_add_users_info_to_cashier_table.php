<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\cashier;
use App\User;
use App\pos_cashier;

class AddUsersInfoToCashierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashier', function (Blueprint $table) {
            $table->string('opening_by_name')->after('opening_by')->nullable();
            $table->string('closing_by_name')->after('closing_by')->nullable();
            $table->string('cashier_name')->after('ip')->nullable();
        });

        $cashier = cashier::get();
        foreach($cashier as $value)
        {
          $update_query = [];
          $update = false;
          if($value->opening_by)
          {
            $update = true;
            $update_query['opening_by_name'] = User::where('id', $value->opening_by)->first()->name;
          }

          if($value->closing_by)
          {
            $update = true;
            $update_query['closing_by_name'] = User::where('id', $value->closing_by)->first()->name;
          }

          if($value->ip)
          {
            $pos_cashier = pos_cashier::where('ip', $value->ip)->first();
            if($pos_cashier)
            {
              $update = true;
              $update_query['cashier_name'] = $pos_cashier->cashier_name;
            }
          }

          if($update == true)
          {
            cashier::where('id', $value->id)->update($update_query);
          }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashier', function (Blueprint $table) {
            $table->dropColumn(['opening_by_name', 'closing_by_name', 'cashier_name']);
        });
    }
}
