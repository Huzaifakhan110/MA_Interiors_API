<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table="ht_projects";
    public $timestamps = false;
    protected $primaryKey='project_id';

    protected $fillable = ['project_name','budget','description','total_amount','company_id','start_date','contact_name','deadline','contact_email','contact_num_person','extra_amount','status'];

}
