<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\cash_float;
use App\User;
use App\pos_cashier;

class AddUsersInfoToCashFloatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_float', function (Blueprint $table) {
            $table->string('created_by')->after('user_id')->nullable();
            $table->string('cashier_name')->after('ip')->nullable();
        });

        $cash_float = cash_float::get();
        foreach($cash_float as $value)
        {
          $update_query = [];
          $update = false;
          if($value->user_id)
          {
            $update = true;
            $update_query['created_by'] = User::where('id', $value->user_id)->first()->name;
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
            cash_float::where('id', $value->id)->update($update_query);
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
        Schema::table('cash_float', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'cashier_name']);
        });
    }
}
