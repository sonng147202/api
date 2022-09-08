<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InsuranceFormula extends Model
{
    protected $fillable = ['insurance_type_id', 'code', 'name', 'formula', 'is_default'];

    public function insurance_type()
    {
        return $this->belongsTo('App\Models\InsuranceType');
    }

    public static function unsetDefault($insuranceTypeId) {
        self::where([
            'insurance_type_id' => $insuranceTypeId,
            'is_default' => true
        ])->update(['is_default' => false]);
    }

    /**
     * @param $insuranceTypeId
     */
    public static function getDefaultFormulaByType($insuranceTypeId)
    {
        $cacheKey = 'default_formula_by_type_' . $insuranceTypeId;

        $formula = Cache::remember($cacheKey, config('insurance.default_cache_time', 60), function () use($insuranceTypeId) {
            return self::where('is_default', 1)->where('insurance_type_id', $insuranceTypeId)->first();
        });
        return $formula;
    }

    /**
     * Check formula is exist or not
     * @param $params
     * @param string $id
     */
    public static function isExist($params, $id = '')
    {
        if (isset($params['insurance_type_id'])) {
            $query = self::where('insurance_type_id', $params['insurance_type_id']);

            if (!empty($id)) {
                $query->where('id', '<>', $id);
            }

            if (isset($params['code'])) {
                $query->where('code', $params['code']);

                if ($query->count() > 0) {
                    return ['success' => false, 'field' => trans('insurance::general.formula_code')];
                }
            }

            if (isset($params['name'])) {
                $query->where('name', $params['name']);

                if ($query->count() > 0) {
                    return ['success' => false, 'field' => trans('insurance::general.formula_name')];
                }
            }

            if (isset($params['formula'])) {
                $query->where('formula', $params['formula']);

                if ($query->count() > 0) {
                    return ['success' => false, 'field' => trans('insurance::general.formula')];
                }
            }

            if ($query->count() > 0) {
                return false;
            }
        }

        return ['success' => true];
    }

    /**
     * @param $insuranceType
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getListByType($insuranceType)
    {
        return self::where('insurance_type_id', $insuranceType)->get();
    }

    /**
     * @param $id
     * @return Model|null|static
     */
    public static function getDetail($id)
    {
        $cacheKey = 'insurance_formula_' . $id;

        $formula = Cache::remember($cacheKey, config('insurance.default_cache_time', 60), function () use ($id) {
            return self::where('id', $id)->first();
        });
        return $formula;
    }

    public function clearCache()
    {
        // Clear default for insurance type
        Cache::forget('default_formula_by_type_' . $this->insurance_type_id);
        Cache::forget('insurance_formula_' . $this->id);
        return true;
    }
}
