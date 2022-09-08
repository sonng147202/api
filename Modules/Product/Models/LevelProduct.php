<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class LevelProduct extends Model
{
    protected $table = 'level_product';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'level_id', 'product_id', 'commission_rate', 'counterpart_commission_rate'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    /**
     * The relationship
     */
    
}
