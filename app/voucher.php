<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class voucher extends Model
{
    protected $table = 'voucher';
    protected $fillable = [
      'name',
      'code',
      'type',
      'amount',
      'active',
    ];
}
