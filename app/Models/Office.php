<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $table = 'offices';
    protected $fillable = [
        'name','insurance_agency_id','address','phone','office_type','effective_date','support_charge_date','office_parent_id','status'
    ];

    public function managerOffice(){
        return $this->hasOne('App\Models\InsuranceAgency','id','insurance_agency_id');
    }

    public function parentOffice(){
        return $this->hasOne('App\Models\Office','id','office_parent_id');
    }

    public function insurance_agency()
    {
        return $this->hasMany('App\Models\InsuranceAgency','office_id');
    }

}

