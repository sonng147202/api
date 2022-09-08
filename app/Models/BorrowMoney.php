<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowMoney extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'borrow_money';
    protected $fillable = [
        'name','birthday','sex','address','work_now','number_brrow','number_brrow'
    ];
}
