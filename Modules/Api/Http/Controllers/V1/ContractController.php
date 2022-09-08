<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Models\Product;
use App\Models\InsuranceContract;
use App\Models\ConsultingInformation;
use App\Models\AgencyInfoFwd;
use App\Models\Customer;
use App\Models\RevenueDaily;
use App\Models\InsuranceAgency;
use App\Models\InsuranceType;
use DB;
use Modules\Product\Libraries\ProductPriceHelper;
use Modules\Product\Models\ProductLevelCommission;
use Modules\Api\Lib\InsuranceHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractController extends ApiController
{
    // Doanh thu cá nhân: Danh sách hợp đồng "nhân thọ" của Agency
    public function getListContractLifeAgency(Request $request)
    {
        try {
            $params = $request->all();
            $user = User::where('group_id', 3)->find($params['user_id']);
            if($user == null)
            {
                return \response()->json([
                    'result'       => 0,
                    'current_time' => time(),
                    'message'      => 'Error! Không có user như vậy trong hệ thống!',
                    'data'         => null
                ]);
            }
    
            $insurance_agency = $user->insuranceAgency;
            $list = InsuranceContract::select(
                    'insurance_contracts.id as contract_id',
                    'insurance_contracts.contract_number',
                    'mp_products.name',
                    'customers.name as customer_name',
                    'customers.phone_number',
                    'insurance_contracts.status',
                    'insurance_contracts.release_date',
                    'insurance_contracts.fee_payment_next_date',
                    'consulting_information.amount_paid as fee_main_product',
                    'consulting_information.total_amount_sup as fee_sup_product',
                    'insurance_contracts.insurance_company_id',
                    'insurance_contracts.insurance_agency_id'
                )
                ->whereIn('insurance_contracts.insurance_company_id', [1,10])
                //->whereIn('insurance_contracts.insurance_company_id', [2])
                ->join('customers', 'customers.id', 'insurance_contracts.customer_id')
                ->join('consulting_information', 'consulting_information.id', 'insurance_contracts.consulting_information_id')
                ->join('mp_products', 'mp_products.id', 'consulting_information.main_product_id')
                ->where('insurance_contracts.insurance_agency_id', $insurance_agency->id);
            
            $list2 = clone $list;

            if(!empty($params['search'])){
                $check = $list2->where('insurance_contracts.contract_number','like','%'.$params['search'].'%');
                if ($check->count() == 0) {
                    $list = $list->where('customers.phone_number','like','%'.$params['search'].'%');
                }
                else {
                    $list = $list->where('insurance_contracts.contract_number','like','%'.$params['search'].'%');
                }
            }

            if(!empty($params['begin_Date'])){
                $list = $list->whereDate('insurance_contracts.release_date', '>=', $params['begin_Date']);
            }

            if(!empty($params['end_Date'])){
                $list = $list->whereDate('insurance_contracts.release_date','<=', $params['end_Date']);
            }
    
            $list = $list->orderBy('pass_ack_date','DESC')->orderBy('consulting_information.effective_date','DESC')->orderBy('status','ASC')->orderBy('consulting_information.fee_payment_date','DESC')->paginate(50)->toArray();
            //$list = $list->get();

            $data = $list;
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Doanh thu cá nhân: Danh sách hợp đồng "phi nhân thọ" của Agency
    public function getListContractNonLifeAgency(Request $request)
    {
        try {
            $params = $request->all();
            $user = User::where('group_id', 3)->find($params['user_id']);
            if($user == null)
            {
                return \response()->json([
                    'result'       => 0,
                    'current_time' => time(),
                    'message'      => 'Error! Không có user như vậy trong hệ thống!',
                    'data'         => null
                ]);
            }
    
            $insurance_agency = $user->insuranceAgency;

            $list = InsuranceContract::select(
                    'insurance_contracts.id as contract_id',
                    'insurance_contracts.contract_number',
                    'mp_products.name',
                    'customers.name as customer_name',
                    'customers.phone_number',
                    'insurance_contracts.status',
                    'insurance_contracts.release_date',
                    'insurance_contracts.fee_payment_next_date',
                    'consulting_information.amount_paid as fee_main_product',
                    'consulting_information.total_amount_sup as fee_sup_product',
                    'insurance_contracts.insurance_company_id',
                    'insurance_contracts.insurance_agency_id'
                )
                ->where('insurance_contracts.insurance_agency_id', $insurance_agency->id)
                ->whereNotIn('insurance_contracts.insurance_company_id', [1,10])
                ->join('customers', 'customers.id', 'insurance_contracts.customer_id')
                ->join('consulting_information', 'consulting_information.id', 'insurance_contracts.consulting_information_id')
                ->join('mp_products', 'mp_products.id', 'consulting_information.main_product_id')
                ;

            $list2 = clone $list;

            if(!empty($params['search'])){
                $check = $list2->where('insurance_contracts.contract_number','like','%'.$params['search'].'%');
                if ($check->count() == 0) {
                    $list = $list->where('customers.phone_number','like','%'.$params['search'].'%');
                }
                else {
                    $list = $list->where('insurance_contracts.contract_number','like','%'.$params['search'].'%');
                }
            }

            if(!empty($params['begin_Date'])){
                $list = $list->whereDate('insurance_contracts.release_date', '>=', $params['begin_Date']);
            }

            if(!empty($params['end_Date'])){
                $list = $list->whereDate('insurance_contracts.release_date','<=', $params['end_Date']);
            }
    
            $list = $list->orderBy('pass_ack_date','DESC')->orderBy('consulting_information.effective_date','DESC')->orderBy('status','ASC')->orderBy('consulting_information.fee_payment_date','DESC')->paginate(50)->toArray();
            
            $data = $list;
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Báo cáo công việc - Báo cáo hợp đồng của BH nhân thọ
    public function addLifeContractReport(Request $request)
    {
        $params = $request->all();
        $user = User::where('group_id', 3)->find($params['user_id']);

        if (empty($params["user_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Id đại lý không được bỏ trống']);
        }

        if(empty($params['sex']) && $params['sex'] != '0'){
            return \response()->json(['result' => 0, 'message' => 'Giới tính khách hàng không được bỏ trống']);
        }

        if (empty($params["name_customer"])) {
            return \response()->json(['result' => 0, 'message' => 'Họ tên không khách hàng được bỏ trống']);
        }

        if (empty($params["email"])) {
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không được bỏ trống']);
        }

        if (empty($params["phone"])) {
            return \response()->json(['result' => 0, 'message' => 'Số điện thoại FWD không được bỏ trống']);
        }

        if (empty($params["name_agency_official"])) {
            return \response()->json(['result' => 0, 'message' => 'Họ và tên FWD không được bỏ trống']);
        }

        if (empty($params["date_of_birth"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày sinh khách hàng không được bỏ trống']);
        }

        if (empty($params["address"])) {
            return \response()->json(['result' => 0, 'message' => 'Địa chỉ khách hàng không được bỏ trống']);
        }

        if (empty($params["phone_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số điện thoại khách hàng không được bỏ trống']);
        }

        if (empty($params["img_url"])) {
            return \response()->json(['result' => 0, 'message' => 'Bạn phải chụp ảnh đơn thông tin tư vấn']);
        }

        if (empty($params["img_info_url"])) {
            return \response()->json(['result' => 0, 'message' => 'Bạn phải chụp ảnh minh họa quyền lợi bảo hiểm']);
        }

        if (empty($params["date_submit"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày nộp hợp đồng không được bỏ trống']);
        }

        if (empty($params["contract_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số hợp đồng không được bỏ trống']);
        }

        if(empty($params['type_id']) && $params['type_id'] != '0'){
            return \response()->json(['result' => 0, 'message' => 'Loại khách hàng không được bỏ trống']);
        }

        if(!filter_var($params['email'], FILTER_VALIDATE_EMAIL)){
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không đúng định dạng']);
        }

        if (empty($params["code_agency_official"])) {
            return \response()->json(['result' => 0, 'message' => 'Mã số FWD đại lý bảo hiểm không được bỏ trống']);
        }

        if (empty($params["id_card_number_FWD"])) {
            return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD FWD không được bỏ trống']);
        }

        if (empty($params["code_fad"])) {
            return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD FWD không được bỏ trống']);
        }
        
        if (empty($params["id_card_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD khách hàng không được bỏ trống']);
        }

        if (empty($params["total_amount_sup"]) && $params['total_amount_sup'] != '0') {
            return \response()->json(['result' => 0, 'message' => 'Tổng phí SPBT đã đóng không được bỏ trống']);
        }

        if (empty($params["amount_paid"]) && $params['amount_paid'] != '0') {
            return \response()->json(['result' => 0, 'message' => 'Phí sản phẩm chính đã đóng không được bỏ trống']);
        }

        if (empty($params["quantity_product_sup"]) && $params['quantity_product_sup'] != '0') {
            return \response()->json(['result' => 0, 'message' => 'Số lượng sản phẩm bổ trợ (SPBT) không được bỏ trống']);
        }

        if (empty($params["main_product_id"])) {
            return \response()->json(['result' => 0, 'message' => 'ID sản phẩm chính không được bỏ trống']);
        }

        if (empty($params["periodic_fee_type"])) {
            return \response()->json(['result' => 0, 'message' => 'Kỳ đóng phí không được bỏ trống']);
        }

        if (empty($params["company_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Công ty bảo hiểm không được bỏ trống']);
        }

        $check = ConsultingInformation::where('contract_number', $params['contract_number'])->where('insurance_company_id', $params["company_id"])->count();
        
        if ($check >= 1) {
            return \response()->json(['result' => 0, 'message' => 'Số hợp đồng đã tồn tại!']);
        }

        DB::beginTransaction();
        try {
            $fwd = AgencyInfoFwd::where('code_agency_official',$params["code_agency_official"])->first();
            if(!$fwd){
                $fwd = AgencyInfoFwd::create([
                    'name_agency_official' => mb_strtoupper($params["name_agency_official"],'UTF-8'),
                    'phone' => $params["phone"],
                    'code_agency_official'  => $params["code_agency_official"],
                    'id_card_number'  => $params["id_card_number_FWD"],
                    'code_FAD'  => $params["code_fad"]
                ]);
            }
            $agency = InsuranceAgency::where('code_agency',$user->insuranceAgency->code_agency)->first();
            $customer = Customer::where('id_card_number', $params["id_card_number"])->first();
            if(!$customer){
                $params["date_of_birth"] = date('Y-m-d',strtotime(str_replace('/', '-', $params["date_of_birth"])));

                $customer = Customer::create([
                    'id_card_number' => $params["id_card_number"],
                    'email' => $params["email"],
                    'name' => mb_strtoupper($params["name_customer"],'UTF-8'),
                    'phone_number' => $params["phone_number"],
                    'date_of_birth' => $params["date_of_birth"],
                    'address' => $params["address"],
                    'sex' => $params["sex"],
                    'insurance_agency_id' =>$user->insuranceAgency->id,
                    'type_id' => $params["type_id"]
                ]);
            }
            $params["date_submit"] = date("Y-m-d",strtotime( str_replace('/', '-', $params["date_submit"])));
            $amount_paid = str_replace(".","",$params["amount_paid"]);
            $total_amount_sup = str_replace(".","",$params["total_amount_sup"]);
            $p_fyp_temporary = 0;
            if($amount_paid >= 5000000 && $params['quantity_product_sup'] > 2){
                $p_fyp_temporary = (int)$amount_paid/1.1 + (int)$total_amount_sup/2.8;
            }elseif(6000000 <= $amount_paid && $amount_paid <= 10000000 && $params['quantity_product_sup'] > 0 && $params['quantity_product_sup'] < 3 ){
                $p_fyp_temporary = (int)$amount_paid/1.15 + (int)$total_amount_sup/2.8;
            }

            $array_img_url = [];
            foreach ($params["img_url"] as $img_url) {
                $filename_img_url = uniqid().'.'.$img_url->getClientOriginalExtension();
                $file_path_img_url = '/storage/' . $filename_img_url;
                Storage::disk('s3')->put($file_path_img_url, file_get_contents($img_url));
                array_push($array_img_url, $file_path_img_url);
            }

            $array_img_info_url = [];
            foreach ($params["img_info_url"] as $img_info_url) {
                $filename_img_info_url = uniqid().'_'.$img_info_url->getClientOriginalExtension();
                $file_path_img_info_url = '/storage/' . $filename_img_info_url;
                Storage::disk('s3')->put($file_path_img_info_url, file_get_contents($img_info_url));
                array_push($array_img_info_url, $file_path_img_info_url);
            }

            ConsultingInformation::create([                   
                "contract_number"=> $params["contract_number"],
                "fee_payment_date"=> $params["date_submit"],
                "main_product_id"=> $params["main_product_id"],
                "amount_paid"=> str_replace(".","",$params["amount_paid"]),
                "quantity_product_sup"=> $params["quantity_product_sup"],
                "total_amount_sup"=> str_replace(".","",$params["total_amount_sup"]),
                "img_url"=> json_encode($array_img_url),
                "img_info_url"=> json_encode($array_img_info_url),
                "insurance_agency_id"=>$user->insuranceAgency->id,
                "agency_info_fwd_id"=>$fwd->id,
                "customer_id" => $customer->id,
                "insurance_company_id" => $params["company_id"],
                'periodic_fee_type'=>$params["periodic_fee_type"],
                "p_fyp_temporary" => $p_fyp_temporary
            ]);
            
            DB::commit();

            return \response()->json([
                'result'       => 1,
                'current_time' => time(),
                'message'      => 'Tạo báo cáo công việc thành công!',
            ]);
        }catch(\Exception $e){
            DB::rollback();
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }
    }

    // Báo cáo công việc - Báo cáo hợp đồng BH phi nhân thọ
    public function addNonLifeContractReport(Request $request)
    {
        $params = $request->all();
        $user = User::where('group_id', 3)->find($params['user_id']);

        if (empty($params["user_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Id đại lý không được bỏ trống']);
        }

        if(empty($params['sex']) && $params['sex'] != '0'){
            return \response()->json(['result' => 0, 'message' => 'Giới tính khách hàng không được bỏ trống']);
        }

        if (empty($params["name_customer"])) {
            return \response()->json(['result' => 0, 'message' => 'Họ tên không khách hàng được bỏ trống']);
        }

        if (empty($params["email"])) {
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không được bỏ trống']);
        }

        if (empty($params["effective_date"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày hiệu lực không được bỏ trống']);
        }

        if (empty($params["date_of_birth"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày sinh khách hàng không được bỏ trống']);
        }

        if (empty($params["address"])) {
            return \response()->json(['result' => 0, 'message' => 'Địa chỉ khách hàng không được bỏ trống']);
        }

        if (empty($params["phone_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số điện thoại khách hàng không được bỏ trống']);
        }

        if (empty($params["contract_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số hợp đồng không được bỏ trống']);
        }

        if(!filter_var($params['email'], FILTER_VALIDATE_EMAIL)){
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không đúng định dạng']);
        }
        
        if (empty($params["id_card_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD khách hàng không được bỏ trống']);
        }

        if (empty($params["company_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Công ty bảo hiểm không được bỏ trống']);
        }

        if (empty($params["fee_payment_date"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày nộp phí không được bỏ trống']);
        }

        if (empty($params["gross_amount"])) {
            return \response()->json(['result' => 0, 'message' => 'Phí BH trước thuế không được bỏ trống']);
        }

        if (empty($params["vat"]) && $params['vat'] != '0') {
            return \response()->json(['result' => 0, 'message' => 'Thuế không được bỏ trống']);
        }

        if (empty($params["renewals_customer_status"]) && $params['renewals_customer_status'] != '0') {
            return \response()->json(['result' => 0, 'message' => 'Hình thức tham gia không được bỏ trống']);
        }

        if (empty($params["payment_via"]) && $params['payment_via'] != '0') {
            return \response()->json(['result' => 0, 'message' => 'Hình thức thanh toán phí không được bỏ trống']);
        }

        if (empty($params["main_product_id"])) {
            return \response()->json(['result' => 0, 'message' => 'ID sản phẩm chính không được bỏ trống']);
        }

        $check = ConsultingInformation::where('contract_number', $request->contract_number)
                                        ->where('insurance_company_id', $params["company_id"])
                                        ->first();
        if ($check) {
            return \response()->json(['result' => 0, 'message' => 'Số hợp đồng đã tồn tại!']);
        }

        DB::beginTransaction();
        try {
            $agency = InsuranceAgency::where('code_agency',$user->insuranceAgency->code_agency)->first();
            if(!$agency){
                return redirect()->back()->withErrors(['code_agency' => 'Mã Medici không tồn tại'])->withInput();
            }
            $customer = Customer::where('id_card_number', $params["id_card_number"])->first();
            if(!$customer){
                $params["date_of_birth"] = date('Y-m-d',strtotime(str_replace('/', '-', $params["date_of_birth"])));
                $customer = Customer::create([
                    'id_card_number'        => $params["id_card_number"],
                    'email'                 => $params["email"],
                    'name'                  => mb_strtoupper($params["name_customer"],'UTF-8'),
                    'phone_number'          => $params["phone_number"],
                    'date_of_birth'         => $params["date_of_birth"],
                    'address'               => $params["address"],
                    'sex'                   => $params["sex"],
                    'insurance_agency_id'   => $agency->id
                ]);
            }
            $params["fee_payment_date"] = date("Y-m-d",strtotime( str_replace('/', '-', $params["fee_payment_date"])));
            $params["effective_date"] = date("Y-m-d",strtotime( str_replace('/', '-', $params["effective_date"])));
            $params['gross_amount'] = str_replace(".","",$params["gross_amount"]);
            $params["net_amount"] = round($params['gross_amount'] + ($params['gross_amount'] * ($params["vat"]/100)), 0);
            $consulting = ConsultingInformation::create([                   
                "contract_number"           => $params["contract_number"],
                "insurance_company_id"      => $params["company_id"],
                "main_product_id"           => $params["main_product_id"],
                "effective_date"           => $params["effective_date"],
                "fee_payment_date"          => $params["fee_payment_date"],
                "gross_amount"               => $params['gross_amount'],
                "vat"                         => $params["vat"],
                "net_amount"               => $params["net_amount"],
                "renewals_customer_status"  => $params["renewals_customer_status"],
                "payment_via"               => $params["payment_via"],
                "insurance_agency_id"       => $agency->id,
                "customer_id"               => $customer->id,
                "approve_status"           => 0
            ]);
            if($consulting){
                $product = Product::where('id', $params["main_product_id"])->first();
                $data = [
                    'customer_id'                   => $customer->id,
                    'insurance_agency_id'           => $agency->id,
                    'consulting_information_id'     => $consulting->id,
                    'contract_number'               => $params["contract_number"],
                    "insurance_company_id"          => $params["company_id"],
                    "product_id"                    => $params["main_product_id"],
                    "effective_date"                => $params["effective_date"],
                    "fee_payment_date"              => $params["fee_payment_date"],
                    "gross_amount"                  => $params["gross_amount"],
                    "vat"                           => $params["vat"],
                    "net_amount"                    => $params["net_amount"],
                    "renewals_customer_status"      => $params["renewals_customer_status"],
                    "payment_via"                   => $params["payment_via"],
                    'status'                        => 1,
                    'discount_amount'               => $product->discount
                ];
                $contract = InsuranceContract::create($data);
            }
            DB::commit();
            return \response()->json([
                'result'       => 1,
                'current_time' => time(),
                'message'      => 'Tạo báo cáo công việc thành công!',
            ]);
        }catch(\Exception $e){
            DB::rollback();
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }
    }

    // Chi tiết hợp đồng
    public function getContractDetail(Request $request)
    {
        try {
            $params = $request->all();

            $contract = InsuranceContract::select(
                'insurance_contracts.id as contract_id',
                'insurance_contracts.contract_number',
                'mp_products.name as product_name',
                'insurance_companies.name as insurance_company_name',
                'customers.name as customer_name',
                DB::raw("(CASE WHEN mp_products.is_main_product = 1 
                            THEN 'Sản phẩm chính' 
                            ELSE 'Sản phẩm phụ' 
                          END) AS is_main_product"),
                'insurance_contracts.release_date',
                'insurance_contracts.fee_payment_next_date',
                'consulting_information.amount_paid as fee_main_product',
                'consulting_information.total_amount_sup as fee_sup_product',
                'insurance_contracts.status',
                'consulting_information.img_url as img_url',
                'consulting_information.img_info_url as img_info_url'
            )
            ->where('insurance_contracts.id', $params['contract_id'])
            ->join('customers', 'customers.id', 'insurance_contracts.customer_id')
            ->join('consulting_information', 'consulting_information.id', 'insurance_contracts.consulting_information_id')
            ->join('mp_products', 'mp_products.id', 'consulting_information.main_product_id')
            ->join('insurance_companies', 'insurance_companies.id', 'mp_products.insurance_company_id')//;
            //->join('mp_products', 'mp_products.id', 'insurance_contracts.product_id')
            ->first();

            $data =  compact(['contract']);
            //$data = $contract;

        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Doanh thu nhân thọ: Danh sách hợp đồng nhánh BH "nhân thọ"
    public function getListContractLifeBranch(Request $request)
    {
        try {
            $params = $request->all();

            $agency = InsuranceAgency::find($params['insurance_agency_id']);

            $getTree = $this->getTree([$agency->code_agency], []);
            
            $data = InsuranceContract::select(
                    'insurance_contracts.id',
                    'insurance_contracts.contract_number',
                    'mp_products.name as product_name',
                    'customers.name as customer_name',
                    'insurance_contracts.fee_payment_date',
                    DB::raw("consulting_information.amount_paid + consulting_information.total_amount_sup as insurance_fees")
                )
                ->join('customers', 'customers.id', 'insurance_contracts.customer_id')
                ->join('consulting_information', 'consulting_information.id', 'insurance_contracts.consulting_information_id')
                ->join('mp_products', 'mp_products.id', 'consulting_information.main_product_id')
                ->whereIn('insurance_contracts.insurance_company_id', [1, 10])
                ->whereIn('insurance_contracts.insurance_agency_id', $getTree);
    
            $list = clone $data;

            if(!empty($params['search'])){
                $check = $list->where('insurance_contracts.contract_number','like','%'.$params['search'].'%');
                if ($check->count() == 0) {
                    $data = $data->where('customers.name','like','%'.$params['search'].'%');
                }
                else {
                    $data = $data->where('insurance_contracts.contract_number','like','%'.$params['search'].'%');
                }
            }

            if(!empty($params['from_date'])){
                $data = $data->whereDate('insurance_contracts.fee_payment_date', '>=', $params['from_date']);
            }

            if(!empty($params['to_date'])){
                $data = $data->whereDate('insurance_contracts.fee_payment_date','<=', $params['to_date']);
            }
    
            $data = $data->orderBy('pass_ack_date','DESC')->orderBy('consulting_information.effective_date','DESC')->orderBy('insurance_contracts.status','ASC')->orderBy('consulting_information.fee_payment_date','DESC')->paginate(50)->toArray();

        } catch (\Exception $e) {
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }
        return $this->apiResponse($data, null, 1);
    }

    // Doanh thu phi nhân thọ: Danh sách hợp đồng nhánh BH "phi nhân thọ"
    public function getListContractNonLifeBranch(Request $request)
    {
        try {
            $params = $request->all();

            $agency = InsuranceAgency::find($params['insurance_agency_id']);

            $getTree = $this->getTree([$agency->code_agency], []);
            
            $data = InsuranceContract::select(
                    'insurance_contracts.id',
                    'insurance_contracts.contract_number',
                    'mp_products.name as product_name',
                    'customers.name as customer_name',
                    'insurance_contracts.fee_payment_date',
                    'insurance_contracts.net_amount',
                    'insurance_contracts.gross_amount'
                )
                ->join('customers', 'customers.id', 'insurance_contracts.customer_id')
                ->join('consulting_information', 'consulting_information.id', 'insurance_contracts.consulting_information_id')
                ->join('mp_products', 'mp_products.id', 'consulting_information.main_product_id')
                ->whereIn('insurance_contracts.insurance_agency_id', $getTree)
                ->whereNotIn('insurance_contracts.insurance_company_id', [1,10]);
            
            $list = clone $data;

            if(!empty($params['search'])){
                $check = $list->where('insurance_contracts.contract_number','like','%'.$params['search'].'%');
                if ($check->count() == 0) {
                    $data = $data->where('customers.name','like','%'.$params['search'].'%');
                }
                else {
                    $data = $data->where('insurance_contracts.contract_number','like','%'.$params['search'].'%');
                }
            }

            if(!empty($params['from_date'])){
                $data = $data->whereDate('insurance_contracts.fee_payment_date', '>=', $params['from_date']);
            }

            if(!empty($params['to_date'])){
                $data = $data->whereDate('insurance_contracts.fee_payment_date','<=', $params['to_date']);
            }
    
            $data = $data->orderBy('pass_ack_date','DESC')->orderBy('consulting_information.effective_date','DESC')->orderBy('insurance_contracts.status','ASC')->orderBy('consulting_information.fee_payment_date','DESC')->paginate(50)->toArray();

        } catch (\Exception $e) {
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }
        return $this->apiResponse($data, null, 1);
    }

    public function getTree($parentCodeArr, $diffArr) {
        $parentArr = InsuranceAgency::whereIn('code_agency', $parentCodeArr)->pluck('id')->toArray();
        $parentId = $parentArr;
        $data = $parentArr;
        while (count($parentId) > 0){
            $childs = InsuranceAgency::selectRaw("insurance_agencies.*")
                                ->whereIn('insurance_agencies.parent_id', $parentId)
                                ->whereNotNull('insurance_agencies.code_agency')
                                ->whereNotIn('users.status', [3,5])
                                ->leftjoin('users', 'users.id', 'insurance_agencies.user_id');

            if(!empty($diffArr)){
                $childs = $childs->whereNotIn('insurance_agencies.code_agency', $diffArr);      
            }   
            $childs = $childs->get();    

            $parentId = [];
            if(!empty($childs)) {
                foreach($childs as $child){
                    array_push($parentId, $child->id);
                    array_push($data, $child->id);
                }
            }
        } 
        return $data;
    }

    public function getListGCNContract(Request $request)
    {
        try {
            $params = $request->all();

            if (empty($params["contract_id"])) {
                return \response()->json(['result' => 0, 'message' => 'Id hợp đồng không được bỏ trống']);
            }

            $contract = InsuranceContract::find($params['contract_id']);

            if ($contract && $contract->status == 1)
            {
                $data = $contract->files()->select('id', 'contract_id', 'file_path', 'type', 'status')->get()->toArray();
            }
            elseif ($contract && $contract->status == 0)
            {
                return \response()->json(['result' => 0, 'message' => 'Hợp đồng chưa ký số!']);
            }
            elseif ($contract == null) {
                return \response()->json(['result' => 0, 'message' => 'Hợp đồng không tồn tại!']);
            }
        } catch (\Exception $e) {
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    // Tạo hợp đồng phi nhân thọ
    public function storeContractNonLife(Request $request)
    {
        $params = $request->all();
        $user = User::where('group_id', 3)->find($params['user_id']);

        if (empty($params["user_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Id đại lý không được bỏ trống']);
        }

        if(empty($params['sex']) && $params['sex'] != '0'){
            return \response()->json(['result' => 0, 'message' => 'Giới tính khách hàng không được bỏ trống']);
        }

        if (empty($params["name_customer"])) {
            return \response()->json(['result' => 0, 'message' => 'Họ tên không khách hàng được bỏ trống']);
        }

        if (empty($params["email"])) {
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không được bỏ trống']);
        }

        if (empty($params["effective_date"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày hiệu lực không được bỏ trống']);
        }

        if (empty($params["date_of_birth"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày sinh khách hàng không được bỏ trống']);
        }

        if (empty($params["address"])) {
            return \response()->json(['result' => 0, 'message' => 'Địa chỉ khách hàng không được bỏ trống']);
        }

        if (empty($params["phone_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số điện thoại khách hàng không được bỏ trống']);
        }

        // if (empty($params["contract_number"])) {
        //     return \response()->json(['result' => 0, 'message' => 'Số hợp đồng không được bỏ trống']);
        // }

        if(!filter_var($params['email'], FILTER_VALIDATE_EMAIL)){
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không đúng định dạng']);
        }
        
        if (empty($params["id_card_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD khách hàng không được bỏ trống']);
        }

        if (empty($params["company_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Công ty bảo hiểm không được bỏ trống']);
        }

        if (empty($params["fee_payment_date"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày nộp phí không được bỏ trống']);
        }

        if (empty($params["gross_amount"])) {
            return \response()->json(['result' => 0, 'message' => 'Phí BH trước thuế không được bỏ trống']);
        }

        // if (empty($params["renewals_customer_status"]) && $params['renewals_customer_status'] != '0') {
        //     return \response()->json(['result' => 0, 'message' => 'Hình thức tham gia không được bỏ trống']);
        // }

        // if (empty($params["payment_via"]) && $params['payment_via'] != '0') {
        //     return \response()->json(['result' => 0, 'message' => 'Hình thức thanh toán phí không được bỏ trống']);
        // }

        if (empty($params["main_product_id"])) {
            return \response()->json(['result' => 0, 'message' => 'ID sản phẩm chính không được bỏ trống']);
        }

        $agency = InsuranceAgency::where('code_agency',$user->insuranceAgency->code_agency)->first();
        if(!$agency){
            return redirect()->back()->withErrors(['code_agency' => 'Mã Medici không tồn tại'])->withInput();
        }

        DB::beginTransaction();
        try {
            $customer = Customer::where('id_card_number', $params["id_card_number"])->first();
            if(!$customer){
                $params["date_of_birth"] = date('Y-m-d',strtotime(str_replace('/', '-', $params["date_of_birth"])));
                $customer = Customer::create([
                    'id_card_number'        => $params["id_card_number"],
                    'email'                 => $params["email"],
                    'name'                  => mb_strtoupper($params["name_customer"],'UTF-8'),
                    'phone_number'          => $params["phone_number"],
                    'date_of_birth'         => $params["date_of_birth"],
                    'address'               => $params["address"],
                    'sex'                   => $params["sex"],
                    'insurance_agency_id'   => $agency->id
                ]);
            }
            
            $vat = 0;
            $params["fee_payment_date"] = date("Y-m-d",strtotime( str_replace('/', '-', $params["fee_payment_date"])));
            $params["effective_date"] = date("Y-m-d",strtotime( str_replace('/', '-', $params["effective_date"])));
            $params['gross_amount'] = str_replace(".","",$params["gross_amount"]);
            $net_amount = round($params['gross_amount'] + ($params['gross_amount'] * ($vat/100)), 0);

            $consulting = ConsultingInformation::create([                   
                "insurance_company_id"      => $params["company_id"],
                "main_product_id"           => $params["main_product_id"],
                "renewals_customer_status"  => 0, //$params["renewals_customer_status"],
                "payment_via"               => 0, //$params["payment_via"]
                "insurance_agency_id"       => $agency->id,
                "customer_id"               => $customer->id,
                "approve_status"           => 0,
                "net_amount"          => $net_amount,
                "gross_amount"        => $params['gross_amount'],
                "fee_payment_date"         => $params["fee_payment_date"],
                "effective_date"      => $params["effective_date"],
                "vat"                 => $vat,
            ]);
            if($consulting){
                $product = Product::where('id', $params["main_product_id"])->first();
                $data = [
                    'customer_id'                   => $customer->id,
                    'insurance_agency_id'           => $agency->id,
                    'consulting_information_id'     => $consulting->id,
                    "insurance_company_id"          => $params["company_id"],
                    "product_id"                    => $params["main_product_id"],
                    "renewals_customer_status"      => 0, //$params["renewals_customer_status"],
                    "payment_via"                   => 0, //$params["payment_via"]
                    'status'                        => 0,
                    'discount_amount'               => $product->discount,
                    "net_amount"          => $net_amount,
                    "gross_amount"        => $params['gross_amount'],
                    "fee_payment_date"         => $params["fee_payment_date"],
                    "effective_date"      => $params["effective_date"],
                    "vat"                 => $vat,
                ];
                $contract = InsuranceContract::create($data);
                $contract->saveBeneficiaryAttributes(isset($params["beneficiary"]) ? $params["beneficiary"] : null);
            }

            DB::commit();
            return \response()->json([
                'result'       => 1,
                'current_time' => time(),
                'message'      => 'Tạo hợp đồng thành công!',
                'data'         => [
                    'contract_id' => $contract->id,
                ],
            ]);
        }catch(\Exception $e){
            DB::rollback();
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }
    }

    // Ký số hợp đồng VBI
    public function activeCertification(Request $request)
    {
        $params = $request->all();

        if (empty($params["contract_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Id hợp đồng không được bỏ trống']);
        }

        DB::beginTransaction();
        try {
            $contract = InsuranceContract::find($params["contract_id"]);

            if ($contract) {
                $result = InsuranceContract::activeCertificate($contract->id);
                if ($result['success'] == true) {
                    // Check contract_number
                    $check = ConsultingInformation::where('contract_number', $result['contract_number'])
                        ->where('insurance_company_id', $contract->insurance_company_id)
                        ->first();
                    if ($check) {
                        return \response()->json(['result' => 0, 'message' => 'Số hợp đồng đã tồn tại!']);
                    }

                    // Update contract_number in contract, consulting
                    $contract->update([
                        'contract_number'          => $result['contract_number'],
                        "payment_status"           => 1,
                        'status'                   => 1,
                        "effective_date"      => date('Y-m-d'),
                    ]);

                    $contract->consulting_information->update([
                        'contract_number'     => $result['contract_number'],
                        "status"              => 1,
                        "approve_status"      => 1,
                        "effective_date"      => date('Y-m-d'),
                    ]);
                }
                else {
                    return \response()->json(['result' => 0, 'message' => 'Lỗi hệ thống! Ký số thất bại!']);
                }
            }
            else {
                return \response()->json(['result' => 0, 'message' => 'Hợp đồng không tồn tại!']);
            }

            DB::commit();
            return \response()->json([
                'result'       => 1,
                'current_time' => time(),
                'message'      => 'Ký số hợp đồng thành công!',
            ]);
        } catch(\Exception $e){
            DB::rollback();
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }
    }

    // create link thanh toán VNPay
    public function paymentVnpay($data_payment)
    {
        $vnp_TmnCode = env('VNP_TMNCODE'); //Mã website tại VNPAY 
        $vnp_HashSecret = env('VNP_HASH_SECRET'); //Chuỗi bí mật
        $vnp_Url = env('VNP_URL');
        $vnp_Returnurl = env('VNP_RETURN_URL');

        $vnp_TxnRef = $data_payment['contract_id']; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo ='Thanh toán bảo hiểm Medici Pro.';
        $vnp_OrderType = 'other';
        $vnp_Amount = $data_payment['amount']*100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        $returnData = array('code' => '00'
            , 'message' => 'success'
            , 'data' => $vnp_Url);
        return $vnp_Url;
    }

    // VNPay return noti app
    public function paymentVnpayReturn(Request $request)
    {
        // dd($request->all());
        if ($request->vnp_ResponseCode == '00') {
            //thanh toán thành công
            return redirect()->to('http://success.sdk.merchantbackapp');
        } elseif ($request->vnp_ResponseCode == '24') {
            // Cancel: quay lại k thanh toán nữa
            return redirect()->to('http://cancel.sdk.merchantbackapp');
        } else {
            //thanh toán thất bại
            return redirect()->to('http://fail.sdk.merchantbackapp');
        }
    }

    // Thanh toán hợp đồng
    public function contractPayment(Request $request)
    {
        $params = $request->all();

        if (empty($params["contract_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Id hợp đồng không được bỏ trống']);
        }

        try {
            $contract = InsuranceContract::find($params["contract_id"]);

            if ($contract) {
                $data_payment = [
                    'contract_id' => $params["contract_id"],
                    'amount'      => $contract->gross_amount,
                ];

                $url = $this->paymentVnpay($data_payment);

                $data = [
                    'url' => $url,
                ];
            }
            else {
                return \response()->json([
                    'result'       => 0,
                    'current_time' => time(),
                    'message'      => 'Hợp đồng không tồn tại',
                    'data'         => null
                ]);
            }
        } catch(\Exception $e){
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }

        return $this->apiResponse($data, null, 1);
    }
}
