<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class specifications extends Model
{
    protected $table="ht_specifications";
    public $timestamps = false;
    protected $primaryKey='spec_id';
}
