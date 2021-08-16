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
      'measurement',
      'promotion_start',
      'promotion_end',
      'promotion_price',
      'normal_wholesale_price',
      'normal_wholesale_price2',
      'normal_wholesale_price3',
      'normal_wholesale_price4',
      'normal_wholesale_price5',
      'normal_wholesale_price6',
      'normal_wholesale_price7',
      'normal_wholesale_quantity',
      'normal_wholesale_quantity2',
      'normal_wholesale_quantity3',
      'normal_wholesale_quantity4',
      'normal_wholesale_quantity5',
      'normal_wholesale_quantity6',
      'normal_wholesale_quantity7',
      'wholesale_price',
      'wholesale_price2',
      'wholesale_price3',
      'wholesale_price4',
      'wholesale_price5',
      'wholesale_price6',
      'wholesale_price7',
      'wholesale_quantity',
      'wholesale_quantity2',
      'wholesale_quantity3',
      'wholesale_quantity4',
      'wholesale_quantity5',
      'wholesale_quantity6',
      'wholesale_quantity7',
      'wholesale_start_date',
      'wholesale_end_date',
      'deleted_at'
    ];
}
