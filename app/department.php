<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class department extends Model
{
    protected $table="ht_department";
    public $timestamps = false;
    protected $primaryKey='dept_id';
}
