<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Models\RevenueDaily;
use App\Models\InsuranceContract;
use App\Models\ConsultingInformation;
use App\Models\InsuranceAgency;
use App\Models\Approve;
use App\Models\Level;
use App\Models\Image;
use App\Models\Video;
use DB;
use App\Http\Controllers\ApiController;
use DateTime;

class DashboardController extends ApiController
{
    // API QL hệ thống
    public function getDashboard(Request $request)
    {   
        $month = date('m/Y');
        $cycle = date('d') < 16 ? 1 : 2;
        $revenue_cycle_life = 'Tháng '. $month .' - Chu kỳ '. $cycle;
        $revenue_cycle_non_life = 'Tháng '. $month;

        try {
            $params = $request->all();

            if(empty($params['user_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }

            $revenue_daily_life = RevenueDaily::select(
                "insurance_agencies.id as insurance_agency_id",
                "insurance_agencies.code_agency as code_agency",
                "insurance_agencies.name as name",
                "levels.code as level",
                "revenue_dailys.revenue_cycle",
                "revenue_dailys.status",
                // Tổng doanh thu nhân thọ = DT cá nhân + DT hệ thống
                DB::raw("SUM(personal_revenue) + SUM(branch_revenue) as total_revenue_life")
            )
            ->leftjoin('insurance_agencies', 'insurance_agencies.id', 'revenue_dailys.insurance_agency_id')
            ->leftjoin('levels', 'levels.level', 'revenue_dailys.level_id')
            ->groupBy("insurance_agency_id")
            ->groupBy("revenue_cycle")
            ->where('revenue_dailys.is_life_insurance_contract', 1)
            ->where('revenue_dailys.revenue_cycle', $revenue_cycle_life)
            ->where('revenue_dailys.insurance_agency_id', $params['user_id'])
            ->first();

            $revenue_daily_non_life = RevenueDaily::select(
                "insurance_agencies.id as insurance_agency_id",
                "insurance_agencies.code_agency as code_agency",
                "insurance_agencies.name as name",
                "levels.code as level",
                "revenue_dailys.status",
                "revenue_dailys.revenue_cycle",
                DB::raw("SUM(personal_revenue) as total_personal_revenue")
            )
            ->leftjoin('insurance_agencies', 'insurance_agencies.id', 'revenue_dailys.insurance_agency_id')
            ->leftjoin('levels', 'levels.level', 'revenue_dailys.level_id')
            ->groupBy("insurance_agency_id")
            ->groupBy("revenue_cycle")
            ->where('revenue_dailys.is_life_insurance_contract', 2)
            ->where('revenue_dailys.revenue_cycle', $revenue_cycle_non_life)
            ->where('revenue_dailys.insurance_agency_id', $params['user_id'])
            ->first();

            $total_revenue_life = $revenue_daily_life ? $revenue_daily_life->total_revenue_life : '0';
            $total_personal_revenue = $revenue_daily_non_life ? $revenue_daily_non_life->total_personal_revenue : '0';

            $data = [
                'total_revenue_life' => $total_revenue_life,
                'total_revenue_non_life' => $total_personal_revenue
            ];
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }

        return $this->apiResponse($data, null, 1);
    }

    public function getHome(Request $request)
    {
        try {
            $images = Image::latest();
            $videos = Video::latest();

            $data['images'] = $images->take(4)->get()->toArray();
            $data['videos'] = $videos->take(4)->get()->toArray();

            if (isset($request['keyword']))
            {
                $data['images'] = $images->where('name', 'like', '%'.$request['keyword'].'%')
                ->take(4)
                ->get()
                ->toArray();
                
                $data['videos'] = $videos->where('title', 'like', '%'.$request['keyword'].'%')
                ->take(4)
                ->get()
                ->toArray();
            }
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }

        return $this->apiResponse($data, null, 1);
    }
}
