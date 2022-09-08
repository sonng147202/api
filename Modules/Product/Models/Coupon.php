<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'mp_coupons';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'coupon_code', 'start_time', 'end_time', 'status', 'sale_off_type', 'sale_off_amount'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = -1;
    const PERCENT = 0;
    const FIX_AMOUNT = 1;

    public function getStatusName() {
        if ($this->status == self::STATUS_ACTIVE)
            return "Đã sử dụng";
        elseif ($this->status == self::STATUS_INACTIVE)
            return "Chưa sử dụng";
        else
            return "Đã xóa";
    }

    public function getCommissionTypeName() {
        if ($this->sale_off_type == Coupon::FIX_AMOUNT)
            return "Tiền";
        else
            return "Giá trị %";
    }

    public function getCouponAmountFormat() {
        if ($this->sale_off_type == Coupon::FIX_AMOUNT)
            return number_format($this->sale_off_amount);
        else
            return $this->sale_off_amount . "%";
    }
}
