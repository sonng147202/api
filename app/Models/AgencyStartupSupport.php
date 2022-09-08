<?php

namespace App\Models;
use App\Models\InsuranceAgency;
use Illuminate\Database\Eloquent\Model;

class AgencyStartupSupport extends Model
{
    protected $table = 'agency_startup_support';
    protected $fillable = [
        'insurance_agency_id',
        'month',
        'start_date',
        'end_date',
        'p_fyp',
        'accumulated_p_fyp',
        'cash_received',
        'status',
        'updated_at',
        'created_at'
    ];

    public static function searchIndex($params,$agencyId=NULL) {
        $p = AgencyStartupSupport::select("agency_startup_support.*","i.code_agency","i.name","i.level_id",'i.appoint_date')
        ->join('insurance_agencies as i', 'i.id', 'agency_startup_support.insurance_agency_id')
        ->where('appoint_date','<=','2022-01-31');
        if (!empty($agencyId)) {
            $p = $p->where('insurance_agency_id', $agencyId);
        }
        if (!empty($params["name"])) {
            $p = $p->where('name', 'like', '%'.$params["name"].'%');
        }
        if (!empty($params["code_agency"])) {
            $p = $p->where('code_agency', '=', $params["code_agency"]);
        }
        if(!empty($params['level'])){
            $p = $p->where('i.level_id', $params['level']);
        }
        if (!empty($params["month"])) {
            $p = $p->where('agency_startup_support.month', '=', $params["month"]);
        }
        if (!empty($params["start_date"])) {
            $p = $p->where('agency_startup_support.start_date', '=', $params["start_date"]);
        }
        
        return $p->orderBy('appoint_date','desc')->orderBy('level_id', 'desc')->orderBy('id')->paginate(50);
    }
    public static function searchExcelIndex($params,$agencyId=NULL) {
        $p = AgencyStartupSupport::select("agency_startup_support.*","i.code_agency","i.name","i.level_id",'i.appoint_date')
        ->join('insurance_agencies as i', 'i.id', 'agency_startup_support.insurance_agency_id')
        ->where('appoint_date','<=','2022-01-31');
        if (!empty($agencyId)) {
            $p = $p->where('insurance_agency_id', $agencyId);
        }
        if (!empty($params["name"])) {
            $p = $p->where('name', 'like', '%'.$params["name"].'%');
        }
        if (!empty($params["code_agency"])) {
            $p = $p->where('code_agency', '=', $params["code_agency"]);
        }
        if(!empty($params['level'])){
            $p = $p->where('i.level_id', $params['level']);
        }
        if (!empty($params["month"])) {
            $p = $p->where('agency_startup_support.month', '=', $params["month"]);
        }
        if (!empty($params["start_date"])) {
            $p = $p->where('agency_startup_support.start_date', '=', $params["start_date"]);
        }
        return $p->orderBy('level_id', 'desc')->get();
    }
}