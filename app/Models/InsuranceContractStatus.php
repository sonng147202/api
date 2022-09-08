<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InsuranceContractStatus extends Model
{
    protected $table = 'insurance_contract_status';

    protected $fillable = ['code', 'name', 'insurance_company_id'];

}
