<?php

namespace App\Models;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    protected $table = 'revenues';
    protected $fillable = [
        'isurance_agency_id',
        'level_id',
        'revenue_cycle', 
        'total_income',
        'total_revenue', 
        'personal_income', 
        'personal_revenue',
        'total_income_after_tax',
        'branch_income_after_tax',
        'personal_income_same_code_after_tax',
        'personal_income_other_code_after_tax',
        'personal_revenue_not_add_upper_branch',
        'branch_revenue_not_add_upper_branch',
        'insurance_company_id',
        'is_life_insurance_contract'
    ];

    public function insurance_agency ()
    {
        return $this->belongsTo('App\Models\InsuranceAgency','isurance_agency_id');
    }

    public static function searchIndex($params, $agencyId = null)
    {
        $list = InsuranceAgency::select(
            'insurance_agencies.*',
            'revenues.id as revenues_id',
            'revenues.status as revenues_status',
            'revenues.revenue_cycle',
            'revenues.personal_income',
            'revenues.personal_revenue',
            'revenues.revenue_cycle',
            'revenues.total_income',
            'revenues.total_revenue',
            'revenues.total_income_after_tax',
            'revenues.branch_income_after_tax',
            'revenues.personal_income_same_code_after_tax',
            'revenues.personal_income_other_code_after_tax'
        )
            ->join('revenues', 'revenues.isurance_agency_id', 'insurance_agencies.id')
            ->where('revenues.total_income','!=',0)
            ->where('revenues.is_life_insurance_contract', 1);

        if (!empty($agencyId)) {
            $list = $list->where('insurance_agencies.id', $agencyId);
        }
        if (!empty($params["code_agency"])) {
            $list = $list->where('insurance_agencies.code_agency', $params["code_agency"]);
        }

        if (!empty($params["name"])) {
            $list = $list->where('insurance_agencies.name', 'like', '%'.$params["name"].'%');
        }

        if (!empty($params["level"])) {
            $list = $list->where('insurance_agencies.level_id', $params["level"]);
        }
        if (!empty($params["revenue_cycle"])) {
            $list = $list->whereIn('revenues.revenue_cycle', $params["revenue_cycle"]);
        }
        if (isset($params["revenue_status"])) {
            $list = $list->where('revenues.status', $params["revenue_status"]);
        }
        if (!empty($params["phone"])) {
            $list = $list->where('insurance_agencies.phone', $params["phone"]);
        }

        $list= $list->orderBy('revenue_cycle','desc')->orderBy('level_id','desc')->orderBy('code_agency','asc');
        return $list;
    }

    public static function getRevenueNonLife($params, $agencyId = null)
    {
        $list = InsuranceAgency::select(
            'insurance_agencies.*',
            'revenues.id as revenues_id',
            'revenues.status as revenues_status',
            'revenues.revenue_cycle',
            'revenues.personal_income',
            'revenues.personal_revenue',
            'revenues.revenue_cycle',
            'revenues.total_income',
            'revenues.total_revenue',
            'revenues.total_income_after_tax',
            'revenues.branch_income_after_tax',
            'revenues.personal_income_same_code_after_tax',
            'revenues.personal_income_other_code_after_tax'
        )
            ->join('revenues', 'revenues.isurance_agency_id', 'insurance_agencies.id')
            ->where('revenues.total_income','!=',0)
            ->where('revenues.is_life_insurance_contract', 0);

        if (!empty($agencyId)) {
            $list = $list->where('insurance_agencies.id', $agencyId);
        }
        if (!empty($params["code_agency"])) {
            $list = $list->where('insurance_agencies.code_agency', $params["code_agency"]);
        }

        if (!empty($params["name"])) {
            $list = $list->where('insurance_agencies.name', 'like', '%'.$params["name"].'%');
        }

        if (!empty($params["level"])) {
            $list = $list->where('insurance_agencies.level_id', $params["level"]);
        }
        if (!empty($params["revenue_cycle"])) {
            $list = $list->whereIn('revenues.revenue_cycle', $params["revenue_cycle"]);
        }
        if (isset($params["revenue_status"])) {
            $list = $list->where('revenues.status', $params["revenue_status"]);
        }
        if (!empty($params["phone"])) {
            $list = $list->where('insurance_agencies.phone', $params["phone"]);
        }

        $list= $list->orderBy('revenue_cycle','desc')->orderBy('level_id','desc')->orderBy('code_agency','asc');
        return $list;
    }

    public function revenueDetail ()
    {
        return $this->hasMany('App\Models\RevenueDetail');
    }
}
