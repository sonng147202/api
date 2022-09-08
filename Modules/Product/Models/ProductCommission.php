<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCommission extends Model
{
    protected $table = 'mp_product_commissions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'commission_type', 'commission_amount', 'subsidiary_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    const PERCENT = 0;
    const FIX_AMOUNT = 1;

    public function getCommissionTypeName() {
        if ($this->commission_type == ProductCommission::FIX_AMOUNT)
            return "Tiền";
        else
            return "Giá trị %";
    }

    public function getCommissionAmountFormat() {
        if ($this->commission_type == ProductCommission::FIX_AMOUNT)
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
     * Get commission Amount
     * @param $product_id
     * @return Model|null|static
     */
    public static function getCommistionInfo($product_id, $is_check_subsidiary, $insurance_subsidiary_id){
        //Lấy công thức tính hoa hồng cho công ty con
        if (!empty($is_check_subsidiary && !empty($insurance_subsidiary_id))) {
            return self::where([
                'product_id' => $product_id,
                'subsidiary_id' => $insurance_subsidiary_id
            ])->latest()->first();
        }
        //Lấy công thức tính hoa hồng cho công ty mẹ
        else {
            return self::where('product_id',$product_id)
                ->where('subsidiary_id',0)->latest()->first();
        }
    }
}
