<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $connection = 'mysql_ftc';

    /**
     * @param $brandId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getByBrand($brandId)
    {
        return self::where('brand_id', $brandId)->get();
    }

    public static function getName($id)
    {
        $carModel = self::where('id', $id)->select('name')->first();

        return $carModel->name;
    }
}
