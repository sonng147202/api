<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryAttribute extends Model
{
    protected $table = 'mp_category_attributes';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'name', 'title', 'data_type', 'is_required', 'default_value', 'compare_flg'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    const REQUIRED = 1;
    const NOT_REQUIRED = 0;
    const COMPARE = 1;
    const NOT_COMPARE = 0;

    /**
     * The relationship
     */

    public function category()
    {
        return $this->belongsTo('Modules\Product\Models\Category', 'category_id');
    }

    /**
     * Get list compare attributes by category id
     * @param $categoryId
     */
    public static function getListCompareAttributes($categoryId)
    {
        $attributes = CategoryAttribute::where('category_id', $categoryId)->where('compare_flg', 1)->get();

        if (!$attributes || $attributes->count() == 0) {
            // Try get attributes by parent category
            $category = Category::find($categoryId);

            if ($category && $category->parent_id > 0) {
                $attributes = self::getListCompareAttributes($category->parent_id);
            }
        }

        return $attributes;
    }
}
