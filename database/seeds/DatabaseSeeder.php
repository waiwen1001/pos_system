<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('invoice_sequence')->insert([
            'branch_code' => 'A',
            'current_seq' => '00000',
            'next_seq' => '00001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
