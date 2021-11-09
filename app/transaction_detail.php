<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class transaction_detail extends Model
{
    use SoftDeletes;
    
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
      'product_info',
      'product_type',
      'wholesale_price',
      'wholesale_quantity',
      'discount',
      'subtotal',
      'total',
      'void',
    ];
}
