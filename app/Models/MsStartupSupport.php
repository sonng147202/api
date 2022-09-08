<?php

namespace App\Models;
use App\Models\InsuranceAgency;
use Illuminate\Database\Eloquent\Model;

class MsStartupSupport extends Model
{
    protected $table = 'ms_startup_support';
    protected $fillable = [
        'level',
        'month',
        'targets_p_fyp',
        'accumulated_p_fyp',
        'cash_support',
        'month_name'
    ];
}