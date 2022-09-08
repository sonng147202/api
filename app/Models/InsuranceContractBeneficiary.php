<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InsuranceContractBeneficiary extends Model
{
    protected $fillable = ['contract_id', 'beneficiary_type_id', 'status', 'value','price_type_custom_health_insurance'];

    /**
     * Relationship
     */
    public function insurance_contract()
    {
        return $this->belongsTo('App\Models\InsuranceContract', 'contract_id');
    }

    public function beneficiary_type()
    {
        return $this->belongsTo('App\Models\BeneficiaryType');
    }

    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product','product_id');
    }

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = -1;

    public function getStatusName() {
        if ($this->status == self::STATUS_ACTIVE)
            return "Đã kích hoạt ";
        elseif ($this->status == self::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    public function getValueString() {
        if (empty($this->value))
            return "";
        $str = "";
        $attributes = BeneficiaryTypeAttribute::where('beneficiary_type_id', $this->beneficiary_type_id)->get();
        $attrs = $attributes->mapWithKeys(function ($item) {
                return [$item->id => $item->name];
        });
        $values = json_decode($this->value);
        foreach($values as $key => $value) {
            if (isset($attrs[$key])) {
                $str = $str . $attrs[intval($key)] . ": " . $value . ". ";
            }
        }
        return $str;
    }

    /**
     * Get list beneficiary by contract id
     *
     * @param $contractId
     * @return array|bool
     */
    public static function getListByContract($contractId)
    {
        $cacheKey = 'list_beneficiary_contract_' . $contractId;

        $listBeneficiary = Cache::tags('contract_info_' . $contractId)->remember($cacheKey, config('insurance.default_cache_time', 60), function () use ($contractId) {
            $listItem = self::where('contract_id', $contractId)->with('product')->get();

            if ($listItem) {
                $data = [];
                foreach ($listItem as $item){
                    $tmp = json_decode($item->value,true);
                    $item = $item->toArray();
                    $item['attributes'] = $tmp;
                    $data[] = $item;
                }

                return $data;
            } else {
                return false;
            }
        });

        return $listBeneficiary;
    }
}
