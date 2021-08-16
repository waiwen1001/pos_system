<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class transaction_detail extends Model
{
    protected $table = 'transaction_detail';
    protected $fillable = [
      'transaction_id',
      'department_id',
      'category_id',
      'product_id',
      'barcode',
      'product_name',
      'price',
      'quantity',
      'measurement_type',
      'measurement',
      'wholesale_price',
      'wholesale_quantity',
      'discount',
      'subtotal',
      'total',
      'void',
    ];
}
