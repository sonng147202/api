<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiaryType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status'
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
        if ($this->status == BeneficiaryType::STATUS_ACTIVE)
            return "Đang kích hoạt";
        elseif ($this->status == BeneficiaryType::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    /**
     * The relationship
     */
    public function attributes()
    {
        return $this->hasMany('App\Models\BeneficiaryTypeAttribute');
    }

    public static function getActiveList()
    {
        return self::where('status', self::STATUS_ACTIVE)->pluck('name', 'id')->toArray();
    }

}
