<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class transfer extends Model
{
    protected $table="ht_transactions";
    public $timestamps = false;
    protected $primaryKey='tran_id';
    protected $fillable = ['tran_acc_id','cust_id','tran_amount','tran_date'];

}
