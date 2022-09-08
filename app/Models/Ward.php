<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'district_id', 'name', 'name_without_accent'
    ];

    public function district()
    {
        return $this->belongsTo('App\Models\District');
    }
}
