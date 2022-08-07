<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class expense_account extends Model
{
    protected $table="ht_expense_account";
    public $timestamps = false;
    // protected $primaryKey='dept_id';
    protected $fillable = [
        'amount',
     
    ];
}
