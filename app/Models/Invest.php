<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'invests';
    protected $fillable = [
        'name','birthday','sex','address','choose_invest','monney_invest'
    ];
}
