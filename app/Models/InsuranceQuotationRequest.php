<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Lib\InsuranceHelper;

class InsuranceQuotationRequest extends Model
{
    protected $guarded = [];

    public function insurance_type()
    {
        return $this->belongsTo('App\Models\InsuranceType');
    }

    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product', 'product_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id');
    }

    /**
     * @param $insuranceTypeId
     * @param $params
     * @return $this|Model
     */
    public static function createQuotationRequest($insuranceTypeId, $params)
    {
        // Create quotation request
        return self::create([
            'insurance_type_id' => $insuranceTypeId,
            'filter_data'       => is_array($params['filter_data']) ? json_encode($params['filter_data']) : $params['filter_data'],
            'request_email'     => isset($params['email']) ? $params['email'] : '',
            'customer_id'       => isset($params['customer_id']) ? $params['customer_id'] : 0,
            'agency_id'         => isset($params['agency_id']) ? $params['agency_id'] : 0,
            'product_id'        => isset($params['product_id']) ? $params['product_id'] : 0,
            'company_id'        => isset($params['company_id']) ? $params['company_id'] : 0,
            'request_device'    => isset($params['device_info']) ? $params['device_info'] : 0
        ]);
    }
}
