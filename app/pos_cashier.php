<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class pos_cashier extends Model
{
    protected $table = 'pos_cashier';
    protected $fillable = [
      'ip',
      'cashier_name'
    ];
}
