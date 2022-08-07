<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class currency extends Model
{
    protected $table="ht_currency";
    public $timestamps = false;
    protected $primaryKey='currency_id';
}
