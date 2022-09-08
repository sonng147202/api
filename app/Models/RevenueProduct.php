<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueProduct extends Model
{
    protected $fillable = [
        'insurance_agency_id',
        'product_id',
        'self_revenue',
        'branch_revenue',
        'self_income',
        'branch_income',
        'year',
        'month',
        'day',
        'date',
    ];

    public function insurance_agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency');
    }

    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product');
    }
}
