<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $table = 'mp_categories';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'insurance_type_id', 'name', 'parent_id', 'description', 'avatar'
    ];

    protected $dates = ['deleted_at'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = -1;

    public function getStatusName() {
        if ($this->status == Category::STATUS_ACTIVE)
            return "Đang kích hoạt";
        elseif ($this->status == Category::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    public function getParentCategoryNameById() {
        $category = self::select('name')
            ->where('id', $this->parent_id)
            ->first();

        if ($category) {
            return $category->name;
        }

        return '';
    }

    public static function getListCategory() {
        return self::select('id', 'name')
            ->where('status', Category::STATUS_ACTIVE)
            ->get();
    }

    public static function getCategoryNameById($id) {
        $category = self::select('name')
            ->where('id', $id)
            ->first();

        if ($category) {
            return $category->name;
        }

        return '';
    }

    /**
     * @param int $maxLevel
     * @return array|bool|string
     */
    public static function getNestedListCategory($maxLevel = 3)
    {
        $query = self::whereNull('parent_id')->where('status', self::STATUS_ACTIVE);

        // Get multi children
        $tmp = 'children';
        for ($i = 1; $i <= $maxLevel; $i++) {
            $query->with($tmp);
            $tmp .= '.children';
        }
        $categories = $query->get();

        if ($categories) {
            $tmp = [];

            foreach ($categories as $category) {
                $category->prefix = '';
                $tmp[] = $category;

                $childArray = self::_getChildCategory($category, $category->prefix);

                if ($childArray) {
                    foreach ($childArray as $item) {
                        $tmp[] = $item;
                    }
                }
            }

            return $tmp;
        }

        return false;
    }

    protected static function _getChildCategory($category, $prefix = '')
    {
        $tmp = [];
        if ($category->children) {
            foreach ($category->children as $child) {
                if ($child->status == self::STATUS_ACTIVE) {
                    $child->prefix = $prefix . '----';
                    $tmp[] = $child;

                    $childArray = self::_getChildCategory($child, $child->prefix);

                    if ($childArray) {
                        foreach ($childArray as $item) {
                            $tmp[] = $item;
                        }
                    }
                }
            }
        }

        return $tmp;
    }

    /**
     * @param $insuranceTypeId
     */
    public static function getByInsurance($insuranceTypeId)
    {
        return self::where('insurance_type_id', $insuranceTypeId)->whereNull('parent_id')->first();
    }

    /**
     * Get list child categories
     * @param $parentId
     */
    public static function getListChild($parentId)
    {
        return self::where('parent_id', $parentId)->select(['id', 'name', 'parent_id', 'insurance_type_id'])->get();
    }

    /**
     * The relationship
     */
    public function insurance_type()
    {
        return $this->belongsTo('App\Models\InsuranceType', 'insurance_type_id');
    }

    public function parent_category()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\Category', 'parent_id');
    }

    public function attributes()
    {
        return $this->hasMany('App\Models\CategoryAttribute', 'category_id');
    }
}
