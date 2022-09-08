<?php

namespace App\Models;
use App\Models\InsuranceAgency;
use Illuminate\Database\Eloquent\Model;

class ApproveOffice extends Model
{
    protected $table = 'approves_office';
    protected $fillable = [
        'insurance_agency_id',
        'approver',
        'office_id',
        'office_old_id',
        'manger_status',
        'manager_time',
        'fad_time',
    ];
    
    public function insurance_agency()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'insurance_agency_id');
    }

}