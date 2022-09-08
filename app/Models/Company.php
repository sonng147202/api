<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $table    = 'insurance_companies';

    protected $guarded  = [];

    public function parentCompany()
    {
        return $this->belongsTo(self::class,'parent_id');
    }

    public function childCompany()
    {
        return $this->hasMany(self::class,'id');
    }

    /**
     * @param array $filter
     * @param $page
     * @param int $page_size
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($filter = [], $page, $page_size = 10)
    {
        $query = self::offset(($page - 1) * $page_size)->with('parentCompany')->orderBy('created_at', 'desc');

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
        return self::with('parentCompany')->find($id);
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
        return self::where('id', $id)->delete();
    }
}
