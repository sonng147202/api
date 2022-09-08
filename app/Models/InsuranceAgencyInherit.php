<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\OauthAccessToken;
use Modules\Product\Models\ProductLevelCommission;
use App\Models\InsuranceContract;
use App\Models\AgencyWallet;
use App\Models\AgencyComment;
use Modules\Product\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class InsuranceAgencyInherit extends Model
{
    protected $table = 'insurance_agencies_inherits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'relationship', 'phone','insurance_agency_id'
    ];

    public function parent()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'insurance_agency_id');
    }
}