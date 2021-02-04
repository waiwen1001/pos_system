<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class transaction extends Model
{
    protected $table = 'transaction';
    protected $fillable = [
      'session_id',
      'transaction_no',
      'invoice_no',
      'user_id',
      'subtotal',
      'total_discount',
      'voucher_id',
      'payment',
      'payment_type',
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
