<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InsurancePriceAttribute extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id', 'code', 'title', 'order_number', 'default_value', 'show_in_filter_form'
    ];

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
        return $this->belongsTo('App\Models\InsuranceType', 'type_id');
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
        $cacheKey = 'insurance_price_attributes_type_' . $insuranceTypeId;

        $attributes = Cache::tags('insurance_price_attribute')
            ->remember($cacheKey, config('insurance.default_cache_time', 60), function () use ($insuranceTypeId) {

                $attributes = self::where('type_id', $insuranceTypeId)->orderBy('order_number')->get()->toArray();

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
}
