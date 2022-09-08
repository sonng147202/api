<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAgencyCommission extends Model
{
    protected $table = 'mp_product_agency_commissions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'agency_id','commission_type', 'commission_amount'
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
        if ($this->commission_type == ProductAgencyCommission::FIX_AMOUNT)
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
        return $this->belongsTo('Modules\Product\Models\Product', 'product_id');
    }

    public function insurance_agency()
    {
        return $this->belongsTo('Modules\Insurance\Models\InsuranceAgency', 'agency_id');
    }
}
