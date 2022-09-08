<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModelTrim extends Model
{
    protected $connection = 'mysql_ftc';

    /**
     * @param $modelId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getModelYears($modelId)
    {
        return self::where('model_id', $modelId)->select(['year'])
            ->groupBy('year')->orderBy('year', 'DESC')->get();
    }

    /**
     * @param $modelId
     * @param $year
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getListTrim($modelId, $year)
    {
        return self::where('model_id', $modelId)->where('year', $year)
                ->select(['id', 'code', 'name', 'year'])->get();
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public static function getName($id)
    {
        $item = self::where('id', $id)->select('name')->first();
        if ($item) {
            return $item->name;
        } else {
            return false;
        }
    }
}
