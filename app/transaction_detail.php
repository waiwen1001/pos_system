<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class transaction_detail extends Model
{
    protected $table = 'transaction_detail';
    protected $fillable = [
      'transaction_id',
      'product_id',
      'product_name',
      'price',
      'quantity',
      'discount',
      'subtotal',
      'total',
      'void',
    ];
}
