<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiaryTypeAttribute extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'is_required', 'default_value','data_type', 'beneficiary_type_id','code','order_number'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @param $typeId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getListByType($typeId)
    {
        return self::where('beneficiary_type_id', $typeId)->orderBy('order_number')->get();
    }
}
