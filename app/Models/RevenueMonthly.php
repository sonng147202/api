<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueMonthly extends Model
{
    protected $table = 'revenue_monthly';
    protected $fillable = [
        'insurance_agency_id','self_revenue', 'branch_revenue','self_income', 'branch_income', 'month','day', 'year', 'nolife_before_tax','nolife_affter_tax','life_before_tax','	life_affter_tax'
    ];
    public function insurance_agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency');
    }
    
}
