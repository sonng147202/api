<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVBIInsurancePackage extends Model
{
    protected $table = 'mp_product_vbi_insurance_packages';
    protected $fillable = [
        'product_id',
        'vbi_id',
        'group',
        'code',
        'name',
        'from_age',
        'to_age',
        'gender',
        'fee',
        'insurance_money',
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function productVBIAdditionConditions()
    {
        return $this->hasMany('App\Models\ProductVBIAdditionCondition', 'insurance_package_id');
    }

}
