<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use SoftDeletes;

    protected $table = 'mp_commissions';

    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'commission_type', 'commission_amount'
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
}
