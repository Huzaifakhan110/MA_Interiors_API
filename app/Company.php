<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table="ht_company";
    public $timestamps = false;
    protected $primaryKey='company_id';

    protected $fillable = ['company_name','contact_no','contact_name','email'];
}
