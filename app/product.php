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
      'promotion_start',
      'promotion_end',
      'promotion_price'
    ];
}
