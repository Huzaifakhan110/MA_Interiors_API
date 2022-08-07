<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance_emp extends Model
{
    protected $table="ht_employee_attendance";
    public $timestamps = false;
    // protected $primaryKey='cont_id';
    protected $fillable = ['date','emp_id','time_in','time_out','status'];

}
