<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'subject','content','attach_file','levels','date_join_start','date_join_end','offices','is_office_director',
        'revenue_type','revenue_year','revenue_quarter','revenue_month','revenue_from','revenue_to','group_id','status',
        'time_send','users','type','created_by','category','image','description','display_date'
    ];

    public function insuranceagencies()
    {
        return $this->hasMany('App\Models\InsuranceAgency','is_office_director');
    }

}

