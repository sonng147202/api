<?php

namespace App\Models;
use App\Models\InsuranceAgency;
use Illuminate\Database\Eloquent\Model;

class AgencyInfoFwd extends Model
{
    protected $table = 'agency_info_fwd';
    protected $fillable = [
        'name_agency_official',
        'phone',
        'code_agency_official',
        'code_FAD',
        'id_card_number'
    ];
}

