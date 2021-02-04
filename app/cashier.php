<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cashier extends Model
{
    protected $table = 'cashier';
    protected $fillable = [
      'branch',
      'ip',
      'session_id',
      'opening',
      'opening_by',
      'opening_amount',
      'opening_date_time',
      'closing',
      'closing_by',
      'closing_amount',
      'closing_date_time',
    ];
}
