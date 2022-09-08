<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'code'
    ];

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
        if ($this->status == CustomerType::STATUS_ACTIVE)
            return "Đang kích hoạt";
        elseif ($this->status == CustomerType::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    /**
     * @param $id
     * @return Model|null|static
     */
    public static function getDetail($id)
    {
        return self::where('id', $id)->first();
    }

    /**
     * Get list active
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getListActive()
    {
        return self::where('status', self::STATUS_ACTIVE)->get();
    }

    public function customers()
    {
        return $this->hasMany('App\Models\Customer', 'type_id');
    }
}
