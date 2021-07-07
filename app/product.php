<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class product extends Model
{
    use SoftDeletes;

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
      'wholesale_end_date',
      'deleted_at'
    ];
}
