<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CategoryClass extends Model
{
    protected $table = 'mp_category_classes';
    protected $fillable = ['category_id', 'code', 'name', 'status', 'order_number'];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = -1;

    public function getStatusName() {
        if ($this->status == CategoryClass::STATUS_ACTIVE)
            return "Đang kích hoạt";
        elseif ($this->status == CategoryClass::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    /**
     * belongs to category
     */
    public function category()
    {
        return $this->belongsTo('Modules\Product\Models\Category');
    }

    /**
     * @param $params
     * @return bool
     */
    public static function createClass($params)
    {
        if (isset($params['category_id']) && isset($params['order_number'])) {
            if ($class = self::create($params)) {
                // Check order_number
                $item = self::where('category_id', $params['category_id'])->where('order_number', $params['order_number'])->first();

                if ($item) {
                    // Swap order_number
                    $item->order_number = self::getTotalActive($params['category_id']);
                    $item->save();
                }

                return $class;
            }
        }

        return false;
    }

    /**
     * @param $params
     * @return bool
     */
    public static function updateClass($id, $params)
    {
        if (isset($params['category_id']) && isset($params['order_number'])) {
            try {
                // Get old data
                $class = self::where('id', $id)->first();
                $oldOrderNumber = $class->order_number;

                // Update data
                self::where('id', $id)->update($params);

                if (isset($params['order_number']) && $params['order_number'] != $oldOrderNumber) {
                    // Check order_number
                    $item = self::where('category_id', $params['category_id'])->where('id', '<>', $id)
                        ->where('order_number', $params['order_number'])->first();

                    if ($item) {
                        // Swap order_number
                        $item->order_number = $oldOrderNumber;
                        $item->save();
                    }
                }

                return true;
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return false;
    }

    /**
     * @param $categoryId
     * @return bool
     */
    public static function updateOrderNumber($categoryId)
    {
        try {
            // Get all active class
            $classes = self::where('status', self::STATUS_ACTIVE)->where('category_id', $categoryId)
                ->orderBy('order_number')->get();

            if ($classes) {
                $index = 1;
                foreach ($classes as $class) {
                    if ($class->order_number != $index) {
                        // Update order_number
                        $class->order_number = $index;
                        $class->save();
                    }
                    $index++;
                }
            }

            return true;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());

            return false;
        }
    }

    /**
     * Get list class by category
     *
     * @param $categoryId
     * @return bool
     */
    public static function getListClassByCategory($categoryId)
    {
        $classes = self::where('category_id', $categoryId)->get();

        if ($classes->count() == 0) {
            // Get by parent
            $category = Category::find($categoryId);

            if ($category && !empty($category->parent_id)) {
                $classes = self::getListClassByCategory($category->parent_id);
            }
        }

        return $classes;
    }

    public static function getListClassByCategoryIds($categoryIds)
    {
        $classes = self::whereIn('category_id', $categoryIds)
            ->where('status', CategoryClass::STATUS_ACTIVE)
            ->get();
        return $classes;
    }

    public static function getTotalActive($categoryId)
    {
        return self::where('category_id', $categoryId)->where('status', self::STATUS_ACTIVE)->count();
    }
}
