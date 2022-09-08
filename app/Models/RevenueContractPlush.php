<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueContractPlush extends Model
{
    protected $table = 'revenue_contracts_plush';
    protected $fillable = [
        'insurance_agency_id','product_id', 'contract_value','agency_type','self_income', 'branch_income', 'child_agency_id', 'self_commission', 'branch_commission', 'start_time', 'end_time', 'contract_id', 'date_signed', 'is_system','nolife_affter_tax','life_before_tax',
        'life_affter_tax','nolife_before_tax','PFYP'
    ];
    public function insurance_agency ()
    {
        return $this->belongsTo('App\Models\InsuranceAgency');
    }
    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product');
    }
    public function contract()
    {
        return $this->belongsTo('App\Models\InsuranceContract');
    }
}
