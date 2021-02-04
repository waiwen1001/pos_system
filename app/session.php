<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class session extends Model
{
    protected $table = 'session';
    protected $fillable = [
      'branch',
      'ip',
      'opening_date_time',
      'closing_date_time',
      'closed'
    ];
}
