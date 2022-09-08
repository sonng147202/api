<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyContactApp extends Model
{
    protected $table = 'agency_contact';
    protected $fillable = [
        'id', 'agency_id', 'contact'
    ];

    public function insurance_agencies()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'agency_id');
    }

}
