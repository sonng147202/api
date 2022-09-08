<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $table    = 'insurance_companies';

    protected $fillable = ['name', 'email', 'address', 'phone_number'];
    protected $guarded  = [];

    /**
     * @param array $filter
     * @param $page
     * @param int $page_size
     */
    public static function getList($filter = [], $page, $page_size = 10)
    {
        $query = self::offset(($page - 1) * $page_size);

        // Check filter
        if (isset($filter['search_key']) && !empty($filter['search_key'])) {
            $query->where('name', 'like', '%' . $filter['search_key'] . '%');
        }

        return $query->paginate($page_size, ['*'], 'page', $page);
    }

    /**
     * Get detail company info
     * @param $id
     * @return mixed
     */
    public static function getDetail($id)
    {
        return self::find($id);
    }

    /**
     * @param $id
     * @param $data
     * @return mixed
     */
    public static function updateById($id, $data)
    {
        return self::where('id', $id)->update($data);
    }

    /**
     * @param $id
     * @return bool
     */
    public static function deleteById($id)
    {
        return self::trashed();
    }
}
