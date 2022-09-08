<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenuePlush extends Model
{
    protected $table = 'revenue_plush';
    protected $fillable = [
        'isurance_agency_id','personal_income','low_level_revenue'
    ];
    public function insurance_agency ()
    {
        return $this->belongsTo('App\Models\InsuranceAgency');
    }
}
