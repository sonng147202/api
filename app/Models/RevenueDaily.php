<?php

namespace App\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\RevenueContract;
use App\Models\RevenueProduct;
use App\Models\RevenueContractPlush;
use App\Models\Level;
use Modules\Product\Models\Product;

class RevenueDaily extends Model
{
    protected $table = 'revenue_dailys';

    protected $fillable = [
        'date',
        'insurance_agency_id',
        'contract_id',
        'level_id',
        'insurance_agency_id_incurred',
        'agency_level_id_incurred',
        'personal_revenue',
        'personal_tax',
        'personal_income_before_tax',
        'personal_income_after_tax',
        'branch_revenue',
        'branch_tax',
        'branch_income_before_tax',
        'branch_income_after_tax',
        'peer_revenue',
        'peer_tax',
        'peer_income_before_tax',
        'peer_income_after_tax',
        'status',
        'type',
        'revenue_cycle',
        'is_life_insurance_contract'
    ];

    public function insurance_agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency')->with('level');
    }

}