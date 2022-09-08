<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class CustomerWalletExchange extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'customer_wallet_exchange';
    protected $fillable = ['id_customer', 'value', 'note', 'balance_after_payment', 'status', 'type', 'formality', 'recharge_content'];
    
    /**
     * Search by condition
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'id_customer');
    }
}
