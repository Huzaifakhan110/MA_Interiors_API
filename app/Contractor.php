<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $table="ht_contractors";
    public $timestamps = false;
    protected $primaryKey='cont_id';
    protected $fillable = ['cont_name','cont_phone','email','specification'];

}
