<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $table = 'mp_product_categories';

    protected $fillable = [];

    public function category()
    {
        return $this->belongsTo('Modules\Product\Models\Category');
    }

    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product');
    }
}
