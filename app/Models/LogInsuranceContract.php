<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\OauthAccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class LogInsuranceContract extends Model
{
    protected $table = 'log_insurance_contracts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code_agency',
        'name_agency',
        'activity',
        'contract_number',
        'status',
        'periodic_fee_type',
        'release_date',
        'effective_date',
        'fee_payment_date',
        'fee_payment_next_date',
        'pass_ack_date',
        'ack_date',
        'change_date',
        'ape_gross',
        'fyp_gross',
        'ape_cancel_in_flcfafi_terminated',
        'fyp_cancel_in_flcfafi_terminated',
        'ape_net',
        'fyp_net',
        'log_status',
        'insurance_company_id',
        'date_import'
    ];
}