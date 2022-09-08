<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeFee extends Model
{
    protected $table = 'office_fees';
    protected $fillable = [
        'type','date','insurance_agency_id','office_id','pfyp','rate','fee','insurance_agency_child_id','fee_by_child','office_child_id','office_child_pfyp','office_child_rate'
    ];

    public function insurance_agencies()
    {
        return $this->hasMany('App\Models\InsuranceAgency','office_id');
    }

}

