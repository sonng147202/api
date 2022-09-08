<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceType extends Model
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

    public static function getTypeNameById($id) {
        $type = self::select('name')
            ->where('id', $id)
            ->first();

        if ($type) {
            return $type->name;
        }

        return '';
    }
    
    /**
     * Get list type
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getListType() {
        return self::select('id', 'name')
            ->where('status', InsuranceType::STATUS_ACTIVE)
            ->get();
    }

    /**
     * The relationship
     */
}
