<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    protected $table="ht_accounts";
    public $timestamps = false;
    protected $primaryKey='acc_id';
    protected $fillable = ['acc_title','bank_name','acc_number','status','currency','bank_phone','bank_address','opening_balance','current_balance'];
}
