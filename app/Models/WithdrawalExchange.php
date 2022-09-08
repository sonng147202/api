<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InsuranceAgency;

class WithdrawalExchange extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'withdrawal_exchange';
    protected $fillable = ['id_agencies', 'value', 'note', 'balance_after_payment', 'status', 'type', 'formality', 'recharge_content'];
    
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
