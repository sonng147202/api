<?php

namespace Modules\Core\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Core\Lib\NewsService;
use Modules\Insurance\Models\Customer;
use Modules\Insurance\Models\InsuranceAgency;
use Modules\Insurance\Models\AgencyWallet;
use Modules\Insurance\Models\InsuranceContract;
use Carbon\Carbon;
use Modules\Insurance\Models\InsuranceQuotation;
use Modules\Insurance\Models\InsuranceType;
use Modules\Insurance\Models\RevenueContract;
use Modules\Insurance\Models\RevenueMonthly;
use Modules\Insurance\Models\RevenueDaily;
use Modules\Core\Models\NotificationsActive;
use DB;
use DateTime;

class DashboardController extends Controller
{
    /**
     * Check agency is company
     * If agency is company, get all data from this company's staff
     */
    public function checkAgencyIsCompany($agencyId)
    {
        $agencyInfo = InsuranceAgency::find($agencyId);
        if (!empty($agencyInfo)) {
            if ($agencyInfo->agency_company_is_manager == 1) {
                $arrayAgencyIdStaff = InsuranceAgency::select('id')
                    ->where('agency_company_id', $agencyInfo->agency_company_id)
                    ->get()
                    ->toArray();
                $arrayAgencyIdStaffConvert = [];
                if (!empty($arrayAgencyIdStaff)) {
                    foreach ($arrayAgencyIdStaff as $row) {
                        $arrayAgencyIdStaffConvert[] = $row['id'];
                    }
                }
                $arrayEmployee = $arrayAgencyIdStaffConvert;
                array_push($arrayAgencyIdStaffConvert, (int) $agencyId);
                $rs = [
                    'all' => $arrayAgencyIdStaffConvert,
                    'agency' => [(int) $agencyId],//agency
                    'agency_employee' => $arrayEmployee//employee of agency
                ];
                return $rs;
            } else {
                $arrayAgencyIdStaffConvert = [(int) $agencyId];
                $rs = [
                    'all' => $arrayAgencyIdStaffConvert,
                    'agency' => [(int) $agencyId],
                    'agency_employee' => []
                ];
                return $rs;
            }
        } else {
            return [];
        }
    }

