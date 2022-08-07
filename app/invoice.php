<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    protected $table="ht_invoice";
    public $timestamps = false;
    protected $primaryKey='inv_id';
}
