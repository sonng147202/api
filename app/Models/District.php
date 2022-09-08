<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'province_id', 'name', 'name_without_accent'
    ];

    public function province()
    {
        return $this->belongsTo('App\Models\Province');
    }

    public function wards()
    {
        return $this->hasMany('App\Models\Ward');
    }
}
