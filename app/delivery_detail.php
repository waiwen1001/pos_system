<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class delivery_detail extends Model
{
    use SoftDeletes;
    
    protected $table = 'delivery_detail';
    protected $fillable = [
      'delivery_id',
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
      'discount',
      'subtotal',
      'total',
      'void',
    ];
}
