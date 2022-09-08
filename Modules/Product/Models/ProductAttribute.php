<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $table = 'mp_product_attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'attribute_id', 'attribute_data'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function saveAtributes($attrs, $productId) {
        ProductAttribute::where("product_id", $productId)->delete();
        if (empty($attrs))
            return;

        $records = [];
        foreach ($attrs as $key => $value) {
            if (intval($key) == 0)
                continue;
            $obj = [
                "product_id" => $productId,
                "attribute_id" => $key,
                "attribute_data" => $value,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];
            array_push($records, $obj);
        }
        ProductAttribute::insert($records);
    }

    /**
     * The relationship
     */

    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product', 'product_id');
    }
    public function category_attribute()
    {
        return $this->belongsTo('Modules\Product\Models\CategoryAttribute', 'attribute_id');
    }
}
