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

class InsuranceAgencyReport extends Model
{
    protected $table = 'insurance_agency_reports';

    public $timestamps = true;

    protected $fillable = [
        'insurance_agency_id', 'code_agency_fwd', 'name_agency_fwd','phone_agency_fwd',
        'id_card_number_customer','name_customer','birthday_customer','sex_customer','email_customer',
        'phone_customer','address_customer','contract_number','fee_payment_date','product_id',
        'product_main_fee','sub_product_quantity','sub_product_fee','img_url','img_info_url'
    ];

    public function insurance_agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'insurance_agency_id');
    }

}
    