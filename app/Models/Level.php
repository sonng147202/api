<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $table = 'levels';
    protected $fillable = [
        'name',
        'code',
        'approve',
        // 'commission_rate',
        // 'level_up_amount',
        // 'counterpart_commission_rate',
        'level'
    ];

    public function insuranceagencies()
    {
        return $this->hasMany('App\Models\InsuranceAgency','level_id');
    }
    public function products()
    {
        return $this->belongsToMany('Modules\Product\Models\Product');
    }
}
