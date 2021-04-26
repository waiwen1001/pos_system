<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class shortcut_key extends Model
{
    protected $table = 'shortcut_key';
    protected $fillable = [
      'code',
      'character',
      'function',
      'function_name',
    ];
}
