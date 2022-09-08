<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarBrand extends Model
{
    protected $connection = 'mysql_ftc';

    public static function getAllBrand()
    {
        return self::all();
    }

    public static function getListBrand()
    {
        return self::get()->pluck('name', 'id')->toArray();
    }
}
