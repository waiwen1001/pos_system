<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class profile extends Model
{
    protected $table = 'profile';
    protected $fillable = [
      'branch_name',
      'address',
      'contact_number'
    ];
}
