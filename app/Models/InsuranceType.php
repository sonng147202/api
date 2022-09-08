<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InsuranceType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'vat', 'addition_contract_attributes', 'main_fee_name', 'fee_interval_type',
        'fee_interval_default_value', 'apply_fee_type', 'avatar'
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

    const FEE_INTERVAL_TYPE = ['days' => 'Theo ngày', 'years' => 'Theo năm'];
    const APPLY_FEE_TYPE = [0 => 'Theo sản phẩm trên hợp đồng', 1 => 'Theo đối tượng hưởng bảo hiểm'];
    const APPLY_FEE_TYPE_CONTRACT = 0;
    const APPLY_FEE_TYPE_BENEFICIARY = 1;

    public function getStatusName() {
        if ($this->status == InsuranceType::STATUS_ACTIVE)
            return "Đang kích hoạt";
        elseif ($this->status == InsuranceType::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    /**
     * Get list active insurance type
     */
    public static function getActiveList()
    {
        return self::where('status', self::STATUS_ACTIVE)->orderBy('name', 'DESC')->get();
    }

    /**
     * Get detail insurance type
     *
     * @param $id
     * @return Model|null|static
     */
    public static function getDetail($id)
    {
        $cacheKey = 'insurance_type_' . $id;

        $insuranceType = Cache::tags('insurance_type')->remember($cacheKey, config('insurance.default_cache_time', 60), function () use ($id) {
            return self::where('id', $id)->first();
        });

        return $insuranceType;
    }

    /**
     * Get list insurance type by list id
     *
     * @param $ids
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getByListId($ids)
    {
        return self::whereIn('id', $ids)->get();
    }

    public static function getTypeNameById($id) {
        $type = self::select('name')
            ->where('id', $id)
            ->first();

        if ($type) {
            return $type->name;
        }

        return '';
    }

    public static function getListType() {
        return self::select('id', 'name')
            ->where('status', InsuranceType::STATUS_ACTIVE)
            ->get();
    }
    
    /**
     * Revenue
     * @param $start
     * @param $end
     * @return array
     */
    public static function revenue($start, $end, $agencyIdArray)
    {
        $result = array(
            'total'=>0,
            'data'=>array()
        );
        $types = self::select('id', 'name')
            ->where('status', InsuranceType::STATUS_ACTIVE)
            ->get();

        foreach ($types as $row) {
            $query = InsuranceContract::whereBetween('created_at', [$start, $end])->where('status', 1);
            if (!empty($agencyIdArray)) {//filter by agency_id
                $query->whereIn('sale_type_id', $agencyIdArray);
            }
            $revenue = $query->where('type_id', $row->id)
                ->sum('require_pay_amount');
            $result['data'][] = array(
                'id'=>$row->id,
                'name'=>$row->name,
                'revenue'=>$revenue
            );
            $result['total'] += $revenue;
        }
    
    
        return $result;
    }
    
    /**
     * Report revenue by date
     * @param $start
     * @param $end
     */
    public static function report($start, $end, $agencyIdArray = [])
    {
        $result = array();
        $revenue = self::revenue($start, $end, $agencyIdArray);
        foreach ($revenue['data'] as $row) {
            $result[] = array(
                'label'=>str_replace('&', ' ', $row['name']),
                'data'=>$row['revenue']
            );
        }
        return $result;
    }
}
