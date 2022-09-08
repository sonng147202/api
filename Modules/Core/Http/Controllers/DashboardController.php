<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Lib\NewsService;
use Modules\Core\Lib\PushNotification;
use Modules\Core\Lib\PushNotificationHelper;
use Modules\Core\Lib\SendSMS;
use Modules\Insurance\Models\Customer;
use Modules\Insurance\Models\CustomerType;
use Modules\Insurance\Models\InsuranceAgency;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductInsuranceType;
use Modules\Insurance\Models\InsuranceContract;
use Carbon\Carbon;
use Modules\Insurance\Models\InsuranceQuotation;
use Modules\Insurance\Models\InsuranceType;
use Modules\Insurance\Models\RevenueMonthly;
use Modules\Insurance\Models\RevenueDaily;
use Modules\Insurance\Models\User;
use Modules\Core\Models\NotificationsActive;
use DB;
use DateTime;
use Illuminate\Support\Facades\Redirect;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
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

        $insuranceContractQuery = InsuranceContract::where('status', '>', InsuranceContract::STATUS_DELETED)->whereBetween('created_at', [$start, $end]);
        // Đối tác
        $partner_total = InsuranceAgency::whereBetween('created_at', [$start, $end])->count();
        // $partner_contract_total = InsuranceAgency::whereHas('insuranceContracts', function ($query) use ($start, $end) {
        //     $query->whereBetween('created_at', [$start, $end]);
        // })->count();
        $partner_contract_total = InsuranceContract::groupBy('sale_type_id')->whereBetween('created_at', [$start, $end])->count();
        // top 10 đối tác doanh thu lớn nhất
        $top_revenue_partner = RevenueDaily::select('revenue_dailys.id', 'insurance_agency_id', 'date', 'year', 'month', 'day',
            DB::raw('
                SUM(self_revenue) AS self_revenue, 
                SUM(branch_revenue) AS branch_revenue,
                SUM(branch_revenue+self_revenue) AS total_revenue
            '))
            ->where('year', date_format(new DateTime($start), "Y"))
            ->where('month', date_format(new DateTime($start), "m"))
            ->groupBy('insurance_agency_id')
            ->orderBy('total_revenue', 'DESC')
            ->join('insurance_agencies', 'revenue_dailys.insurance_agency_id', '=', 'insurance_agencies.id')
            ->where('insurance_agencies.status', 1)
            ->limit(10)
            ->get();


        // Block Khách hàng
        $customerQuery = Customer::where('status', '>', Customer::STATUS_DELETED)->whereBetween('created_at', [$start, $end]);
        $total_customer = $customerQuery->count();
        // $kh_tiem_nang = $customerQuery->where('classify', 1)->count();
        $kh_moi = Customer::where('status', '>', Customer::STATUS_DELETED)->whereBetween('created_at', [$start, $end])->count();
        $kh_co_hoi = $customerQuery->where('classify', 2)->count();
        $kh_mua_hang = $insuranceContractQuery->distinct('customer_id')->count();
        $kh_tai_tuc = $customerQuery->where('classify', 3)->count();

        // Block tuong tac
        $total_quotations = InsuranceQuotation::whereBetween('created_at', [$start, $end])->count();

        // Block Doanh thu
        // Doanh thu sale bao gồm các hợp đồng không có bảo hiểm du lịch

        $contract_sale = InsuranceContract::whereNotIn('type_id', [3])->whereBetween('created_at', [$start, $end])->where([['sale_type', 1],['status', 1]]);

         // Doanh thu
        $revenue_online = InsuranceContract::whereBetween('created_at', [$start, $end])->where([['sale_type', 0],['status', 1]])->sum('require_pay_amount');
        // $revenue_partner = InsuranceContract::whereBetween('created_at', [$start, $end])->where([['sale_type', 2],['status', 1]])->sum('require_pay_amount');
        $revenue_eroscare = RevenueDaily::select('id', 'insurance_agency_id', 'date', 'year', 'month', 'day',
            DB::raw('
                SUM(self_revenue) AS self_revenue, 
                SUM(branch_revenue) AS branch_revenue,
                SUM(branch_revenue+self_revenue) AS total_revenue
            '))
            ->where('insurance_agency_id', 1685
            )->whereBetween('date', [$start, $end])
            ->groupBy('insurance_agency_id')
            ->first();
        $revenue_partner = $revenue_eroscare['branch_revenue'];


        // $contract_sale = InsuranceContract::where('status', '>', InsuranceContract::STATUS_DELETED)->whereBetween('created_at', [$start, $end]);
        $contract_agence = InsuranceContract::where('status', '>', InsuranceContract::STATUS_DELETED)->whereBetween('created_at', [$start, $end])
            ->where('sale_type_id', '>', 0);
        $tong_doanh_thu_hieuluc = InsuranceContract::where('status', '>', InsuranceContract::STATUS_DELETED)->whereBetween('created_at', [$start, $end])
            ->where('certificate_active', 1);
        $tong_doanh_thu_khonghieuluc = InsuranceContract::where('status', '>', InsuranceContract::STATUS_DELETED)->whereBetween('created_at', [$start, $end])
            ->where('certificate_active', '!=', 1);
        $hoa_hong = InsuranceContract::where('status', '>', InsuranceContract::STATUS_DELETED)->whereBetween('created_at', [$start, $end]);

        $hoa_hong_nhan_tu_nha_BH = $hoa_hong->sum('commission_product');
        $hoa_hong_thuc_nhan = $this->calculateCommissionNetAmount($hoa_hong);

        // Block cong no
        $congno_nhanvien = InsuranceContract::where('status', '>', InsuranceContract::STATUS_DELETED)->whereBetween('created_at', [$start, $end])
                ->where('sale_type', 0);
        $congno_daily = InsuranceContract::where('status', '>', InsuranceContract::STATUS_DELETED)->whereBetween('created_at', [$start, $end])
                ->where('sale_type', '>', 0);
        $congno_baohiem = InsuranceContract::where('status', '>', InsuranceContract::STATUS_DELETED)->whereBetween('created_at', [$start, $end]);

        //If sales => Chi duoc xem cac hop dong cua sale do
        $belong_to_role = Auth::user()->load('roles');
        if (!empty($belong_to_role->roles[0]) && $belong_to_role->roles[0]->id == 5) {
            $contract_sale = $contract_sale->where('sale_type_id',Auth::id());
            $contract_agence = $contract_agence->where('sale_type_id',Auth::id());
            $tong_doanh_thu_hieuluc = $tong_doanh_thu_hieuluc->where('sale_type_id',Auth::id());
            $tong_doanh_thu_khonghieuluc = $tong_doanh_thu_khonghieuluc->where('sale_type_id',Auth::id());


            $congno_nhanvien = $congno_nhanvien->where('sale_type_id',Auth::id());
            $congno_daily = $congno_daily->where('sale_type_id',Auth::id());
            $congno_baohiem = $congno_baohiem->where('sale_type_id',Auth::id());
        }

        $contract_sale = $contract_sale->sum('require_pay_amount');
        $contract_agence = $contract_agence->sum('require_pay_amount');
        $tong_doanh_thu_hieuluc = $tong_doanh_thu_hieuluc->sum('require_pay_amount');
        $tong_doanh_thu_khonghieuluc = $tong_doanh_thu_khonghieuluc->sum('require_pay_amount');

        // Block cong no
        $congno_nhanvien = $congno_nhanvien->sum('require_pay_amount') - $insuranceContractQuery
                ->where('sale_type', 0)->sum('paid_amount');
        $congno_daily = $congno_daily->sum('require_pay_amount') - $insuranceContractQuery->where('sale_type', '>', 0)
                ->sum('paid_amount');
        $congno_baohiem = $congno_baohiem->sum('gross_amount') - $insuranceContractQuery->sum('paid_amount');


        // Block bieu do
        $reportInsuranceType = InsuranceType::report($start, $end);
        $reportSource = InsuranceContract::reportBySource($start, $end);
        // Block news
        $newsService = new NewsService();
        $bantins = $newsService->getList(env('NEWS-BAN-TIN', 7));
        $daotaos = $newsService->getList(env('NEWS-DAO-TAO', 8));
        $huongdans = $newsService->getList(env('NEWS-HUONG-DAN-SU-DUNG', 9));
        // Block bieu do doanh thu
        $insuranceContract = new InsuranceContract();
        $report = $insuranceContract->reportMonth();
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
        //notifications
        $notifications = NotificationsActive::whereBetween('schedule', [$start, $end])->orderBy('schedule', 'DESC')->limit(10)->get();
        //end report type and source


        return view('core::index',compact(
             // 'manager',
             // 'agencies',
            'partner_contract_total',
            'top_revenue_partner',
            'revenue_partner',
            'revenue_online',
            'partner_total',
            'notifications',
            'start',
            'end',
            'total_customer',
            'kh_moi',
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
            'sourceColor',
            'sourceLabel',
            'sourceData',
            'typeColor',
            'typeLabel',
            'typeData',
            'hoa_hong_nhan_tu_nha_BH',
            'hoa_hong_thuc_nhan'
        ))->withReport($report)
            ->withLabels(implode(',', array_keys($report)))
            ->withBantins($bantins)
            ->withDaotaos($daotaos)
            ->withHuongdans($huongdans);
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
     * Get article detail
     */
    public function getArticleDetail(Request $request)
    {
        $data = $request->all();
        $newsService = new NewsService();
        $data = $newsService->getDetail($data['id']);
        echo $data;
    }

    /**
     * Call modal display article detail
     */
    public function modalArticleDetail(Request $request)
    {
        return view('core::modalArticleDetail');
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
     * @param $contracts
     */
    public function calculateCommissionNetAmount($contracts)
    {
        $contracts = $contracts->get();
        $commission_net_amount = 0;
        foreach ($contracts as $contract) {
            $vat =  str_replace('%','',$contract->vat);
            $rate = (float)$vat > 0 ? 1.1 : 1;
            $discountAmountOfInsuranceCompany_Total = $contract->discount_amount_of_insurance_company;
            $discountAmount_Total = $contract->discount_amount;

            if ($contract->discount_type == 0 && $contract->discount_amount_of_insurance_company_type == 0) {
                // % %
                $discountAmountOfInsuranceCompany_Total = $contract->gross_amount * $contract->discount_amount_of_insurance_company /100;
                $discountAmount_Total = ($contract->gross_amount - $discountAmountOfInsuranceCompany_Total)/$rate * $discountAmount_Total / 100;

            } else if ($contract->discount_type == 1 && $contract->discount_amount_of_insurance_company_type == 0) {
                //VND %
                $discountAmountOfInsuranceCompany_Total = $contract->gross_amount * $contract->discount_amount_of_insurance_company /100;

            } else if ($contract->discount_type == 0 && $contract->discount_amount_of_insurance_company_type == 1) {
                //% VND
                $discountAmount_Total =  ($contract->gross_amount - $discountAmountOfInsuranceCompany_Total)/$rate * $discountAmount_Total / 100;
            }
            $commission_net_amount += ($contract->commission_product - $discountAmount_Total);
        }
        return $commission_net_amount;
    }




}
