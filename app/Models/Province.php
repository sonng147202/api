<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'name_without_accent'
    ];

    public function districts()
    {
        return $this->hasMany('App\Models\District');
    }
}
