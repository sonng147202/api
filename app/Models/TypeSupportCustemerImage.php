<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;


class TypeSupportCustemerImage extends Model
{
    protected $table = 'type_support_custemer_image';
    protected $fillable = [
        'customer_id','type_support_custemer_id', 'note','image','contract_id','date_time','place'
    ];

    public static function getList($page, $page_size, $filter)
    {
        $query = self::orderBy('created_at', 'desc');
        // if (isset($filter['is_called'])) {
        //     $query->where('is_called', $filter['is_called']);
        // }
        // if (isset($filter['start_date'])) {
        //     $query->whereDate('created_at', '>=', $filter['start_date']);
        // }
        // if (isset($filter['end_date'])) {
        //     $query->whereDate('created_at', '<=', $filter['end_date']);
        // }
        // $query->orderBy('created_at', 'desc');
        return $query->paginate($page_size, ['*'], 'page', $page);
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id');
    }

    public function type_support_custemer()
    {
        return $this->belongsTo('App\Models\TypeSupportCustemer', 'type_support_custemer_id');
    }
}
