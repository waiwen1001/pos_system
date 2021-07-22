<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class refund extends Model
{
    protected $table = 'refund';
    protected $fillable = [
      'session_id',
      'opening_id',
      'ip',
      'cashier_name',
      'user_id',
      'user_name',
      'transaction_no',
      'subtotal',
      'round_off',
      'total',
      'synced',
    ];
}
