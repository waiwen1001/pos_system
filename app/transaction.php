<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class transaction extends Model
{
    protected $table = 'transaction';
    protected $fillable = [
      'transaction_no',
      'invoice_no',
      'user_id',
      'subtotal',
      'total_discount',
      'payment',
      'payment_type',
      'balance',
      'void',
      'completed',
    ];
}
