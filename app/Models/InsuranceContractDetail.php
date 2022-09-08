<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class InsuranceContractDetail extends Model
{
    protected $table = 'insurance_contract_details'; 

    protected $fillable = [
        'product_id', 
        'insurance_contract_id', 
        'contract_number',
        'code',
        'contract_year', 
        'insurance_fee', 
        'insurance_fee_received', 
        'investment_fee',
        'periodic_fee_type',
        'release_date',
        'expired_date',
        'transaction_date',
        'revenue_fee',
        'tax',
        'age_of_insured',
        'insurance_factor',
        'insurance_factor_type'
    ];

    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product', 'product_id');
    }
}
