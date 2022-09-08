<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InsuranceExtraFee extends Model
{
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The relationship
     */
    public function insurance_type()
    {
        return $this->belongsTo('App\Models\InsuranceType');
    }

    /**
     * Return list price attribute with attribute-code as array key by multi type
     *
     * @param $insuranceTypeIds
     * @return array
     */
    public static function getListWithKeyCode($insuranceTypeIds)
    {
        $attributes = self::whereIn('type_id', $insuranceTypeIds)->orderBy('order_number')->get()->toArray();

        if ($attributes) {
            $tmpData = [];

            foreach ($attributes as $attribute) {
                $tmpData[$attribute['code']] = $attribute;
            }

            $attributes = $tmpData;
        }

        return $attributes;
    }

    /**
     * Return list price attribute with attribute-code as array key
     *
     * @param $insuranceTypeId
     * @return array
     */
    public static function getListWithKeyCodeByTypeId($insuranceTypeId)
    {
        $cacheKey = 'list_extra_fees_by_type_' . $insuranceTypeId;

        $attributes = Cache::tags('product')->remember($cacheKey, config('product.default_cache_time', 60), function () use ($insuranceTypeId) {
            $attributes = self::where('insurance_type_id', $insuranceTypeId)->orderBy('order_number')->get()->toArray();

            if ($attributes) {
                $tmpData = [];

                foreach ($attributes as $attribute) {
                    $tmpData[$attribute['code']] = $attribute;
                }

                $attributes = $tmpData;
            }

            return $attributes;
        });

        return $attributes;
    }

    /**
     * @param $id
     * @return $this
     */
    public static function getDetail($id)
    {
        $cacheKey = 'insurance_extra_fee_' . $id;
        $extraFee = Cache::tags('product')->remember($cacheKey, config('product.default_cache_time', 60), function () use ($id) {
            return self::where('id', $id)->first();
        });
        return $extraFee;
    }

    /**
     * @param $code
     * @return mixed
     */
    public static function getDetailByCode($code)
    {
        $cacheKey = 'insurance_extra_fee_code_' . $code;

        $extraFee = Cache::tags('product')->remember($cacheKey, config('product.default_cache_time', 60), function () use ($code) {
            return self::where('code', $code)->first();
        });
        return $extraFee;
    }
}
