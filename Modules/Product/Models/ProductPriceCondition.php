<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductPriceCondition extends Model
{
    protected $table = 'mp_product_price_conditions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price_id', 'attr_key', 'attr_value', 'attr_operator'
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
    public function price()
    {
        return $this->belongsTo('Modules\Product\Models\ProductPrice', 'price_id');
    }

    /**
     * @param $priceId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getDetailByPrice($priceId)
    {
        return self::where('price_id', $priceId)->get();
    }

    public static function getListAttributeKeyByProduct($productId)
    {
        $cacheKey = 'list_price_attribute_key_by_product_' . $productId;
        $attributeKeys = Cache::tags('product_price_condition')->remember($cacheKey, config('product.default_cache_time', 60), function () use ($productId) {
            // Get list price with conditions
            $productPrices = ProductPrice::getListByProduct($productId, true);
            $attributeKeys = [];
            if ($productPrices) {
                foreach ($productPrices as $productPrice) {
                    // Check list condition
                    if ($productPrice->productPriceCondition) {
                        foreach ($productPrice->productPriceCondition as $priceCondition) {
                            if (!in_array($priceCondition->attr_key, $attributeKeys)) {
                                $attributeKeys[] = $priceCondition->attr_key;
                            }
                        }
                    }
                }
            }

            return $attributeKeys;
        });

        return $attributeKeys;
    }
}
