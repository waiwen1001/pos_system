<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cash_float extends Model
{
  protected $table = 'cash_float';
  protected $fillable = [
    'user_id',
    'ip',
    'session_id',
    'opening_id',
    'type',
    'amount',
    'remarks'
  ];
}
