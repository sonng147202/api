<?php

namespace App\Models;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
class RevenueFormula extends Model
{
    protected $table = 'revenue_formulas';
    protected $fillable = [
        'level','type', 'value','system_level'
    ];
}