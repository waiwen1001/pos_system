<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class transaction extends Model
{
    protected $table = 'transaction';
    protected $fillable = [
      'session_id',
      'ip',
      'cashier_name',
      'transaction_no',
      'invoice_no',
      'user_id',
      'subtotal',
      'total_discount',
      'voucher_id',
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
