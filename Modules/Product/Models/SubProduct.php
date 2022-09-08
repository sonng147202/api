<?php


namespace Modules\Product\Models;


use Illuminate\Database\Eloquent\Model;

class SubProduct extends Model
{
    protected $table = 'sub_products';

    protected $fillable = [
        'product_name',
        'status'
    ];
    const ACTIVE = 1;
    const UN_ACTIVE = 0;
}
