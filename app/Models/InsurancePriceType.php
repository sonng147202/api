<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InsurancePriceType extends Model
{
    protected $guarded = [];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = -1;

    const USE_TYPE_BY_ATTR_CONDITIONS = 0;
    const USE_TYPE_BY_PRODUCT = 1;

    public function getStatusName() {
        if ($this->status == InsurancePriceType::STATUS_ACTIVE)
            return "Đang kích hoạt";
        elseif ($this->status == InsurancePriceType::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    /**
     * @param $insuranceTypeId
     * @return array|bool
     */
    public static function getListWithCodeAsKey($insuranceTypeId)
    {
        $cacheKey = 'insurance_price_types_list_code_key_' . $insuranceTypeId;

        $types = Cache::tags('insurance_price_types_' . $insuranceTypeId)->remember($cacheKey, config('insurance.default_cache_time', 60), function () use ($insuranceTypeId) {
            $types = self::where('insurance_type_id', $insuranceTypeId)->get()->toArray();

            if ($types) {
                $tmpData = [];
                foreach ($types as $type) {
                    $tmpData[$type['code']] = $type;
                }

                return $tmpData;
            }

            return false;
        });

        return $types;
    }

    public static function unsetPrimary($insuranceTypeId) {
        self::where([
            'insurance_type_id' => $insuranceTypeId,
            'is_primary' => true
        ])->update(['is_primary' => false]);
    }

    /**
     * @param $insuranceTypeId
     * @param int $useType
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getListPriceTypesByTypeId($insuranceTypeId, $useType = 0)
    {
        $cacheKey = 'insurance_price_type_' . $insuranceTypeId . '_use_type_' . $useType;

        $list = Cache::tags('insurance_price_type')->remember($cacheKey, config('insurance.default_cache_time', 60), function () use ($insuranceTypeId, $useType) {
            $query = self::where([
                'insurance_type_id' => $insuranceTypeId,
                'status' => self::STATUS_ACTIVE
            ]);

            if (is_numeric($useType)) {
                $query->where('use_type', $useType);
            }

            return $query->get();
        });

        return $list;
    }

    /**
     * @param $insuranceTypeIds
     * @param int $useType
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getListPriceTypesByTypeIds($insuranceTypeIds, $useType = 0) {
        return self::where('status', self::STATUS_ACTIVE)
            ->where('use_type', $useType)
            ->whereIn('insurance_type_id', $insuranceTypeIds)->get();
    }

    /**
     * Check price type name exist
     *
     * @param $name
     * @param $insuranceType
     * @param bool $isUpdate
     */
    public static function checkName($name, $insuranceType, $isUpdate = false)
    {

    }

    public function clearCache()
    {
        Cache::tags('insurance_price_types_' . $this->insurance_type_id)->flush();

        return true;
    }
}
