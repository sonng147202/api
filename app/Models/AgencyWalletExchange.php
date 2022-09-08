<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InsuranceAgency;

class AgencyWalletExchange extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'agency_wallet_exchange';
    protected $fillable = ['id_agencies', 'value', 'note', 'balance_after_payment', 'status', 'type', 'formality', 'recharge_content', 'vnp_TransactionNo'];
    
    /**
     * Search by condition
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function insurance_agencies()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'id_agencies');
    }

    public static function searchWalletExchangeReport($params) {
        $p = AgencyWalletExchange::where('status', 1)->orderBy('id', 'desc');
        if (!empty($params["id_agencies"])) {
            $p = $p->where('id_agencies', $params["id_agencies"])->orderBy('id', 'desc');
        }
        if (!empty($params["start_date"])) {
            $start_date = date("Y-m-d", strtotime(str_replace('/', '-', $params["start_date"])) );
            $p = $p->where('created_at', '>=', $start_date);
        }
        if (!empty($params["end_date"])) {
            $end_date = date("Y-m-d", strtotime(str_replace('/', '-', $params["end_date"])) );
            $p = $p->where('created_at', '<=', $end_date);
        }

        return $p;
    }
}
