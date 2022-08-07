<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table="ht_other_expense";
    public $timestamps = false;
    protected $primaryKey='Id';
}
