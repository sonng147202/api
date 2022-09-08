<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInsuranceType extends Model
{
    protected $table = 'mp_product_insurance_types';

    protected $guarded = [];

    public function insurance_type()
    {
        return $this->belongsTo('Modules\Insurance\Models\InsuranceType');
    }

    /**
     * @param $productId
     * @return mixed
     */
    public static function getByProduct($productId)
    {
        return self::where('product_id', $productId)->first();
    }
}
