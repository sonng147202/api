<?php

namespace App\Models;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class RevenueDetail extends Model
{
    protected $table = 'revenue_details';
    protected $fillable = [
        'revenue_id','agency_income','level_id','branch_revenue'
    ];
    public function insurance_agency ()
    {
        return $this->belongsTo('App\Models\Revenue','revenue_id');
    }
}
