<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardWallet extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'reward_wallet';
    protected $fillable = ['id_agencies', 'value', 'debt_permission', 'recharge_content'];

    /**
     * Search by condition
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function insurance_agencies()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'id_agencies');
    }


    public static function searchByCondition($params) {
        $p = InsuranceAgency::with( "rewardWallet" , "agencyWallet");

        if (!empty($params["name"])) {
            $p = $p->where('name', 'like', '%'.$params["name"].'%');
        }
        if (!empty($params["email"])) {
            $p = $p->orWhere('email', 'like', '%'.$params["email"].'%');
        }
        if (!empty($params["commission_id"])) {
            $p = $p->orWhere('commission_id', $params["commission_id"]);
        }
        if (!empty($params["manager_id"])) {
            $p = $p->orWhere('manager_id', $params["manager_id"]);
        }

        if(!empty($params['export']) && $params['export'] == 1){
            return $p->get();
        }

        return $p->orderBy('created_at', 'desc')->paginate(10);
    }
}
