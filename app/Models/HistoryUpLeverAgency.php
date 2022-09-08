<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InsuranceAgency;
use App\Models\User;

class HistoryUpLeverAgency extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'history_up_level_agency';
    protected $fillable = ['insurance_agency_id', 'date_start','create_by', 'lever_old','lever_now'];
    
    /**
     * Search by condition
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function insurance_agencies()
    {
        return $this->belongsTo('App\Models\InsuranceAgency', 'insurance_agency_id');
    }
    public function users()
    {
        return $this->belongsTo('App\Models\User', 'create_by');
    }
}
