<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    protected $table = 'product';
    protected $fillable = [
      'department_id',
      'category_id',
      'barcode',
      'product_name',
      'price',
      'uom',
      'promotion_start',
      'promotion_end',
      'promotion_price',
      'wholesale_price',
      'wholesale_quantity',
      'wholesale_start_date',
      'wholesale_end_date'
    ];
}
