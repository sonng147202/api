<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLevelCommission extends Model
{
    protected $table = 'mp_product_level_commissions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'commission_id', 'commission_amount'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    /**
     * The relationship
     */
    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product');
    }
}
