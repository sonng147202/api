<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVBIAdditionCondition extends Model
{
    protected $table = 'mp_product_vbi_addition_conditions';
    protected $fillable = [
        'insurance_package_id',
        'code',
        'name',
        'fee',
        'insurance_money',
    ];

    public function productVBIInsurancePackage()
    {
        return $this->belongsTo('App\Models\ProductVBIInsurancePackage', 'insurance_package_id');
    }
}
