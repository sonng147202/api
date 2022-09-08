<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\User;

class MemberAdvisor extends Model
{
    protected $table = 'member_advisor';

    const CALLED = 1;
    const NOT_CALLED = 0;

    const TYPE_CUSTOMER = 0;
    const TYPE_AGENCY = 1;

    public function insuranceProductPriceTypes()
    {
        return $this->belongsTo('App\Models\InsuranceProductPriceTypes', 'product_id', 'product_id');
    }
    /**
     * Save phone to call
     */
    public static function savePhoneToCall($params)
    {
        $model = new MemberAdvisor();
        $model->phone = $params['phone'];
        $model->product_id = $params['product_id'];
        $model->filter_data = $params['filter_data'];
        $model->member_id = !empty($params['member_id']) ? $params['member_id'] : '';
        $model->member_type = !empty($params['member_type']) ? $params['member_type'] : '';
        $model->save();
        return $model;
    }

    /**
     * Get list
     */
    public static function getList($page, $page_size, $filter)
    {
        $query = self::with('insuranceProductPriceTypes:product_id,insurance_type_id');
        if (isset($filter['is_called'])) {
            $query->where('is_called', $filter['is_called']);
        }
        if (isset($filter['start_date'])) {
            $query->whereDate('created_at', '>=', $filter['start_date']);
        }
        if (isset($filter['end_date'])) {
            $query->whereDate('created_at', '<=', $filter['end_date']);
        }
        $query->orderBy('created_at', 'desc');
        return $query->paginate($page_size, ['*'], 'page', $page);
    }

    /**
     * Change status
     */
    public static function changeStatus($params)
    {
        $user = Auth::user();
        $model = MemberAdvisor::with('insuranceProductPriceTypes:product_id,insurance_type_id')->find($params['id']);
        if (!empty($model)) {
            $model->called_id = $user->id;
            $model->called_date = date('Y-m-d H:i:s');
            $model->called_comment = $params['called_comment'];
            $model->updated_at = date('Y-m-d H:i:s');
            $model->is_called = self::CALLED;//called
            $model->save();
        }
        return $model;
    }

    /**
     * Get detail
     */
    public static function getDetail($id)
    {
        $data = self::find($id);
        return $data;
    }

    /**
     * Get user info
     */
    public static function getUserInfo($type, $id)
    {
        if ($type == self::TYPE_CUSTOMER) {
            $model = new Customer();
        } else {
            $model = new InsuranceAgency();
        }
        $data = $model::select('name')->find($id);
        $name = '';
        if (!empty($data)) {
            $name = $data->name;
        }
        return $name;
    }

    /**
     * Get user who call
     */
    public static function getUserCall($id)
    {
        $user = User::select('username')->find($id);
        $name = '';
        if (!empty($user)) {
            $name = $user->username;
        }
        return $name;
    }
}
