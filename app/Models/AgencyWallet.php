<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InsuranceAgency;

class AgencyWallet extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'agency_wallet';
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
}
