<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Models\RevenueDaily;
use DB;
use App\Http\Controllers\ApiController;

class RevenueController extends ApiController
{
	// Doanh thu nhân thọ (Chu kỳ hiện tại)
	public function getRevenueLife(Request $request)
    {   
    	$month = date('m/Y');
    	$cycle = date('d') < 16 ? 1 : 2;
    	$revenue_cycle = 'Tháng '. $month .' - Chu kỳ '. $cycle;
        try {
	        $params = $request->all();

	        if(empty($params['user_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }

	        $data = RevenueDaily::select(
	            "insurance_agencies.id as insurance_agency_id",
	            "insurance_agencies.code_agency as code_agency",
	            "insurance_agencies.name as name",
	            "levels.code as level",
	            "revenue_dailys.revenue_cycle",
	            "revenue_dailys.status",
	            // Tổng doanh thu nhân thọ = DT cá nhân + DT hệ thống
	            DB::raw("SUM(personal_revenue) + SUM(branch_revenue) as total_revenue_life"),
	            // DT cá nhân
	            DB::raw("SUM(personal_revenue) as total_personal_revenue"),
	            // Thu nhập cá nhân trước thuế
	            DB::raw("SUM(personal_income_before_tax) as total_personal_income_before_tax"),
	            // Thu nhập cá nhân sau thuế
	            DB::raw("SUM(personal_income_after_tax) as total_personal_income_after_tax"),
	            // Doanh thu hệ thống
	            DB::raw("SUM(branch_revenue) as total_branch_revenue"),
	            // Thu nhập hệ thống trước thuế
	            DB::raw("SUM(branch_income_before_tax) as total_branch_income_before_tax"),
	            // Thu nhập hệ thống sau thuế
	            DB::raw("SUM(branch_income_after_tax) as total_branch_income_after_tax")
	            // Doanh thu đồng cấp
	            // DB::raw("SUM(peer_revenue) as total_peer_revenue"),
	            // Thu nhập đồng cấp
	            // DB::raw("SUM(peer_income_before_tax) as total_peer_income_before_tax"),
	            // Tổng doanh thu
	            // DB::raw("SUM(personal_revenue + branch_revenue + peer_revenue) as total_revenue"),
	            // Tổng thu nhập
	            // DB::raw("SUM(personal_income_before_tax + branch_income_before_tax + peer_income_before_tax) as total_income_before_tax"),
	            // Tổng thu nhập sau thuế
	            // DB::raw("SUM(personal_income_after_tax + branch_income_after_tax + peer_income_after_tax) as total_income_after_tax")
	        )
	        ->leftjoin('insurance_agencies', 'insurance_agencies.id', 'revenue_dailys.insurance_agency_id')
	        ->leftjoin('levels', 'levels.level', 'revenue_dailys.level_id')
	        ->groupBy("insurance_agency_id")
	        ->groupBy("revenue_cycle")
	        ->where('revenue_dailys.is_life_insurance_contract', 1)
	        ->where('revenue_dailys.revenue_cycle', $revenue_cycle)
	        ->where('revenue_dailys.insurance_agency_id', $params['user_id'])
			->get()->toArray();
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }

        return $this->apiResponse($data, null, 1);
    }

    // Doanh phi thu nhân thọ (Tháng hiện tại)
    public function getRevenueNonLife(Request $request)
    {    
    	$month = date('m/Y');
    	$revenue_cycle = 'Tháng '. $month;
        try {
	        $params = $request->all();

	        if(empty($params['user_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }
               
	        $data = RevenueDaily::select(
	            "insurance_agencies.id as insurance_agency_id",
	            "insurance_agencies.code_agency as code_agency",
	            "insurance_agencies.name as name",
	            "levels.code as level",
	            "revenue_dailys.status",
	            "revenue_dailys.revenue_cycle",
	            DB::raw("SUM(personal_revenue) as total_personal_revenue"),
	            DB::raw("SUM(personal_income_before_tax) as personal_income_before_tax"),
	            DB::raw("SUM(personal_income_after_tax) as personal_income_after_tax")
	        )
	        ->leftjoin('insurance_agencies', 'insurance_agencies.id', 'revenue_dailys.insurance_agency_id')
	        ->leftjoin('levels', 'levels.level', 'revenue_dailys.level_id')
	        ->groupBy("insurance_agency_id")
	        ->groupBy("revenue_cycle")
	        ->where('revenue_dailys.is_life_insurance_contract', 2)
	        ->where('revenue_dailys.revenue_cycle', $revenue_cycle)
	        ->where('revenue_dailys.insurance_agency_id', $params['user_id'])
	        ->get()->toArray();
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }

        return $this->apiResponse($data, null, 1);
    }
}
