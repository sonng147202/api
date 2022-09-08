<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyCompany extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'note', 'created_id', 'created_at', 'updated_id', 'updated_at', 'deleted_id', 'deleted_at'
    ];
    
    /**
     * Search by condition
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function searchByCondition($params) {
        $p = AgencyCompany::select("*");
        if (!empty($params["name"])) {
            $p = $p->where('name', 'like', '%'.$params["name"].'%');
        }
        if (!empty($params["email"])) {
            $p = $p->where('email', 'like', '%'.$params["email"].'%');
        }
        
        return $p->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];


    /**
     * @param $id
     * @return Model|null|static
     */
    public static function getDetail($id)
    {
        return self::where('id', $id)->first();
    }

}
