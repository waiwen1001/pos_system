<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice_sequence extends Model
{
    protected $table = 'invoice_sequence';
    protected $fillable = [
      'branch_code',
      'current_seq',
      'next_seq',
    ];
}
