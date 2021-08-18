<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class transaction extends Model
{
    use SoftDeletes;

    protected $table = 'transaction';
    protected $fillable = [
      'session_id',
      'opening_id',
      'ip',
      'cashier_name',
      'transaction_no',
      'reference_no',
      'user_id',
      'subtotal',
      'total_discount',
      'voucher_id',
      'voucher_code',
      'payment',
      'payment_type',
      'payment_type_text',
      'balance',
      'total',
      'round_off',
      'void',
      'void_by',
      'void_date',
      'completed',
      'completed_by',
      'transaction_date'
    ];
}
