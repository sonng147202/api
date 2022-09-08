<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProductCustomerCommission extends Model
{
    protected $table = 'mp_product_customer_commissions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    const PERCENT = 0;
    const FIX_AMOUNT = 1;

    public function getCommissionTypeName() {
        if ($this->commission_type == self::FIX_AMOUNT)
            return "Tiền";
        else
            return "Giá trị %";
    }

    public function getCommissionAmountFormat() {
        if ($this->commission_type == self::FIX_AMOUNT)
            return number_format($this->commission_amount);
        else
            return $this->commission_amount . "%";
    }

    /**
     * The relationship
     */
    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product');
    }

    /**
     * @param $productId
     * @return mixed
     */
    public static function getByProduct($productId)
    {
        $cacheKey = 'product_customer_commission_' . $productId;

        $commission = Cache::tags('product_commissions')->remember($cacheKey, config('product.default_cache_time', 60), function () use ($productId) {
            return self::where('product_id', $productId)->latest()->first();
        });

        return $commission;
    }
}
