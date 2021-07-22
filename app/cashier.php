<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cashier extends Model
{
    protected $table = 'cashier';
    protected $fillable = [
      'branch',
      'ip',
      'cashier_name',
      'session_id',
      'opening',
      'opening_by',
      'opening_by_name',
      'opening_amount',
      'opening_date_time',
      'closing',
      'closing_by',
      'closing_by_name',
      'closing_amount',
      'calculated_amount',
      'diff',
      'closing_date_time',
      'synced'
    ];
}
