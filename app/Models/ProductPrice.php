<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductPrice extends Model
{
    protected $table = 'mp_product_prices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price', 'price_detail', 'product_id', 'conditions', 'price_type', 'product_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function saveConditions($attrKeys, $attrValues, $attrOperators, $attrMinValue, $attrMaxValue) {
        ProductPriceCondition::where("price_id", $this->id)->delete();
        if (count($attrKeys) <= 0)
            return;

        $records = [];
        for ($i=0; $i<count($attrKeys); $i++) {
            $obj = [
                "price_id" => $this->id,
                "attr_key" => $attrKeys[$i],
                "attr_value" => isset($attrValues[$i]) ? $attrValues[$i]: 0,
                "attr_operator" => $attrOperators[$i],
                "attr_min_value" => isset($attrMinValue[$i]) ? $attrMinValue[$i] : null,
                "attr_max_value" => isset($attrMaxValue[$i]) ? $attrMaxValue[$i] : null
            ];
            array_push($records, $obj);
        }
        ProductPriceCondition::insert($records);
    }

    /**
     * @param $params
     * @param $productId
     */
    public function saveUnitPriceType($params, $productId)
    {
        if (isset($params["unit_price_in_fee_health_insurance"]) && isset($params["unit_price_AA00123_health_insurance"])) {
            $product = Product::findOrFail($productId);
            $unitPriceType = [
                "in_fee" => $params["unit_price_in_fee_health_insurance"],
                'AA00123' => $params["unit_price_AA00123_health_insurance"],
            ];

            $unitPriceType = \GuzzleHttp\json_encode($unitPriceType);
            $product->unit_price_type_health_insurance = $unitPriceType;
            $product->save();
        }
    }
    /**
     * @param $productId
     * @param bool $getCondition
     * @return mixed
     */
    public static function getListByProduct($productId, $getCondition = false)
    {
        $cacheKey = 'list_price_by_product_' . $productId;
        if ($getCondition) {
            $cacheKey .= '_with_condition';
        }

        $prices = Cache::tags('product_prices')->remember($cacheKey, config('product.default_cache_time', 60), function () use ($productId, $getCondition) {
            $query = self::where('product_id', $productId);
            if ($getCondition) {
                $query->with('productPriceCondition');
            }

            return self::where('product_id', $productId)->get();
        });

        return $prices;
    }

    /**
     * The relationship
     */
    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product', 'product_id');
    }

    public function productPriceCondition()
    {
        return $this->hasMany('Modules\Product\Models\ProductPriceCondition', 'price_id');
    }
}
