<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class personalExpenseRecord extends Model
{
    protected $table="ht_other_expense_personal_expense_record";
    public $timestamps = false;
    protected $primaryKey='id';
}
