<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class resyncRefund extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('refund')->where('synced', 1)->update([
          'synced' => null
        ]);
    }
}
