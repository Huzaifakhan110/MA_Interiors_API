<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table="ht_employee";
    public $timestamps = false;
    protected $primaryKey='emp_id';

    protected $fillable = ['emp_name','emp_phone','job_type','dept_id','emp_salary'];
}
