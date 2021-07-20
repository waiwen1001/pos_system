<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class refund_detail extends Model
{
    protected $table = 'refund_detail';
    protected $fillable = [
      'refund_id',
      'department_id',
      'category_id',
      'product_id',
      'barcode',
      'product_name',
      'quantity',
      'price',
      'subtotal',
      'total',
    ];

    
}