    /**
     * Get data for agency dashboard
     */
    public function index(Request $request)
    {
        $params = $request->all();
        $agencyId = $params['agency_id'];
        $agencyEmployee = $this->checkAgencyIsCompany($agencyId);
        $agencyIdArray = $agencyEmployee['all'];
//        $child_agency_id_array = InsuranceAgency::find($agencyId)->child_agency;

        if (empty($agencyIdArray)) {
            return response()->json([]);
        }
        if ($request->has('start')) {
            $start = Carbon::parse($request->get('start'))->toDateString();
        } else {
            $start = Carbon::now()->startOfMonth();
        }
        if ($request->has('end')) {
            $end = Carbon::parse($request->get('end'))->addDay(1)->toDateString();
        } else {
            $end = Carbon::now()->endOfMonth();
        }
        $insuranceContractQuery = InsuranceContract::whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type_id', $agencyIdArray);
        
        // Đối tác
//        $total_new_partner = InsuranceAgency::whereBetween('created_at', [$start, $end])->whereIn('id', json_decode($child_agency_id_array))->count();
        // $partner_contract_total = InsuranceAgency::whereHas('insuranceContracts', function ($query) use ($start, $end) {
        //     $query->whereBetween('created_at', [$start, $end]);
        // })->whereIn('id', json_decode($child_agency_id_array))->count();

//        $partner_contract_total = InsuranceContract::whereIn('sale_type_id', json_decode($child_agency_id_array))->groupBy('sale_type_id')->whereBetween('created_at', [$start, $end])->get()->count();
        $level = InsuranceAgency::find($agencyId)->level->name;

        // // Doanh thu
        // $branch_revenue = InsuranceContract::whereBetween('created_at', [$start, $end])->whereIn('sale_type_id', json_decode($child_agency_id_array))->where([['sale_type', 2],['status', 1]])->sum('require_pay_amount');

        // $revenue_monthly = RevenueMonthly::where([['month', date_format(new DateTime($start), "m")], ['year', date_format(new DateTime($start), "Y")], ['insurance_agency_id', $agencyId]])->first();
        $revenue_monthly = RevenueDaily::select('id', 'insurance_agency_id', 'date', 'year', 'month', 'day', 
            DB::raw('
                SUM(self_revenue) AS self_revenue, 
                SUM(branch_revenue) AS branch_revenue, 
                SUM(self_income) AS self_income, 
                SUM(nolife_before_tax) AS nolife_before_tax,
                SUM(nolife_affter_tax) AS nolife_affter_tax,
                SUM(branch_income) AS branch_income,
                SUM(life_before_tax) AS life_before_tax,
                SUM(life_affter_tax) AS life_affter_tax
            '))
            ->where('insurance_agency_id', $agencyId)
            ->where('year', date_format(new DateTime($start), "Y"))
            ->where('month', date_format(new DateTime($start), "m"))
            ->groupBy('insurance_agency_id')
            ->first();

        if ($revenue_monthly != null) {
            $self_revenue = $revenue_monthly->self_revenue;
            $branch_revenue = $revenue_monthly->branch_revenue;
            $total_revenue_monthly = (int)$self_revenue + (int)$branch_revenue;
//            $self_income = (int)$revenue_monthly->self_income;
//            $branch_income = $revenue_monthly->branch_income;
//            $total_income = (int)$self_income + (int)$branch_income;
        } else {
            $self_revenue = 0;
            $branch_revenue = 0;
            $total_revenue_monthly = 0;
//            $self_income = 0;
//            $branch_income = 0;
//            $total_income = 0;
        }
        $self_income =  0 ;
        $self_income_tb = RevenueContract::select(
            DB::raw('
                SUM(self_income) AS self_income
          
            '))
            ->where('child_agency_id', 0)
            ->where('status', 1)
            ->where('month', date_format(new DateTime($start), "m"))
            ->where('year', date_format(new DateTime($start), "Y"))
            ->where('insurance_agency_id',$agencyId)
            ->groupBy('insurance_agency_id')
            ->first();
        if($self_income_tb){
            $self_income = $self_income_tb['self_income'];
        }
        $branch_income = 0;
        $branch_income_tb = RevenueContract::select(
            DB::raw('
                SUM(branch_income) AS branch_income
            '))
            ->where('child_agency_id', $agencyId)
            ->where('status', 1)
            ->where('month', date_format(new DateTime($start), "m"))
            ->where('year', date_format(new DateTime($start), "Y"))
            ->groupBy('insurance_agency_id')
            ->first();
        if($branch_income_tb){
            $branch_income = $branch_income_tb['branch_income'];
        }



        // Doanh thu hang thang cua doi tac
        // $revenue_each_month = RevenueMonthly::select('month','branch_revenue')->where([['insurance_agency_id', $agencyId],['year',date("Y")]])->orderBy('month','asc')->get();
        $revenue_each_month = RevenueDaily::select('id', 'insurance_agency_id', 'date', 'year', 'month', 'day', 
            DB::raw('
                SUM(self_revenue) AS self_revenue, 
                SUM(branch_revenue) AS branch_revenue, 
                SUM(self_income) AS self_income, 
                SUM(nolife_before_tax) AS nolife_before_tax,
                SUM(nolife_affter_tax) AS nolife_affter_tax,
                SUM(branch_income) AS branch_income,
                SUM(life_before_tax) AS life_before_tax,
                SUM(life_affter_tax) AS life_affter_tax
            '))
            ->where('insurance_agency_id', $agencyId)
            ->where('year', date("Y"))
            ->groupBy('insurance_agency_id', 'month')
            ->orderBy('month','asc')
            ->get();

        $month_number =  (int)date("m");
        $data_month = [];
        // count($revenue_each_month);
        foreach ($revenue_each_month as $k=> $item){
            for($i=0 ; $i< $month_number ;$i++){
                if($i+1 == (int)$item['month'] ){
                    $data_month[$i+1] = $item['branch_revenue'];
                }else{
                    if(!isset( $data_month[$i+1])) {
                        $data_month[$i+1] = 0;
                    }
                }
            }
        }
        $data_month = array_values($data_month);

        // thong bao
        $notifications = NotificationsActive::whereBetween('schedule', [$start, $end])->orderBy('schedule', 'DESC')->limit(10)->get();
        // Block Khách hàng
        $customerQuery = Customer::whereBetween('created_at', [$start, $end])
            ->whereIn('agency_id', $agencyIdArray);
        $total_customer = $customerQuery->count();
        $kh_tiem_nang = $customerQuery->where('classify', 1)->count();
        $kh_co_hoi = $customerQuery->where('classify', 2)->count();
        $kh_mua_hang = $insuranceContractQuery->distinct('customer_id')->count();
        $kh_tai_tuc = $customerQuery->where('classify', 3)->count();
        
        // Block tuong tac
        $total_quotations = InsuranceQuotation::whereBetween('created_at', [$start, $end])
            ->whereIn('agency_id', $agencyIdArray)->count();

        // Block Doanh thu
        $contract_sale = InsuranceContract::whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type_id', $agencyIdArray)
            ->sum('require_pay_amount');
        //$contract_agence = $insuranceContractQuery->where('sale_type_id', '>', 0)->sum('require_pay_amount');
        $contract_agence = InsuranceContract::whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type_id', $agencyIdArray)
            ->sum('require_pay_amount');
        $tong_doanh_thu_hieuluc = InsuranceContract::whereBetween('created_at', [$start, $end])
            ->where('certificate_active', 1)
            ->whereIn('sale_type_id', $agencyIdArray)
            ->sum('require_pay_amount');
        $tong_doanh_thu_khonghieuluc = InsuranceContract::whereBetween('created_at', [$start, $end])
            ->where('certificate_active', '!=', 1)
            ->whereIn('sale_type_id', $agencyIdArray)
            ->sum('require_pay_amount');

        // top đối tác có doanh số lớn nhất
        // $top_revenue_partner = RevenueMonthly::with('insurance_agency')->where([['month', date('m')], ['year', date('Y')]])->select('*', DB::raw('self_revenue+branch_revenue as self_revenue'))->groupBy('insurance_agency_id')->orderBy('self_revenue', 'DESC')->limit(12)->get();
        $top_revenue_partner = RevenueDaily::select('revenue_dailys.id', 'insurance_agency_id', 'date', 'year', 'month', 'day','avatar','name',
            DB::raw('
                SUM(self_revenue) AS self_revenue, 
                SUM(branch_revenue) AS branch_revenue,
                SUM(branch_revenue+self_revenue) AS total_revenue,
                SUM(self_income) AS self_income, 
                SUM(nolife_before_tax) AS nolife_before_tax,
                SUM(nolife_affter_tax) AS nolife_affter_tax,
                SUM(branch_income) AS branch_income,
                SUM(life_before_tax) AS life_before_tax,
                SUM(life_affter_tax) AS life_affter_tax
            '))
            ->where('insurance_agency_id', '!=', '1310')
            ->where('insurance_agency_id', '!=', '1685')
            ->where('year', date_format(new DateTime($start), "Y"))
            ->where('month', date_format(new DateTime($start), "m"))
            ->groupBy('insurance_agency_id')
            ->orderBy('total_revenue', 'desc')
            ->join('insurance_agencies', 'revenue_dailys.insurance_agency_id', '=', 'insurance_agencies.id')
            ->where('insurance_agencies.status', 1)
            ->limit(10)
            ->get();
        $top_moncover = RevenueDaily::select('id', 'insurance_agency_id', 'date', 'year', 'month', 'day',
            DB::raw('
                SUM(self_revenue) AS self_revenue, 
                SUM(branch_revenue) AS branch_revenue,
                SUM(branch_revenue+self_revenue) AS total_revenue,
                SUM(self_income) AS self_income, 
                SUM(nolife_before_tax) AS nolife_before_tax,
                SUM(nolife_affter_tax) AS nolife_affter_tax,
                SUM(branch_income) AS branch_income,
                SUM(life_before_tax) AS life_before_tax,
                SUM(life_affter_tax) AS life_affter_tax
            '))
            ->where('insurance_agency_id', '=', '1310')
            ->where('year', date_format(new DateTime($start), "Y"))
            ->where('month', date_format(new DateTime($start), "m"))
            ->groupBy('insurance_agency_id')
            ->orderBy('total_revenue', 'desc')
            ->with('insurance_agency')
            ->limit(10)
            ->get();
        $revenue_total = RevenueDaily::select('id', 'insurance_agency_id', 'date', 'year', 'month', 'day', 
            DB::raw('
                SUM(self_revenue) AS self_revenue, 
                SUM(branch_revenue) AS branch_revenue, 
                SUM(self_income) AS self_income, 
                SUM(nolife_before_tax) AS nolife_before_tax,
                SUM(nolife_affter_tax) AS nolife_affter_tax,
                SUM(branch_income) AS branch_income,
                SUM(life_before_tax) AS life_before_tax,
                SUM(life_affter_tax) AS life_affter_tax
            '))
            ->where('insurance_agency_id', $agencyId)
            ->groupBy('insurance_agency_id')
            ->with('insurance_agency')
            ->first();
        $personal_income = 0;
        $low_level_revenue = 0;
        $level_up_amount = 0;
        if($revenue_total) {
            $personal_income = (int) $revenue_total->self_revenue ?? 0;
            $low_level_revenue = (int) $revenue_total->branch_revenue ?? 0;
            $level_up_amount = (int) $revenue_total->insurance_agency->level->level_up_amount; 
        }

        $total_revenue = $personal_income + $low_level_revenue;
       
        if ($level_up_amount == 0 ) {
            $level_percentage = ($total_revenue / 2000000) * 100;
        } else {
            $level_percentage = ($total_revenue / $level_up_amount) * 100;    
        }

        // Block cong no
        $congno_nhanvien = 0;
        if (!empty($agencyEmployee['agency_employee'])) {
            $paidEmployee = InsuranceContract::whereBetween('created_at', [$start, $end])
                    ->whereIn('sale_type_id', $agencyEmployee['agency_employee'])
                    ->sum('paid_amount');
            $requiredAmountEmployee = InsuranceContract::whereBetween('created_at', [$start, $end])
                ->whereIn('sale_type_id', $agencyEmployee['agency_employee'])
                ->sum('require_pay_amount');
            $congno_nhanvien = $requiredAmountEmployee - $paidEmployee;
        }

        $paidAmountAgency = InsuranceContract::whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type_id', $agencyEmployee['agency'])
            ->sum('paid_amount');
        $requiredAmountAgency = InsuranceContract::whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type_id', $agencyEmployee['agency'])
            ->sum('require_pay_amount');
        $congno_daily = $requiredAmountAgency - $paidAmountAgency;

        $congno_baohiem = $congno_nhanvien + $congno_daily;
        
        // Block bieu do
        $reportInsuranceType = InsuranceType::report($start, $end, $agencyIdArray);
        $reportSource = InsuranceContract::reportBySource($start, $end, $agencyIdArray);
        
        // Block news
        $newsService = new NewsService();
        $bantins = $newsService->getList(env('NEWS-DOI-TAC-DAI-LY', 10));//for agency
        $sanpham = $newsService->getList(env('NEWS-SAN-PHAM',13));//for agency
        $uudais = $newsService->getList(env('NEWS-UU-DAI',30));//for agency
        $daotaos = $newsService->getList(env('NEWS-DAO-TAO', 8));
        $chinhsachs = $newsService->getList(env('NEWS-CHINH-SACH', 32));
        $huongdans = $newsService->getList(env('NEWS-HUONG-DAN-SU-DUNG', 9));
        // Block bieu do doanh thu
        $insuranceContract = new InsuranceContract();
        $report = $insuranceContract->reportMonth($agencyIdArray);
        $report = $this->convertReportMonth($report);

        //add report type and source
        $typeLabel = [];
        $typeData = [];
        foreach ($reportInsuranceType as $row) {
            $typeLabel[] = $row['label'];
            $typeData[] = $row['data'];
        }
        $typeColor = $this->generateRandomColor(count($typeData));
        $sourceLabel = [];
        $sourceData = [];
        foreach ($reportSource as $row) {
            $sourceLabel[] = $row['label'];
            $sourceData[] = $row['data'];
        }
        $sourceColor = $this->generateRandomColor(count($sourceData));
        //end report type and source
        

        $data_wallet = AgencyWallet::where("id_agencies",  $agencyId)->first();
        return response()->json(compact(
            'data_wallet',
            'top_revenue_partner',
            // 'revenue_partner',
            'branch_revenue',
            'level',
            // 'revenue_online',
            'partner_contract_total',
            'total_new_partner',
            // 'manager',
            'notifications',
            // 'agencies',
            'start',
            'end',
            'total_customer',
            'kh_tiem_nang',
            'kh_co_hoi',
            'kh_mua_hang',
            'kh_tai_tuc',
            'total_quotations',
            'contract_sale',
            'contract_agence',
            'tong_doanh_thu_hieuluc',
            'tong_doanh_thu_khonghieuluc',
            'congno_nhanvien',
            'congno_daily',
            'congno_baohiem',
            'reportInsuranceType',
            'reportSource',
            'report',
            'bantins',
            'sanpham',
            'uudais',
            'chinhsachs',
            'daotaos',
            'huongdans',
            'sourceColor',
            'sourceLabel',
            'sourceData',
            'typeColor',
            'typeLabel',
            'typeData',
            'self_revenue',
            'total_revenue_monthly',
            'revenue_monthly',
            'self_income',
            'branch_income',
//            'total_income',
            'revenue_each_month',
            'data_month',
            'personal_income',
            'low_level_revenue',
            'total_revenue',
            'level_percentage',
            'top_moncover'
        ));
    }

    /**
     * Get article detail
     */
    public function getArticleDetail(Request $request)
    {
        $data = $request->all();
        $newsService = new NewsService();
        $rs = $newsService->getDetail($data['id']);
        $rsArray = json_decode($rs, true);
        return response()->json($rsArray);
    }

    /**
     * Generate random color hex
     */
    public function generateRandomColor($num)
    {
        $colorArray = [];
        for ($i=0; $i<$num; $i++) {
            $colorArray[] = '#'.dechex(rand(0x000000, 0xFFFFFF));
        }
        return $colorArray;
    }

    /**
     * Convert report month
     */
    public function convertReportMonth($data)
    {
        $rs = [];
        foreach ($data as $key => $value) {
            if (!empty($key)) {
                $keyArray = explode('-', $key);
                $keyConvert = $keyArray[1].'/'.$keyArray[0];
                $rs[$keyConvert] = $value;
            }
        }
        return $rs;
    }

    /**
     * Get list articles
     */
    public function getListArticle(Request $request)
    {
        $id = $request->id;
        $newsService = new NewsService();
        $listArticles = $newsService->getList($id);//for agency
        return response()->json($listArticles);
    }

    /**
     * Get number customer, agency, quotation
     */
    public function getCustomerAgencyQuotation(Request $request)
    {
        $numCustomer = Customer::where('status', Customer::STATUS_ACTIVE)->count();
        $numAgency = InsuranceAgency::where('status', Customer::STATUS_ACTIVE)->count();
        $numQuotation = InsuranceQuotation::sum('main_fee');
        $rs = [
            'num_customer' => $numCustomer,
            'num_agency' => $numAgency,
            'num_quotation' => $numQuotation
        ];
        return response()->json($rs);
    }

    /**
     * Get list article pagination
     */
    public function getListArticlePag(Request $request)
    {
        $id = $request->id;
        $params = $request->all();
        $newsService = new NewsService();
        isset($params['page']) ? $page = $params['page'] : $page = 1;
        isset($params['page_size']) ? $pageSize = $params['page_size'] : $pageSize = 10;
        isset($params['key']) ? $keySearch = $params['key'] : $keySearch = '';
        $listArticles = $newsService->getList($id, $page, $pageSize, $keySearch, 1);//for agency
        return response()->json($listArticles);
    }

    // Lấy ra thông báo
    public function getNotification (Request $request){
        $array_notification = [];
        $agency = InsuranceAgency::find($request->agency_id);
        $notifications = NotificationsActive::where([['object_type', 1], ['schedule', '<=', date('Y-m-d H:i:s')]])->orderBy('schedule', 'desc');
        foreach ($notifications->get() as $notification) {
            $kt = in_array($agency->id, json_decode($notification->read_at));
            if($kt == false)
            {
                array_push($array_notification, $notification);
            }
        }
        $count = count($array_notification);
        $notifications = $notifications->limit(10)->get();
        if(count($notifications) > 0){
            return response()->json(['result' => 1, 'message' => 'success','data' => $notifications, 'count' => $count]);
        } else {
            return response()->json(['result' => 0, 'message' => 'Không có thông báo nào']);
        }
    }

    // hàm đánh dấu đã đọc
    public function readNotification (Request $request){
        $agency = InsuranceAgency::find($request->agency_id);

        $notifications = NotificationsActive::where([['object_type', 1], ['schedule', '<=', date('Y-m-d H:i:s')]])->get();
        foreach ($notifications as $notification) {
            $kt = in_array($agency->id, json_decode($notification->read_at));
            if($kt == false)
            {
                $read_at = $notification->read_at;
                if($read_at != null){
                    $array_read_at = json_decode($read_at);
                    $array_read_at[] = $agency->id;
                    $notification->update([
                        'read_at' => json_encode($array_read_at)
                    ]);
                }
                else{
                    $array_read_at = [$agency->id];
                    $notification->update([
                        'read_at' => json_encode($array_read_at)
                    ]);
                }
            }
        }
    }
}
