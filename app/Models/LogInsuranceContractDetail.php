<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class LogInsuranceContractDetail extends Model
{
    protected $table = 'log_insurance_contract_details'; 

    protected $fillable = [
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
        'log_status',
        'insurance_company_id',
        'date_import'
    ];

    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product', 'code','code');
    }
}
