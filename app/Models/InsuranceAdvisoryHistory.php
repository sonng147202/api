<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Car\Models\CarBrand;
use Modules\Car\Models\CarModel;
use Modules\Car\Models\CarModelTrim;

class InsuranceAdvisoryHistory extends Model
{
    protected $guarded = [];

    public function insurance_type()
    {
        return $this->belongsTo('App\Models\InsuranceType');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }

    /**
     * @param $insuranceTypeId
     * @param $customerId
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public static function getList($insuranceTypeId, $customerId, $page = 1, $pageSize = 20)
    {
        $query = self::with(['customer', 'insurance_type'])->orderBy('created_at', 'desc');

        if (!empty($insuranceTypeId)) {
            $query->where('insurance_type_id', $insuranceTypeId);
        }

        if (!empty($customerId)) {
            $query->where('customer_id', $insuranceTypeId);
        }

        $listAdvisory = $query->offset(($page - 1) * $pageSize)->take($pageSize)->get();

        if ($listAdvisory) {
            $tmpData = [];

            foreach ($listAdvisory as $advisory) {
                // Get advisory text:
                $params = json_decode($advisory->request_params, true);
                $paramStr = [];
                foreach ($params as $key => $value) {
                    $paramStr[] = self::getStringForParam($key, $value);
                }

                $tmpData[] = [
                    'id'                => $advisory->id,
                    'customer_id'       => $advisory->customer_id,
                    'customer_name'     => !empty($advisory->customer) ? $advisory->customer->name : '',
                    'insurance_type_id' => $advisory->insurance_type_id,
                    'insurance_type'    => !empty($advisory->insurance_type) ? $advisory->insurance_type->name : '',
                    'request_string'    => implode(',', $paramStr),
                    'full_name'         => $advisory->full_name,
                    'email'             => $advisory->email,
                    'phone'             => $advisory->phone,
                ];
            }

            return $tmpData;
        } else {
            return [];
        }
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    protected static function getStringForParam($key, $value)
    {
        $str = '';

        switch ($key) {
            case 'car_brand':
                // Get car brand
                $carBrand = CarBrand::find($value);
                $str = 'H??ng xe: ' . isset($carBrand->name) ? $carBrand->name : '';
                break;
            case 'car_model':
                $carModel = CarModel::find($value);
                $str = 'D??ng xe: ' . isset($carModel->name) ? $carModel->name : '';
                break;
            case 'year_manufacture':
                $str = 'N??m s???n xu???t: ' . $value;
                break;
            case 'car_trim':
                $carTrim = CarModelTrim::find($value);
                $str = 'Trim: ' . isset($carTrim->name) ? $carTrim->name : '';
                break;
            case 'house_type':
                $str = 'Lo???i nh??: ' . ($value == 1) ? 'Nh??': 'Chung c??';
                break;
            case 'house_create_year':
                $str = 'N??m x??y d???ng: ' . $value;
                break;
            case 'house_price':
                $str = 'Gi?? tr??? nh??: ' . $value;
                break;
            case 'travel_insurance_method':
                $str = 'Lo???i h??nh b???o hi???m: ' . ($value == 1) ? 'Theo n??m': 'Theo chuy???n';
                break;
            case 'trip_type':
                $str = 'H??nh th???c du l???ch: ' . $value;
                break;
            case 'trip_start':
                $str = 'Ng??y ??i: ' . $value;
                break;
            case 'trip_end':
                $str = 'Ng??y v???: ' . $value;
                break;
        }

        return $str;
    }
}
